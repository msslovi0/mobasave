<?php
namespace App\Controller;

use App\Entity\Country;
use App\Entity\Category;
use App\Entity\Database;
use App\Entity\Company;
use App\Entity\Maker;
use App\Entity\Model;
use App\Entity\State;
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

class MakerController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/maker/add/', name: 'mbs_maker_add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/logo/maker')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $maker = new Maker();

        $form = $this->createFormBuilder($maker)
            ->add('name', TextType::class)
            ->add('category', ChoiceType::class, ['required' => false, 'expanded' => true, 'mapped' => false, 'multiple' => true, 'choices' => $category, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('image', FileType::class, ['required' => false, 'data_class' => null, 'empty_data' => ''])
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
                    return $this->redirectToRoute('mbs_maker', ['id' => str_replace("0x","",$maker->getUuid()->toHex())]);
                    // ... handle exception if something happens during file upload
                }
                if(isset($currentImage) && file_exists($imageDirectory."/".$currentImage)) {
                    unlink($imageDirectory."/".$currentImage);
                }
                $maker->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'logo/maker/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
            } elseif(isset($currentImage) && $currentImage!="") {
                $maker->setImage($currentImage);
            }

            $categories = $form->get('category')->getData();
            foreach($categories as $category) {
                $maker->addCategory($category);
            }

            $maker->setUser($user);
            $maker->setUuid(Uuid::v4());
            $entityManager->persist($maker);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('maker.saved', ['name' => $maker->getName()])
            );
            return $this->redirectToRoute('mbs_maker', ['id' => str_replace("0x","",$maker->getUuid()->toHex())]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('maker/maker.html.twig', [
            "disabled" => false,
            "databases" => $databases,
            "makerform" => $form->createView(),
            "maker" => $maker,
        ]);
    }

    #[Route('/maker/{id}', name: 'mbs_maker', methods: ['GET', 'POST'])]
    public function maker(mixed $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/logo/maker')] string $imageDirectory, AuthorizationCheckerInterface $authChecker): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        if(strlen($id)==32) {
            $maker = $entityManager->getRepository(Maker::class)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $maker = $entityManager->getRepository(Maker::class)->findOneBy(["id" => $id]);
        } else {
            $maker = false;
        }

        if(!$maker) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and is_object($maker->getUser()) and $maker->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        if($maker->getImage()!="") {
            $currentImage = $maker->getImage();
        }

        $allCategories = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        if(true === $authChecker->isGranted('ROLE_ADMIN') || $maker->getUser()==$user) {
            $disabled = false;
        } else {
            $disabled = true;
        }
        foreach($maker->getCategory() as $categories) {
            $defaults[] = $entityManager->getRepository(Category::class)->findOneBy(array("id" => $categories->getId(), "user" => null));

        };

        $form = $this->createFormBuilder($maker)
            ->add('name', TextType::class, ['disabled' => $disabled])
            ->add('category', ChoiceType::class, ['required' => false, 'expanded' => true, 'data' => $defaults, 'mapped' => false, 'multiple' => true, 'choices' => $allCategories, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('image', FileType::class, ['required' => false, 'disabled' => $disabled, 'data_class' => null, 'empty_data' => ''])
            ->add('save', SubmitType::class, ['disabled' => $disabled, 'label' => $translator->trans('global.save')]);

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $disabled===false) {
            $imageFile = $form->get('image')->getData();
            $categories = $form->get('category')->getData();
            foreach($allCategories as $category) {
                $maker->removeCategory($category);
            }
            foreach($categories as $category) {
                $maker->addCategory($category);
            }

            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension();
                $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;
                try {
                    $imageFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $translator->trans('upload.failed', ['message' => $e->getMessage()])
                    );
                    return $this->redirectToRoute('mbs_maker', ['id' => str_replace("0x","",$maker->getUuid()->toHex())]);
                    // ... handle exception if something happens during file upload
                }
                $maker->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'logo/maker/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
            } elseif(isset($currentImage) && $currentImage!="") {
                $maker->setImage($currentImage);
            }
            $entityManager->persist($maker);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('maker.saved', ['name' => $maker->getName()])
            );
            return $this->redirectToRoute('mbs_maker', ['id' => str_replace("0x","",$maker->getUuid()->toHex())]);
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
        $qb = $entityManager->createQueryBuilder();
        $models = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.category','c')
            ->leftJoin('m.subcategory','sub')
            ->leftJoin('m.status','status')
            ->leftJoin('m.storage','s')
            ->leftJoin('m.manufacturer','manu')
            ->where('m.modeldatabase in (:databases)')
            ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('value', $maker)]));
        $models->leftJoin('m.locomotive','l');
        $models->leftJoin('m.tram','t');
        $models->leftJoin('m.vehicle','v');
        $models->andWhere(
            $qb->expr()->orX(
                $qb->expr()->eq('l.maker', ':value'),
                $qb->expr()->eq('t.maker', ':value'),
                $qb->expr()->eq('v.maker', ':value'),
            )
        );
        $models = $models->getQuery()->getResult();

        return $this->render('maker/maker.html.twig', [
            "current" => $maker,
            "models" => $models,
            "databases" => $databases,
            "makerform" => $form->createView(),
            "maker" => $maker,
            "disabled" => $disabled,
        ]);
    }

    #[Route('/maker/', name: 'mbs_maker_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $limit = $request->query->get('limit');
        $sortcolumn = $request->query->get('sortcolumn');
        $sortorder = $request->query->get('sortorder');
        $limits = $this->getParameter('limits');
        $sortcolumns = $this->getParameter('maker.sortcolumns');
        if($sortcolumn!="" && in_array($sortcolumn, $sortcolumns)) {
            $request->getSession()->set('sortcolumn', $sortcolumn);
        } else {
            $request->getSession()->set('sortcolumn', $this->getParameter('maker.sortcolumn'));
        }
        if($sortorder!="" && in_array($sortorder, ['asc', 'desc'])) {
            $request->getSession()->set('sortorder', $sortorder);
        } else {
            $request->getSession()->set('sortorder', $this->getParameter('maker.sortorder'));
        }
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }

        $makers = $entityManager->getRepository(Maker::class)->findBy(array("user" => [null, $user->getId()]), [$request->getSession()->get('sortcolumn') => $request->getSession()->get('sortorder')]);

        $pagination = $paginator->paginate(
            $makers,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('maker/list.html.twig', [
            "databases" => $databases,
            "makers" => $pagination
        ]);
    }

    #[Route('/maker/{id}/models', name: 'mbs_maker_models', methods: ['GET', 'POST'])]
    public function models(mixed $id, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

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
            $maker = $entityManager->getRepository(Maker::class)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $maker = $entityManager->getRepository(Maker::class)->findOneBy(["id" => $id]);
        } else {
            $maker = false;
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
            ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('value', $maker)]));
        $models->leftJoin('m.locomotive','l');
        $models->leftJoin('m.tram','t');
        $models->leftJoin('m.vehicle','v');
        $models->andWhere(
            $qb->expr()->orX(
                $qb->expr()->eq('l.maker', ':value'),
                $qb->expr()->eq('t.maker', ':value'),
                $qb->expr()->eq('v.maker', ':value'),
            )
        );
        $models = $models->getQuery()->getResult();
        $pagination = $paginator->paginate(
            $models,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        return $this->render('collection/list.html.twig', [
            "current" => 'maker',
            "databases" => $databases,
            "models" => $pagination
        ]);
    }

    #[Route('/maker/delete/{id}', name: 'mbs_maker_delete', methods: ['GET'])]
    public function delete(mixed $id, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        if(strlen($id)==32) {
            $maker = $entityManager->getRepository(Maker::class)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $maker = $entityManager->getRepository(Maker::class)->findOneBy(["id" => $id]);
        } else {
            $maker = false;
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
            ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('value', $maker)]));
        $models->leftJoin('m.locomotive','l');
        $models->leftJoin('m.tram','t');
        $models->leftJoin('m.vehicle','v');
        $models->andWhere(
            $qb->expr()->orX(
                $qb->expr()->eq('l.maker', ':value'),
                $qb->expr()->eq('t.maker', ':value'),
                $qb->expr()->eq('v.maker', ':value'),
            )
        );
        $models = $models->getQuery()->getResult();

        if(count($models)>0) {
            $this->addFlash(
                'error',
                $translator->trans('maker.has-models', ['count' => count($maker->getModels()), 'name' => $maker->getName()])
            );
            return $this->redirectToRoute('mbs_maker', ['id' => str_replace("0x","",$maker->getUuid()->toHex())]);
        }
        if(!$maker) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        $entityManager->remove($maker);
        $this->addFlash(
            'success',
            $translator->trans('maker.deleted', ['name' => $maker->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_maker_list');

    }

}

