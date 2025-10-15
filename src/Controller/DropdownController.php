<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Country;
use App\Entity\Database;
use App\Entity\Company;
use App\Entity\Model;
use App\Entity\State;
use App\Entity\Box;
use App\Entity\Scale;
use App\Entity\ScaleTrack;
use App\Entity\Axle;
use App\Entity\Epoch;
use App\Entity\Subepoch;
use App\Entity\Project;
use App\Entity\Status;
use App\Entity\Condition;
use App\Entity\Modelset;
use App\Entity\Containertype;
use App\Entity\Coupler;
use App\Entity\Decoder;
use App\Entity\Edition;
use App\Entity\Pininterface;
use App\Entity\Power;
use App\Entity\Protocol;
use App\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class DropdownController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/box/add/', name: 'mbs_box_add', methods: ['GET', 'POST'])]
    #[Route('/scale/add/', name: 'mbs_scale_add', methods: ['GET', 'POST'])]
    #[Route('/scaletrack/add/', name: 'mbs_scaletrack_add', methods: ['GET', 'POST'])]
    #[Route('/axle/add/', name: 'mbs_axle_add', methods: ['GET', 'POST'])]
    #[Route('/epoch/add/', name: 'mbs_epoch_add', methods: ['GET', 'POST'])]
    #[Route('/subepoch/add/', name: 'mbs_subepoch_add', methods: ['GET', 'POST'])]
    #[Route('/containertype/add/', name: 'mbs_containertype_add', methods: ['GET', 'POST'])]
    #[Route('/coupler/add/', name: 'mbs_coupler_add', methods: ['GET', 'POST'])]
    #[Route('/decoder/add/', name: 'mbs_decoder_add', methods: ['GET', 'POST'])]
    #[Route('/edition/add/', name: 'mbs_edition_add', methods: ['GET', 'POST'])]
    #[Route('/pininterface/add/', name: 'mbs_pininterface_add', methods: ['GET', 'POST'])]
    #[Route('/power/add/', name: 'mbs_power_add', methods: ['GET', 'POST'])]
    #[Route('/protocol/add/', name: 'mbs_protocol_add', methods: ['GET', 'POST'])]
    #[Route('/project/add/', name: 'mbs_project_add', methods: ['GET', 'POST'])]
    #[Route('/status/add/', name: 'mbs_status_add', methods: ['GET', 'POST'])]
    #[Route('/condition/add/', name: 'mbs_condition_add', methods: ['GET', 'POST'])]
    #[Route('/condition/add/', name: 'mbs_modelcondition_add', methods: ['GET', 'POST'])]
    #[Route('/set/add/', name: 'mbs_modelset_add', methods: ['GET', 'POST'])]
    #[Route('/subcategory/add/', name: 'mbs_subcategory_add', methods: ['GET', 'POST'])]
    public function add(string $_route, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $route = explode("_", $_route);
        $database = $route[1];

        $classname = "App\\Entity\\" . $database;
        $value = new $classname();

        $form = $this->createFormBuilder($value)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);
        if($database=="subcategory") {
            $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
            $form->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}]);
        }
        if($database=="scaletrack") {
            $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
            $form->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}]);
        }
        if($database=="status") {
            $form->add('color', ChoiceType::class, ['required' => false, 'choices' => [
                $translator->trans('red') => 'red',
                $translator->trans('orange') => 'orange',
                $translator->trans('amber') => 'amber',
                $translator->trans('yellow') => 'yellow',
                $translator->trans('lime') => 'lime',
                $translator->trans('green') => 'green',
                $translator->trans('emerald') => 'emerald',
                $translator->trans('teal') => 'teal',
                $translator->trans('cyan') => 'cyan',
                $translator->trans('sky') => 'sky',
                $translator->trans('blue') => 'blue',
                $translator->trans('indigo') => 'indigo',
                $translator->trans('violet') => 'violet',
                $translator->trans('purple') => 'purple',
                $translator->trans('fuchsia') => 'fuchsia',
                $translator->trans('pink') => 'pink',
                $translator->trans('rose') => 'rose',
                $translator->trans('slate') => 'slate',
                $translator->trans('gray') => 'gray',
                $translator->trans('zinc') => 'zinc',
                $translator->trans('neutral') => 'neutral',
                $translator->trans('stone') => 'stone',
            ]]);
        }
        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $value->setUser($user);
            $value->setUuid(Uuid::v4());
            $entityManager->persist($value);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans($database.'.saved', ['name' => $value->getName()])
            );
            return $this->redirectToRoute('mbs_'.$database, ['id' => str_replace("0x","",$value->getUuid()->toHex())]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('dropdown/dropdown.html.twig', [
            "current" => $database,
            "disabled" => false,
            "databases" => $databases,
            "dropdownform" => $form->createView(),
            "dropdown" => $value,
        ]);
    }

    #[Route('/box/{id}', name: 'mbs_box', methods: ['GET', 'POST'])]
    #[Route('/scale/{id}', name: 'mbs_scale', methods: ['GET', 'POST'])]
    #[Route('/scaletrack/{id}', name: 'mbs_scaletrack', methods: ['GET', 'POST'])]
    #[Route('/axle/{id}', name: 'mbs_axle', methods: ['GET', 'POST'])]
    #[Route('/epoch/{id}', name: 'mbs_epoch', methods: ['GET', 'POST'])]
    #[Route('/subepoch/{id}', name: 'mbs_subepoch', methods: ['GET', 'POST'])]
    #[Route('/containertype/{id}', name: 'mbs_containertype', methods: ['GET', 'POST'])]
    #[Route('/coupler/{id}', name: 'mbs_coupler', methods: ['GET', 'POST'])]
    #[Route('/decoder/{id}', name: 'mbs_decoder', methods: ['GET', 'POST'])]
    #[Route('/edition/{id}', name: 'mbs_edition', methods: ['GET', 'POST'])]
    #[Route('/pininterface/{id}', name: 'mbs_pininterface', methods: ['GET', 'POST'])]
    #[Route('/power/{id}', name: 'mbs_power', methods: ['GET', 'POST'])]
    #[Route('/protocol/{id}', name: 'mbs_protocol', methods: ['GET', 'POST'])]
    #[Route('/project/{id}', name: 'mbs_project', methods: ['GET', 'POST'])]
    #[Route('/status/{id}', name: 'mbs_status', methods: ['GET', 'POST'])]
    #[Route('/condition/{id}', name: 'mbs_condition', methods: ['GET', 'POST'])]
    #[Route('/condition/{id}', name: 'mbs_modelcondition', methods: ['GET', 'POST'])]
    #[Route('/set/{id}', name: 'mbs_modelset', methods: ['GET', 'POST'])]
    #[Route('/subcategory/{id}', name: 'mbs_subcategory', methods: ['GET', 'POST'])]
    public function dropdown(mixed $id, string $_route, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, AuthorizationCheckerInterface $authChecker): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $route = explode("_", $_route);
        $database = $route[1];

        if(strlen($id)==32) {
            $value = $entityManager->getRepository('App\\Entity\\'.$database)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $value = $entityManager->getRepository('App\\Entity\\'.$database)->findOneBy(["id" => $id]);
        } else {
            $value = false;
        }

        if(!$value) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and is_object($value->getUser()) and $value->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        if(true === $authChecker->isGranted('ROLE_ADMIN') || $value->getUser()==$user) {
            $disabled = false;
        } else {
            $disabled = true;
        }

        $form = $this->createFormBuilder($value)
            ->add('name', TextType::class, ['disabled' => $disabled])
            ->add('save', SubmitType::class, ['disabled' => $disabled, 'label' => $translator->trans('global.save')]);

        if($database=="subcategory") {
            $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
            $form->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}]);
        }
        if($database=="status") {
            $form->add('color', ChoiceType::class, ['required' => false, 'choices' => [
                $translator->trans('red') => 'red',
                $translator->trans('orange') => 'orange',
                $translator->trans('amber') => 'amber',
                $translator->trans('yellow') => 'yellow',
                $translator->trans('lime') => 'lime',
                $translator->trans('green') => 'green',
                $translator->trans('emerald') => 'emerald',
                $translator->trans('teal') => 'teal',
                $translator->trans('cyan') => 'cyan',
                $translator->trans('sky') => 'sky',
                $translator->trans('blue') => 'blue',
                $translator->trans('indigo') => 'indigo',
                $translator->trans('violet') => 'violet',
                $translator->trans('purple') => 'purple',
                $translator->trans('fuchsia') => 'fuchsia',
                $translator->trans('pink') => 'pink',
                $translator->trans('rose') => 'rose',
                $translator->trans('slate') => 'slate',
                $translator->trans('gray') => 'gray',
                $translator->trans('zinc') => 'zinc',
                $translator->trans('neutral') => 'neutral',
                $translator->trans('stone') => 'stone',
            ]]);
        }

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $disabled===false) {
            $entityManager->persist($value);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans($database.'.saved', ['name' => $value->getName()])
            );
            return $this->redirectToRoute('mbs_'.$database, ['id' => str_replace("0x","",$value->getUuid()->toHex())]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->addFlash(
                'error',
                $translator->trans('model.resubmit', ['name' => $model->getName()])
            );
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_OK);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        if(!in_array($database, ['pininterface', 'axle', 'coupler', 'power', 'decoder', 'protocol', 'containertype', 'maker', 'condition','scaletrack'])) {
            $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $databases, $database => $value]);
        } elseif(in_array($database, ['condition'])) {
            $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $databases, 'modelcondition' => $value]);
        } elseif(in_array($database, ['scaletrack'])) {
            $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $databases, 'track' => $value]);
        } elseif(in_array($database, ['containertype','decoder','pininterface','protocol','axle','power','coupler'])) {
            $qb = $entityManager->createQueryBuilder();
            $models = $qb->select('m')->from(Model::class, 'm')
                ->leftJoin('m.category','c')
                ->leftJoin('m.subcategory','sub')
                ->leftJoin('m.status','status')
                ->leftJoin('m.storage','s')
                ->leftJoin('m.manufacturer','manu')
                ->where('m.modeldatabase in (:databases)')
                ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('value', $value)]));
            switch($database) {
                case "axle":
                    $models->leftJoin('m.locomotive','l');
                    $models->leftJoin('m.tram','t');
                    $models->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->eq('l.'.$database, ':value'),
                            $qb->expr()->eq('t.'.$database, ':value'),
                        )
                    );
                break;
                case "coupler":
                case "power":
                    $models->leftJoin('m.locomotive','l');
                    $models->leftJoin('m.tram','t');
                    $models->leftJoin('m.car','car');
                    $models->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->eq('l.'.$database, ':value'),
                            $qb->expr()->eq('t.'.$database, ':value'),
                            $qb->expr()->eq('car.'.$database, ':value'),
                        )
                    );
                break;
                case "containertype":
                    $models->leftJoin('m.container','cont');
                    $models->andWhere('cont.'.$database.' = :value');
                break;
                case "decoder":
                case "protocol":
                case "pininterface":
                    $models->leftJoin('m.digital','d');
                    $models->andWhere('d.'.$database.' = :value');
                break;
                default:
                    $models->andWhere('m.'.($database=='condition'?'modelcondition':$database).' = :value');
            }
            $models = $models->getQuery()->getResult();
        } else {
            $models = false;
        }
        return $this->render('dropdown/dropdown.html.twig', [
            "models" => $models,
            "databases" => $databases,
            "dropdownform" => $form->createView(),
            "dropdown" => $value,
            "current" => $database,
            "disabled" => $disabled,
        ]);
    }

    #[Route('/box/', name: 'mbs_box_list', methods: ['GET'])]
    #[Route('/scale/', name: 'mbs_scale_list', methods: ['GET'])]
    #[Route('/scaletrack/', name: 'mbs_scaletrack_list', methods: ['GET'])]
    #[Route('/scaletrack/', name: 'mbs_track_list', methods: ['GET'])]
    #[Route('/axle/', name: 'mbs_axle_list', methods: ['GET'])]
    #[Route('/epoch/', name: 'mbs_epoch_list', methods: ['GET'])]
    #[Route('/subepoch/', name: 'mbs_subepoch_list', methods: ['GET'])]
    #[Route('/containertype/', name: 'mbs_containertype_list', methods: ['GET'])]
    #[Route('/coupler/', name: 'mbs_coupler_list', methods: ['GET'])]
    #[Route('/decoder/', name: 'mbs_decoder_list', methods: ['GET'])]
    #[Route('/edition/', name: 'mbs_edition_list', methods: ['GET'])]
    #[Route('/pininterface/', name: 'mbs_pininterface_list', methods: ['GET'])]
    #[Route('/power/', name: 'mbs_power_list', methods: ['GET'])]
    #[Route('/protocol/', name: 'mbs_protocol_list', methods: ['GET'])]
    #[Route('/project/', name: 'mbs_project_list', methods: ['GET'])]
    #[Route('/status/', name: 'mbs_status_list', methods: ['GET'])]
    #[Route('/condition/', name: 'mbs_condition_list', methods: ['GET'])]
    #[Route('/condition/', name: 'mbs_modelcondition_list', methods: ['GET'])]
    #[Route('/set/', name: 'mbs_modelset_list', methods: ['GET'])]
    #[Route('/subcategory/', name: 'mbs_subcategory_list', methods: ['GET'])]
    public function index(string $_route, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $route = explode("_", $_route);
        $database = $route[1];

        $limit = $request->query->get('limit');
        $sortcolumn = $request->query->get('sortcolumn');
        $sortorder = $request->query->get('sortorder');
        $limits = $this->getParameter('limits');
        $sortcolumns = $this->getParameter($database.'.sortcolumns');
        if($sortcolumn!="" && in_array($sortcolumn, $sortcolumns)) {
            $request->getSession()->set('sortcolumn', $sortcolumn);
        } else {
            $request->getSession()->set('sortcolumn', $this->getParameter($database.'.sortcolumn'));
        }
        if($sortorder!="" && in_array($sortorder, ['asc', 'desc'])) {
            $request->getSession()->set('sortorder', $sortorder);
        } else {
            $request->getSession()->set('sortorder', $this->getParameter($database.'.sortorder'));
        }
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }

        $values = $entityManager->getRepository('App\\Entity\\'.$database)->findBy(array("user" => [null, $user->getId()]), [$request->getSession()->get('sortcolumn') => $request->getSession()->get('sortorder')]);

        $pagination = $paginator->paginate(
            $values,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('dropdown/list.html.twig', [
            "current" => $database,
            "databases" => $databases,
            "values" => $pagination
        ]);
    }

    #[Route('/box/{id}/models', name: 'mbs_box_models', methods: ['GET', 'POST'])]
    #[Route('/scale/{id}/models', name: 'mbs_scale_models', methods: ['GET', 'POST'])]
    #[Route('/scaletrack/{id}/models', name: 'mbs_scaletrack_models', methods: ['GET', 'POST'])]
    #[Route('/axle/{id}/models', name: 'mbs_axle_models', methods: ['GET', 'POST'])]
    #[Route('/epoch/{id}/models', name: 'mbs_epoch_models', methods: ['GET', 'POST'])]
    #[Route('/subepoch/{id}/models', name: 'mbs_subepoch_models', methods: ['GET', 'POST'])]
    #[Route('/coupler/{id}/models', name: 'mbs_coupler_models', methods: ['GET', 'POST'])]
    #[Route('/decoder/{id}/models', name: 'mbs_decoder_models', methods: ['GET', 'POST'])]
    #[Route('/edition/{id}/models', name: 'mbs_edition_models', methods: ['GET', 'POST'])]
    #[Route('/pininterface/{id}/models', name: 'mbs_pininterface_models', methods: ['GET', 'POST'])]
    #[Route('/power/{id}/models', name: 'mbs_power_models', methods: ['GET', 'POST'])]
    #[Route('/protocol/{id}/models', name: 'mbs_protocol_models', methods: ['GET', 'POST'])]
    #[Route('/project/{id}/models', name: 'mbs_project_models', methods: ['GET', 'POST'])]
    #[Route('/status/{id}/models', name: 'mbs_status_models', methods: ['GET', 'POST'])]
    #[Route('/condition/{id}/models', name: 'mbs_condition_models', methods: ['GET', 'POST'])]
    #[Route('/condition/{id}/models', name: 'mbs_modelcondition_models', methods: ['GET', 'POST'])]
    #[Route('/set/{id}/models', name: 'mbs_modelset_models', methods: ['GET', 'POST'])]
    #[Route('/containertype/{id}/models', name: 'mbs_containertype_models', methods: ['GET', 'POST'])]
    #[Route('/subcategory/{id}/models', name: 'mbs_subcategory_models', methods: ['GET', 'POST'])]
    public function models(mixed $id, string $_route, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $route = explode("_", $_route);
        $database = $route[1];

        $limit = $request->query->get('limit');
        $sortcolumn = $request->query->get('sortcolumn');
        $sortorder = $request->query->get('sortorder');
        $limits = $this->getParameter('limits');
        $sortcolumns = $this->getParameter('model.sortcolumns');
        if($sortcolumn!="" && in_array($sortcolumn, $sortcolumns)) {
            $request->getSession()->set('sortcolumn', $sortcolumn);
        } else {
            $request->getSession()->set('sortcolumn', $this->getParameter('model.sortcolumn'));
        }
        if($sortorder!="" && in_array($sortorder, ['asc', 'desc'])) {
            $request->getSession()->set('sortorder', $sortorder);
        } else {
            $request->getSession()->set('sortorder', $this->getParameter('model.sortorder'));
        }
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }

        if(strlen($id)==32) {
            $value = $entityManager->getRepository('App\\Entity\\'.$database)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $value = $entityManager->getRepository('App\\Entity\\'.$database)->findOneBy(["id" => $id]);
        } else {
            $value = false;
        }
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $field = $database;
        if($field=='condition') {
            $field = 'modelcondition';
        }
        if($field=='scaletrack') {
            $field = 'track';
        }
        $qb = $entityManager->createQueryBuilder();
        $models = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.category','c')
            ->leftJoin('m.subcategory','sub')
            ->leftJoin('m.status','status')
            ->leftJoin('m.storage','s')
            ->leftJoin('m.manufacturer','manu')
            ->where('m.modeldatabase in (:databases)')
            ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('value', $value)]))
            ->addOrderBy($request->getSession()->get('sortcolumn'), $request->getSession()->get('sortorder'));
        switch($database) {
            case "axle":
                $models->leftJoin('m.locomotive','l');
                $models->leftJoin('m.tram','t');
                $models->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('l.'.$database, ':value'),
                        $qb->expr()->eq('t.'.$database, ':value'),
                    )
                );
            break;
            case "coupler":
            case "power":
                $models->leftJoin('m.locomotive','l');
                $models->leftJoin('m.tram','t');
                $models->leftJoin('m.car','car');
                $models->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('l.'.$database, ':value'),
                        $qb->expr()->eq('t.'.$database, ':value'),
                        $qb->expr()->eq('car.'.$database, ':value'),
                    )
                );
            break;
            case "containertype":
                $models->leftJoin('m.container','cont');
                $models->andWhere('cont.'.$database.' = :value');
            break;
            case "decoder":
            case "protocol":
            case "pininterface":
                $models->leftJoin('m.digital','d');
                $models->andWhere('d.'.$database.' = :value');
            break;
            default:
            $models->andWhere('m.'.$field.' = :value');
        }
        $models->getQuery()->getResult();
        $pagination = $paginator->paginate(
            $models,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        return $this->render('collection/list.html.twig', [
            "current" => ($database=='condition'?'modelcondition':$database),
            "databases" => $databases,
            "models" => $pagination
        ]);
    }

    #[Route('/box/delete/{id}', name: 'mbs_box_delete', methods: ['GET'])]
    #[Route('/scale/delete/{id}', name: 'mbs_scale_delete', methods: ['GET'])]
    #[Route('/scaletrack/delete/{id}', name: 'mbs_scaletrack_delete', methods: ['GET'])]
    #[Route('/axle/delete/{id}', name: 'mbs_axle_delete', methods: ['GET'])]
    #[Route('/epoch/delete/{id}', name: 'mbs_epoch_delete', methods: ['GET'])]
    #[Route('/subepoch/delete/{id}', name: 'mbs_subepoch_delete', methods: ['GET'])]
    #[Route('/containertype/delete/{id}', name: 'mbs_containertype_delete', methods: ['GET'])]
    #[Route('/coupler/delete/{id}', name: 'mbs_coupler_delete', methods: ['GET'])]
    #[Route('/decoder/delete/{id}', name: 'mbs_decoder_delete', methods: ['GET'])]
    #[Route('/edition/delete/{id}', name: 'mbs_edition_delete', methods: ['GET'])]
    #[Route('/pininterface/delete/{id}', name: 'mbs_pininterface_delete', methods: ['GET'])]
    #[Route('/power/delete/{id}', name: 'mbs_power_delete', methods: ['GET'])]
    #[Route('/protocol/delete/{id}', name: 'mbs_protocol_delete', methods: ['GET'])]
    #[Route('/project/delete/{id}', name: 'mbs_project_delete', methods: ['GET'])]
    #[Route('/status/delete/{id}', name: 'mbs_status_delete', methods: ['GET'])]
    #[Route('/condition/delete/{id}', name: 'mbs_condition_delete', methods: ['GET'])]
    #[Route('/condition/delete/{id}', name: 'mbs_modelcondition_delete', methods: ['GET'])]
    #[Route('/set/delete/{id}', name: 'mbs_modelset_delete', methods: ['GET'])]
    #[Route('/subcategory/delete/{id}', name: 'mbs_subcategory_delete', methods: ['GET'])]
    public function delete(mixed $id, string $_route, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $route = explode("_", $_route);
        $database = $route[1];

        if(strlen($id)==32) {
            $value = $entityManager->getRepository('App\\Entity\\'.$database)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $value = $entityManager->getRepository('App\\Entity\\'.$database::class)->findOneBy(["id" => $id]);
        } else {
            $value = false;
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $qb = $entityManager->createQueryBuilder();
        $models = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.category','c')
            ->leftJoin('m.subcategory','sub')
            ->leftJoin('m.status','status')
            ->leftJoin('m.storage','s')
            ->leftJoin('m.manufacturer','manu')
            ->where('m.modeldatabase in (:databases)')
            ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('value', $value)]));
        switch($database) {
            case "axle":
                $models->leftJoin('m.locomotive','l');
                $models->leftJoin('m.tram','t');
                $models->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('l.'.$database, ':value'),
                        $qb->expr()->eq('t.'.$database, ':value'),
                    )
                );
                break;
            case "coupler":
            case "power":
                $models->leftJoin('m.locomotive','l');
                $models->leftJoin('m.tram','t');
                $models->leftJoin('m.car','car');
                $models->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('l.'.$database, ':value'),
                        $qb->expr()->eq('t.'.$database, ':value'),
                        $qb->expr()->eq('car.'.$database, ':value'),
                    )
                );
                break;
            case "containertype":
                $models->leftJoin('m.container','cont');
                $models->andWhere('cont.'.$database.' = :value');
                break;
            case "decoder":
            case "protocol":
            case "pininterface":
                $models->leftJoin('m.digital','d');
                $models->andWhere('d.'.$database.' = :value');
                break;
            default:
                $models->andWhere('m.'.($database=='condition'?'modelcondition':$database).' = :value');
        }
        $models = $models->getQuery()->getResult();

        if(count($models)>0) {
            $this->addFlash(
                'error',
                $translator->trans($database.'.has-models', ['count' => count($value->getModels()), 'name' => $value->getName()])
            );
            return $this->redirectToRoute('mbs_'.$database, ['id' => str_replace("0x","",$value->getUuid()->toHex())]);
        }
        if(!$value) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        $entityManager->remove($value);
        $this->addFlash(
            'success',
            $translator->trans($database.'.deleted', ['name' => $value->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_'.$database.'_list');

    }

}

