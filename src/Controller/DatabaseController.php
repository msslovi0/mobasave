<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Containertype;
use App\Entity\Database;
use App\Entity\Manufacturer;
use App\Entity\Model;
use App\Entity\Status;
use App\Entity\Category;
use App\Entity\Subcategory;
use App\Entity\Company;
use App\Entity\Scale;
use App\Entity\ScaleTrack;
use App\Entity\Epoch;
use App\Entity\Subepoch;
use App\Entity\Storage;
use App\Entity\Project;
use App\Entity\Dealer;
use App\Entity\Country;
use App\Entity\Box;
use App\Entity\Condition;
use App\Entity\Digital;
use App\Entity\Axle;
use App\Entity\Power;
use App\Entity\Coupler;
use App\Entity\Locomotive;
use App\Entity\Car;
use App\Entity\Container;
use App\Entity\Vehicle;
use App\Entity\Tram;
use App\Entity\Decoder;
use App\Entity\Protocol;
use App\Entity\Pininterface;
use BcMath\Number;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

class DatabaseController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }
    #[Route('/collection/{id}', name: 'mbs_database', methods: ['GET'])]
    public function index(int $id, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $database = $entityManager->getRepository(Database::class)->findOneBy(["id" => $id]);
        if(!$database) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $this->render('status/notfound.html.twig', response: $response);
        }
        if(is_object($user) and $database->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $this->render('status/forbidden.html.twig', response: $response);
        }
        $request->getSession()->set('database', $id);

        $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $database], ['purchased' => 'DESC']);

        $pagination = $paginator->paginate(
            $models,
            $request->query->getInt('page', 1), /* page number */
            100 /* limit per page */
        );

        return $this->render('collection/list.html.twig', [
            "models" => $pagination
        ]);
    }
    #[Route('/model/search/', name: 'mbs_model_search', methods: ['GET'])]
    public function search(EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request)
    {
        $query = $request->get('search');
        $qb = $entityManager->createQueryBuilder();
        $result = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.locomotive','l','WITH','m.locomotive = l.id')
            ->leftJoin('m.car','c','WITH','m.car = c.id')
            ->leftJoin('m.container','o','WITH','m.container = o.id')
            ->leftJoin('m.vehicle','v','WITH','m.vehicle = v.id')
            ->leftJoin('m.tram','t','WITH','m.tram = t.id')
            ->leftJoin('l.maker','lm','WITH','l.maker = lm.id')
            ->leftJoin('v.maker','vm','WITH','v.maker = vm.id')
            ->leftJoin('t.maker','tm','WITH','t.maker = tm.id')
            ->leftJoin('o.containertype','ot','WITH','o.containertype = ot.id')
            ->where(
                $qb->expr()->like('m.name', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('m.gtin13', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('m.model', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('l.class', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('l.registration', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('l.nickname', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('c.class', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('c.registration', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('o.registration', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('v.class', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('v.registration', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('t.class', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('t.registration', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('t.nickname', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('lm.name', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('ot.name', $qb->expr()->literal('%' . $query . '%')),
            )->getQuery()->getResult();

        $pagination = $paginator->paginate(
            $result,
            $request->query->getInt('page', 1), /* page number */
            100 /* limit per page */
        );

        return $this->render('collection/list.html.twig', [
            "models" => $pagination
        ]);

    }
    #[Route('/model/delete/{id}', name: 'mbs_model_delete', methods: ['GET'])]
    public function delete(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $database = $request->getSession()->get('database');
        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $this->render('status/notfound.html.twig', response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $this->render('status/forbidden.html.twig', response: $response);
        }
        switch($model->getCategory()->getId()) {
            case 1:
                $detail = $model->getLocomotive();
                $entityManager->remove($detail);
                break;
            case 2:
                $detail = $model->getCar();
                $entityManager->remove($detail);
                break;
            case 3:
                $detail = $model->getContainer();
                $entityManager->remove($detail);
                break;
            case 6:
                $detail = $model->getVehicle();
                $entityManager->remove($detail);
                break;
            case 7:
                $detail = $model->getTram();
                $entityManager->remove($detail);
                break;
        }
        $digital = $model->getDigital();
        $entityManager->remove($digital);
        $entityManager->remove($model);
        $this->addFlash(
            'success',
            $translator->trans('model.deleted', ['name' => $model->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_database', ['id' => $database]);


    }


    #[Route('/model/add/', name: 'mbs_model_add', methods: ['GET','POST'])]
    public function add(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/image')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $id = $request->getSession()->get('database');
        $database = $entityManager->getRepository(Database::class)->findOneBy(["id" => $id]);

        if(is_object($user) and $database->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $this->render('status/forbidden.html.twig', response: $response);
        }

        $status         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, $user->getId()]));
        $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]));
        $subcategory    = $entityManager->getRepository(Subcategory::class)->findBy(array("user" => [null, $user->getId()]));
        $manufacturer   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]));
        $company        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, $user->getId()]));
        $scale          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, $user->getId()]));
        $track          = $entityManager->getRepository(ScaleTrack::class)->findBy(array("user" => [null, $user->getId()]));
        $epoch          = $entityManager->getRepository(Epoch::class)->findBy(array("user" => [null, $user->getId()]));
        $subepoch       = $entityManager->getRepository(Subepoch::class)->findBy(array("user" => [null, $user->getId()]));
        $storage        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]));
        $project        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, $user->getId()]));
        $dealer         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, $user->getId()]));
        $box            = $entityManager->getRepository(Box::class)->findBy(array("user" => [null, $user->getId()]));
        $condition      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, $user->getId()]));
        $country        = $entityManager->getRepository(Country::class)->findAll();

        $model = new Model();

        $form = $this->createFormBuilder($model)
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, ['choices' => $status, 'choice_label' => 'name'])
            ->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name'])
            ->add('subcategory', ChoiceType::class, ['choices' => $subcategory, 'choice_label' => 'name', 'required' => false])
            ->add('manufacturer', ChoiceType::class, ['choices' => $manufacturer, 'choice_label' => 'name', 'required' => false])
            ->add('company', ChoiceType::class, ['choices' => $company, 'choice_label' => 'name', 'required' => false])
            ->add('scale', ChoiceType::class, ['choices' => $scale, 'choice_label' => 'name', 'required' => false])
            ->add('track', ChoiceType::class, ['choices' => $track, 'choice_label' => 'name', 'required' => false])
            ->add('epoch', ChoiceType::class, ['choices' => $epoch, 'choice_label' => 'name', 'required' => false])
            ->add('subepoch', ChoiceType::class, ['choices' => $subepoch, 'choice_label' => 'name', 'required' => false])
            ->add('storage', ChoiceType::class, ['choices' => $storage, 'choice_label' => 'name', 'required' => false])
            ->add('project', ChoiceType::class, ['choices' => $project, 'choice_label' => 'name', 'required' => false])
            ->add('dealer', ChoiceType::class, ['choices' => $dealer, 'choice_label' => 'name', 'required' => false])
            ->add('box', ChoiceType::class, ['choices' => $box, 'choice_label' => 'name', 'required' => false])
            ->add('modelcondition', ChoiceType::class, ['choices' => $condition, 'choice_label' => 'name', 'required' => false])
            ->add('country', ChoiceType::class, ['choices' => $country, 'choice_label' => 'name', 'required' => false])
            ->add('instructions', CheckboxType::class, ['required' => false])
            ->add('parts', CheckboxType::class, ['required' => false])
            ->add('displaycase', CheckboxType::class, ['required' => false])
            ->add('weathered', CheckboxType::class, ['required' => false])
            ->add('enhanced', CheckboxType::class, ['required' => false])
            ->add('model', TextType::class)
            ->add('gtin13', NumberType::class, ['attr' => ['maxlength' => 13], 'required' => false])
            ->add('quantity', NumberType::class)
            ->add('purchased', DateType::class, ['required' => false])
            ->add('color1', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('color2', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('color3', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('msrp', MoneyType::class, ['required' => false])
            ->add('price', MoneyType::class, ['required' => false])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('image', FileType::class, ['data_class' => null, 'required' => false, 'constraints' => [
                new File(
                    extensions: ['jpg','webp','png']
                )
            ]])
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);

        $form = $form->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $translator->trans('upload.failed', ['message' => $e->getMessage()])
                    );
                    return $this->redirectToRoute('mbs_model', ['id' => $model->getId()]);
                    // ... handle exception if something happens during file upload
                }
                $model->setImage($newFilename);
            }
            switch($model->getCategory()->getId()) {
                case 1:
                $detail = new Locomotive();
                $detail->setDigital(0);
                $detail->setSound(0);
                $detail->setSmoke(0);
                $detail->setDccready(0);
                $entityManager->persist($detail);
                $model->setLocomotive($detail);
                break;
                case 2:
                $detail = new Car();
                $entityManager->persist($detail);
                $model->setCar($detail);
                break;
                case 3:
                $detail = new Container();
                $entityManager->persist($detail);
                $model->setContainer($detail);
                break;
                case 6:
                $detail = new Vehicle();
                $entityManager->persist($detail);
                $model->setVehicle($detail);
                break;
                case 7:
                $detail = new Tram();
                $entityManager->persist($detail);
                $model->setTram($detail);
                break;
            }
            $digital = new Digital();
            $model->setDigital($digital);
            $model->setCreated(new \DateTime('now'));
            $model->setUpdated(new \DateTime('now'));
            $model->setModeldatabase($database);
            $entityManager->persist($model);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('model.created', ['name' => $model->getName()])
            );
            return $this->redirectToRoute('mbs_model', ['id' => $model->getId()]);
        }

        return $this->render('collection/model.html.twig', [
            "modelform" => $form->createView(),
            "model" => $model,
        ]);
    }
    #[Route('/model/{id}', name: 'mbs_model', methods: ['GET','POST'])]
    public function model(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/image')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        if($model->getImage()!="") {
            $currentImage = $model->getImage();
        }
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $this->render('status/notfound.html.twig', response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $this->render('status/forbidden.html.twig', response: $response);
        }
        $qb             = $entityManager->createQueryBuilder();
        $status         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, $user->getId()]));
        $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]));
        $subcategory    = $qb->select('s')->from(Subcategory::class, 's')->where($qb->expr()->orX($qb->expr()->isNull('s.user'), $qb->expr()->eq('s.user', ':user'),))->andWhere('s.category = :category')->setParameters(new ArrayCollection([new Parameter('user',  $user->getId()), new Parameter('category',  $model->getCategory())]))->orderBy('s.name')->getQuery()->getResult();
        $manufacturer   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]));
        $company        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, $user->getId()]));
        $scale          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, $user->getId()]));
        $track          = $qb->select('t')->from(ScaleTrack::class, 't')->where($qb->expr()->orX($qb->expr()->isNull('t.user'), $qb->expr()->eq('t.user', ':user'),))->andWhere('t.scale = :scale')->setParameters(new ArrayCollection([new Parameter('user',  $user->getId()), new Parameter('scale',  $model->getScale())]))->getQuery()->getResult();
        $epoch          = $entityManager->getRepository(Epoch::class)->findBy(array("user" => [null, $user->getId()]));
        $subepoch        = $qb->select('se')->from(Subepoch::class, 'se')->where($qb->expr()->orX($qb->expr()->isNull('se.user'), $qb->expr()->eq('se.user', ':user'),))->andWhere('se.epoch = :epoch')->setParameters(new ArrayCollection([new Parameter('user',  $user->getId()), new Parameter('epoch',  $model->getEpoch())]))->getQuery()->getResult();
        $storage        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]));
        $project        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, $user->getId()]));
        $dealer         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, $user->getId()]));
        $box            = $entityManager->getRepository(Box::class)->findBy(array("user" => [null, $user->getId()]));
        $condition      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, $user->getId()]));
        $country        = $entityManager->getRepository(Country::class)->findAll();

        $form = $this->createFormBuilder($model)
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, ['choices' => $status, 'choice_label' => 'name'])
            ->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name'])
            ->add('subcategory', ChoiceType::class, ['choices' => $subcategory, 'choice_label' => 'name', 'required' => false])
            ->add('manufacturer', ChoiceType::class, ['choices' => $manufacturer, 'choice_label' => 'name', 'required' => false])
            ->add('company', ChoiceType::class, ['choices' => $company, 'choice_label' => 'name', 'required' => false])
            ->add('scale', ChoiceType::class, ['choices' => $scale, 'choice_label' => 'name', 'required' => false])
            ->add('track', ChoiceType::class, ['choices' => $track, 'choice_label' => 'name', 'required' => false])
            ->add('epoch', ChoiceType::class, ['choices' => $epoch, 'choice_label' => 'name', 'required' => false])
            ->add('subepoch', ChoiceType::class, ['choices' => $subepoch, 'choice_label' => 'name', 'required' => false])
            ->add('storage', ChoiceType::class, ['choices' => $storage, 'choice_label' => 'name', 'required' => false])
            ->add('project', ChoiceType::class, ['choices' => $project, 'choice_label' => 'name', 'required' => false])
            ->add('dealer', ChoiceType::class, ['choices' => $dealer, 'choice_label' => 'name', 'required' => false])
            ->add('box', ChoiceType::class, ['choices' => $box, 'choice_label' => 'name', 'required' => false])
            ->add('modelcondition', ChoiceType::class, ['choices' => $condition, 'choice_label' => 'name', 'required' => false])
            ->add('country', ChoiceType::class, ['choices' => $country, 'choice_label' => 'name', 'required' => false])
            ->add('instructions', CheckboxType::class, ['required' => false])
            ->add('parts', CheckboxType::class, ['required' => false])
            ->add('displaycase', CheckboxType::class, ['required' => false])
            ->add('weathered', CheckboxType::class, ['required' => false])
            ->add('enhanced', CheckboxType::class, ['required' => false])
            ->add('model', TextType::class)
            ->add('gtin13', NumberType::class, ['attr' => ['maxlength' => 13], 'required' => false])
            ->add('quantity', NumberType::class)
            ->add('purchased', DateType::class, ['required' => false])
            ->add('color1', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('color2', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('color3', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('msrp', MoneyType::class, ['required' => false])
            ->add('price', MoneyType::class, ['required' => false])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('image', FileType::class, ['data_class' => null, 'required' => false, 'constraints' => [
                new File(
                    extensions: ['jpg','webp','png']
                )
            ]])
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);

        $form = $form->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                $this->addFlash(
                    'error',
                    $translator->trans('upload.failed', ['message' => $e->getMessage()])
                );
                return $this->redirectToRoute('mbs_model', ['id' => $model->getId()]);
                    // ... handle exception if something happens during file upload
                }
                $model->setImage($newFilename);
            } elseif($currentImage!="") {
                $model->setImage($currentImage);
            }
            $model->setUpdated(new \DateTime('now'));
            $entityManager->persist($model);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('model.saved', ['name' => $model->getName()])
            );
            return $this->redirectToRoute('mbs_model', ['id' => $model->getId()]);
        }

        return $this->render('collection/model.html.twig', [
            "modelform" => $form->createView(),
            "model" => $model,
        ]);
    }
    #[Route('/model/{id}/details/', name: 'mbs_model_detail', methods: ['GET','POST'])]
    public function detail(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $this->render('status/notfound.html.twig', response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $this->render('status/forbidden.html.twig', response: $response);
        }

        $axle           = $entityManager->getRepository(Axle::class)->findBy(array("user" => [null, $user->getId()]));
        $power          = $entityManager->getRepository(Power::class)->findBy(array("user" => [null, $user->getId()]));
        $coupler        = $entityManager->getRepository(Coupler::class)->findBy(array("user" => [null, $user->getId()]));
        $maker          = $model->getCategory()->getMakers();

        switch($model->getCategory()->getId()) {
            case 1:
                $detail = $model->getLocomotive();
                $template = "collection/".$model->getCategory()->getClass().".html.twig";
                $form = $this->createFormBuilder($detail)
                    ->add('class', TextType::class, ['required' => false])
                    ->add('registration', TextType::class, ['required' => false])
                    ->add('length', NumberType::class, ['required' => false])
                    ->add('nickname', TextType::class, ['required' => false])
                    ->add('power', ChoiceType::class, ['choices' => $power, 'choice_label' => 'name', 'required' => false])
                    ->add('axle', ChoiceType::class, ['choices' => $axle, 'choice_label' => 'name', 'required' => false])
                    ->add('coupler', ChoiceType::class, ['choices' => $coupler, 'choice_label' => 'name', 'required' => false])
                    ->add('maker', ChoiceType::class, ['choices' => $maker, 'choice_label' => 'name', 'required' => false])
                    ->add('digital', CheckboxType::class, ['required' => false])
                    ->add('sound', CheckboxType::class, ['required' => false])
                    ->add('smoke', CheckboxType::class, ['required' => false])
                    ->add('dccready', CheckboxType::class, ['required' => false]);

            break;
            case 2:
                $detail = $model->getCar();
                $template = "collection/".$model->getCategory()->getClass().".html.twig";
                $form = $this->createFormBuilder($detail)
                    ->add('class', TextType::class, ['required' => false])
                    ->add('registration', TextType::class, ['required' => false])
                    ->add('length', NumberType::class, ['required' => false])
                    ->add('power', ChoiceType::class, ['choices' => $power, 'choice_label' => 'name', 'required' => false])
                    ->add('coupler', ChoiceType::class, ['choices' => $coupler, 'choice_label' => 'name', 'required' => false]);
            break;
            case 3:
                $detail = $model->getContainer();
                $containertype = $entityManager->getRepository(Containertype::class)->findBy(array("user" => [null, $user->getId()]));
                $template = "collection/".$model->getCategory()->getClass().".html.twig";
                $form = $this->createFormBuilder($detail)
                    ->add('registration', TextType::class, ['required' => false])
                    ->add('length', NumberType::class, ['required' => false])
                    ->add('containertype', ChoiceType::class, ['choices' => $containertype, 'choice_label' => 'name', 'required' => false]);
            break;
            case 6:
                $detail = $model->getVehicle();
                $template = "collection/".$model->getCategory()->getClass().".html.twig";
                $form = $this->createFormBuilder($detail)
                    ->add('class', TextType::class, ['required' => false])
                    ->add('registration', TextType::class, ['required' => false])
                    ->add('year', NumberType::class, ['required' => false, 'attr' => ['maxlength' => 4]])
                    ->add('maker', ChoiceType::class, ['choices' => $maker, 'choice_label' => 'name', 'required' => false]);
            break;
            case 7:
                $detail = $model->getTram();
                $template = "collection/".$model->getCategory()->getClass().".html.twig";
                $form = $this->createFormBuilder($detail)
                    ->add('class', TextType::class, ['required' => false])
                    ->add('registration', TextType::class, ['required' => false])
                    ->add('length', NumberType::class, ['required' => false])
                    ->add('nickname', TextType::class, ['required' => false])
                    ->add('power', ChoiceType::class, ['choices' => $power, 'choice_label' => 'name', 'required' => false])
                    ->add('axle', ChoiceType::class, ['choices' => $axle, 'choice_label' => 'name', 'required' => false])
                    ->add('coupler', ChoiceType::class, ['choices' => $coupler, 'choice_label' => 'name', 'required' => false])
                    ->add('maker', ChoiceType::class, ['choices' => $maker, 'choice_label' => 'name', 'required' => false]);
            break;
            default:
                $template = "collection/detail.html.twig";
        }

        if(isset($form)) {
            $form = $form->getForm();

            $form->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $model->setUpdated(new \DateTime('now'));
                $entityManager->persist($model);
                $entityManager->persist($detail);
                $entityManager->flush();
                $this->addFlash(
                    'success',
                    $translator->trans('model.saved', ['name' => $model->getName()])
                );
                return $this->redirectToRoute('mbs_model_detail', ['id' => $model->getId()]);
            }
            return $this->render($template, [
                "detailform" => $form->createView(),
                "model" => $model,
                "detail" => $detail,
            ]);
        } else {
            return $this->render($template, [
                "model" => $model,
            ]);
        }

    }
    #[Route('/model/{id}/digital/', name: 'mbs_model_digital', methods: ['GET','POST'])]
    public function digital(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $this->render('status/notfound.html.twig', response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $this->render('status/forbidden.html.twig', response: $response);
        }

        $protocol       = $entityManager->getRepository(Protocol::class)->findBy(array("user" => [null, $user->getId()]));
        $decoder        = $entityManager->getRepository(Decoder::class)->findBy(array("user" => [null, $user->getId()]));
        $interface      = $entityManager->getRepository(Pininterface::class)->findBy(array("user" => [null, $user->getId()]));

        $digital = $model->getDigital();
        $template = "collection/digital.html.twig";
        $form = $this->createFormBuilder($digital)
            ->add('address', NumberType::class, ['required' => false])
            ->add('protocol', ChoiceType::class, ['choices' => $protocol, 'choice_label' => 'name', 'required' => false])
            ->add('decoder', ChoiceType::class, ['choices' => $decoder, 'choice_label' => 'name', 'required' => false])
            ->add('pininterface', ChoiceType::class, ['choices' => $interface, 'choice_label' => 'name', 'required' => false]);

        $form = $form->getForm();

        $form->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $model->setUpdated(new \DateTime('now'));
            $entityManager->persist($model);
            $entityManager->persist($digital);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('model.saved', ['name' => $model->getName()])
            );
            return $this->redirectToRoute('mbs_model_digital', ['id' => $model->getId()]);
        }
        return $this->render($template, [
            "digitalform" => $form->createView(),
            "model" => $model,
            "digital" => $digital,
        ]);
    }
    #[Route('/api/subcategory/', name: 'mbs_api_subcategory', format: 'json', methods: ['POST'])]
    public function subcategory(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $id = $request->get('id');
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $qb = $entityManager->createQueryBuilder();
        $subcategories    = $qb->select('s')->from(Subcategory::class, 's')->where($qb->expr()->orX($qb->expr()->isNull('s.user'), $qb->expr()->eq('s.user', ':user'),))->andWhere('s.category = :category')->setParameters(new ArrayCollection([new Parameter('user',  $user->getId()), new Parameter('category',  $id)]))->orderBy('s.name')->getQuery()->getResult();

        foreach($subcategories as $subcategory) {
            $data[$subcategory->getId()] = $subcategory->getName();
        }

        if(isset($data)) {
            return new JsonResponse($data);
        } else {
            return new Response('{}');
        }
    }
    #[Route('/api/track/', name: 'mbs_api_track', format: 'json', methods: ['POST'])]
    public function track(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $id = $request->get('id');
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $qb = $entityManager->createQueryBuilder();
        $tracks          = $qb->select('t')->from(ScaleTrack::class, 't')->where($qb->expr()->orX($qb->expr()->isNull('t.user'), $qb->expr()->eq('t.user', ':user'),))->andWhere('t.scale = :scale')->setParameters(new ArrayCollection([new Parameter('user',  $user->getId()), new Parameter('scale',  $id)]))->getQuery()->getResult();

        foreach($tracks as $track) {
            $data[$track->getId()] = $track->getName();
        }

        if(isset($data)) {
            return new JsonResponse($data);
        } else {
            return new Response('{}');
        }
    }
    #[Route('/api/subepoch/', name: 'mbs_api_subepoch', format: 'json', methods: ['POST'])]
    public function subepoch(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $id = $request->get('id');
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $qb = $entityManager->createQueryBuilder();
        $subepochs        = $qb->select('se')->from(Subepoch::class, 'se')->where($qb->expr()->orX($qb->expr()->isNull('se.user'), $qb->expr()->eq('se.user', ':user'),))->andWhere('se.epoch = :epoch')->setParameters(new ArrayCollection([new Parameter('user',  $user->getId()), new Parameter('epoch',  $id)]))->getQuery()->getResult();

        foreach($subepochs as $subepoch) {
            $data[$subepoch->getId()] = $subepoch->getName();
        }

        if(isset($data)) {
            return new JsonResponse($data);
        } else {
            return new Response('{}');
        }
    }
}
