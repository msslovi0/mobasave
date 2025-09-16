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
use App\Entity\State;
use App\Entity\Modelset;
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
use App\Entity\DigitalFunction;
use App\Entity\Functionkey;
use App\Entity\Decoderfunction;
use App\Entity\Description;
use App\Entity\Modelload;
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
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

class DatabaseController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }
    #[Route('/collection/create', name: 'mbs_database_create', methods: ['GET','POST'])]
    public function create(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/collection')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $database = new Database();

        $form = $this->createFormBuilder($database)
            ->add('name', TextType::class)
            ->add('color', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('image', FileType::class, ['data_class' => null, 'required' => false, 'constraints' => [
                new File(
                    extensions: ['jpg', 'webp', 'png', 'svg']
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
                    return $this->redirectToRoute('mbs_database_create', ['id' => $database->getId()]);
                }
                if(isset($currentImage) && file_exists($imageDirectory."/".$currentImage)) {
                    unlink($imageDirectory."/".$currentImage);
                }
                $database->setImage($newFilename);

                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'collection/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
            } elseif(isset($currentImage) && $currentImage!="") {
                $database->setImage($currentImage);
            }
            $database->setUser($user);
            $entityManager->persist($database);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('collection.saved', ['name' => $database->getName()])
            );
            return $this->redirectToRoute('mbs_database_edit', ['id' => $database->getId()]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->addFlash(
                'error',
                $translator->trans('collection.resubmit', ['name' => $database->getName()])
            );
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_OK);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('collection/database.html.twig', [
            "databases" => $databases,
            "databaseform" => $form->createView(),
            "database" => $database,
        ], response: $response);
    }
    #[Route('/collection/edit/{id}', name: 'mbs_database_edit', methods: ['GET','POST'])]
    public function edit(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/collection')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $database = $entityManager->getRepository(Database::class)->findOneBy(["id" => $id]);
        if($database->getImage()!="") {
            $currentImage = $database->getImage();
        }

        $form = $this->createFormBuilder($database)
            ->add('name', TextType::class)
            ->add('color', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('image', FileType::class, ['data_class' => null, 'required' => false, 'constraints' => [
                new File(
                    extensions: ['jpg', 'webp', 'png', 'svg']
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
                    return $this->redirectToRoute('mbs_database_edit', ['id' => $database->getId()]);
                }
                if(isset($currentImage) && file_exists($imageDirectory."/".$currentImage)) {
                    unlink($imageDirectory."/".$currentImage);
                }
                if(isset($currentImage) && file_exists($imageDirectory."/".$currentImage)) {
                    unlink($imageDirectory."/".$currentImage);
                }
                $database->setImage($newFilename);

                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'collection/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
            } elseif(isset($currentImage) && $currentImage!="") {
                $database->setImage($currentImage);
            }
            $database->setUser($user);
            $entityManager->persist($database);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('collection.saved', ['name' => $database->getName()])
            );
            return $this->redirectToRoute('mbs_database_edit', ['id' => $database->getId()]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->addFlash(
                'error',
                $translator->trans('collection.resubmit', ['name' => $database->getName()])
            );
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_OK);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('collection/database.html.twig', [
            "databases" => $databases,
            "databaseform" => $form->createView(),
            "database" => $database,
        ], response: $response);
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
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $database->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }
        $request->getSession()->set('database', $id);

        $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $database], ['purchased' => 'DESC']);

        $pagination = $paginator->paginate(
            $models,
            $request->query->getInt('page', 1), /* page number */
            100 /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('collection/list.html.twig', [
            "databases" => $databases,
            "database" => $database,
            "models" => $pagination
        ]);
    }
    #[Route('/model/search/', defaults: ['_format' => 'html'], name: 'mbs_model_search', methods: ['GET'])]
    #[Route('/model/autocomplete/', defaults: ['_format' => 'json'], name: 'mbs_model_autocomplete', methods: ['GET'])]
    public function search(string $_format, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $query = $request->get('search');
        $qb = $entityManager->createQueryBuilder();
        $result = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.locomotive','l','WITH','m.locomotive = l.id')
            ->leftJoin('m.car','c','WITH','m.car = c.id')
            ->leftJoin('m.container','o','WITH','m.container = o.id')
            ->leftJoin('m.vehicle','v','WITH','m.vehicle = v.id')
            ->leftJoin('m.tram','t','WITH','m.tram = t.id')
            ->leftJoin('m.dealer','d','WITH','m.dealer = d.id')
            ->leftJoin('m.manufacturer','w','WITH','m.manufacturer = w.id')
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
                $qb->expr()->like('d.name', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('w.name', $qb->expr()->literal('%' . $query . '%')),
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
            )->andWhere('m.modeldatabase in (:databases)')->setParameters(new ArrayCollection([new Parameter('databases',  $user->getUserdatabases())]))->getQuery()->getResult();

        $pagination = $paginator->paginate(
            $result,
            $request->query->getInt('page', 1), /* page number */
            $_format=="json" ? 10 : 100 /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('collection/list.'.$_format.'.twig', [
            "databases" => $databases,
            "models" => $pagination
        ]);
    }
    #[Route('/model/load/delete/{id}', name: 'mbs_model_load_delete', methods: ['GET'])]
    public function deleteLoad(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $modelload = $entityManager->getRepository(Modelload::class)->findOneBy(["id" =>$id]);
        $user = $this->security->getUser();
        $model = $modelload->getModel();
        $loaditem = $modelload->getLoaditem();
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        $model->setUpdated(new \DateTime());
        $loaditem->setUpdated(new \DateTime());
        $entityManager->remove($modelload);
        $entityManager->persist($model);
        $entityManager->persist($loaditem);
        $this->addFlash(
            'success',
            $translator->trans('model.load.deleted', ['load' => $loaditem->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_model_load', ['id' => $model->getId()]);

    }
    #[Route('/model/function/delete/{id}', name: 'mbs_model_function_delete', methods: ['GET'])]
    public function deleteFunction(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $digitalfunction = $entityManager->getRepository(DigitalFunction::class)->findOneBy(["id" =>$id]);
        $user = $this->security->getUser();
        $models = $digitalfunction->getDigital()->getModels();
        $model = $models[0];
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        $model->setUpdated(new \DateTime());
        $entityManager->remove($digitalfunction);
        $entityManager->persist($model);
        $this->addFlash(
            'success',
            $translator->trans('model.function.deleted', ['function' => $digitalfunction->getFunctionkey()->getName()." ".$digitalfunction->getDecoderfunction()->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_model_digital', ['id' => $model->getId()]);

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
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
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
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        $status         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $subcategory    = $entityManager->getRepository(Subcategory::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $manufacturer   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $company        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $scale          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $track          = $entityManager->getRepository(ScaleTrack::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $epoch          = $entityManager->getRepository(Epoch::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $subepoch       = $entityManager->getRepository(Subepoch::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $storage        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $project        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $dealer         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $box            = $entityManager->getRepository(Box::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $condition      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $country        = $entityManager->getRepository(Country::class)->findAll();
        $modelset       = $entityManager->getRepository(Modelset::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $model = new Model();

        $form = $this->createFormBuilder($model)
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, ['choices' => $status, 'choice_label' => 'name'])
            ->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('subcategory', ChoiceType::class, ['choices' => $subcategory, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "subcategory-option category-".$choice->getCategory()->getId()];}, 'required' => false])
            ->add('manufacturer', ChoiceType::class, ['choices' => $manufacturer, 'choice_label' => 'name', 'required' => false])
            ->add('company', ChoiceType::class, ['choices' => $company, 'choice_label' => 'name', 'required' => false])
            ->add('scale', ChoiceType::class, ['choices' => $scale, 'choice_label' => 'name', 'required' => false, 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('track', ChoiceType::class, ['choices' => $track, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "track-option scale-".$choice->getScale()->getId()];}, 'required' => false])
            ->add('epoch', ChoiceType::class, ['choices' => $epoch, 'choice_label' => function ($choice, string $key, mixed $value): TranslatableMessage|string {
                return $choice->getName()." (".$choice->getStart()."-".($choice->getEnd()!="" ? $choice->getEnd():"∞").")";
            }, 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}, 'required' => false])
            ->add('subepoch', ChoiceType::class, ['choices' => $subepoch, 'choice_label' => function ($choice, string $key, mixed $value): TranslatableMessage|string {
                return $choice->getName()." (".$choice->getStart()."-".($choice->getEnd()!="" ? $choice->getEnd():"∞").")";
            }, 'choice_attr' => function ($choice) {return ['class' => "subepoch-option epoch-".$choice->getEpoch()->getId()];}, 'required' => false])
            ->add('storage', ChoiceType::class, ['choices' => $storage, 'choice_label' => 'name', 'required' => false])
            ->add('project', ChoiceType::class, ['choices' => $project, 'choice_label' => 'name', 'required' => false])
            ->add('dealer', ChoiceType::class, ['choices' => $dealer, 'choice_label' => 'name', 'required' => false])
            ->add('box', ChoiceType::class, ['choices' => $box, 'choice_label' => 'name', 'required' => false])
            ->add('modelcondition', ChoiceType::class, ['choices' => $condition, 'choice_label' => 'name', 'required' => false])
            ->add('country', ChoiceType::class, ['choices' => $country, 'choice_label' => 'name', 'required' => false])
            ->add('modelset', ChoiceType::class, ['choices' => $modelset, 'choice_label' => 'name', 'required' => false])
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
            ->add('description', TextareaType::class, ['required' => false])
            ->add('image', FileType::class, ['data_class' => null, 'required' => false, 'constraints' => [
                new File(
                    extensions: ['jpg','webp','png','svg']
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
                $extension = $imageFile->guessExtension();
                $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;
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
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'image/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
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
            $entityManager->persist($digital);
            $model->setDigital($digital);
            $model->setAvailable(1);
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
        return $this->render('collection/model.html.twig', [
            "databases" => $databases,
            "modelform" => $form->createView(),
            "model" => $model,
        ], response: $response);
    }
    #[Route('/model/{id}', name: 'mbs_model', methods: ['GET','POST'])]
    #[Route('/dealer/{dealer}/model/{id}', name: 'mbs_dealer_model', methods: ['GET','POST'])]
    #[Route('/manufacturer/{manufacturer}/model/{id}', name: 'mbs_manufacturer_model', methods: ['GET','POST'])]
    #[Route('/company/{company}/model/{id}', name: 'mbs_company_model', methods: ['GET','POST'])]
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
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        } else {
            $request->getSession()->set('database', $model->getModeldatabase()->getId());
        }
        $qb             = $entityManager->createQueryBuilder();
        $status         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $subcategory    = $entityManager->getRepository(Subcategory::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $manufacturer   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $company        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $scale          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $track          = $entityManager->getRepository(ScaleTrack::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $epoch          = $entityManager->getRepository(Epoch::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $subepoch        =$entityManager->getRepository(Subepoch::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $storage        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $project        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $dealer         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $box            = $entityManager->getRepository(Box::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $condition      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $country        = $entityManager->getRepository(Country::class)->findAll();
        $modelset       = $entityManager->getRepository(Modelset::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $form = $this->createFormBuilder($model)
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, ['choices' => $status, 'choice_label' => 'name'])
            ->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('subcategory', ChoiceType::class, ['choices' => $subcategory, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "subcategory-option category-".$choice->getCategory()->getId()];}, 'required' => false])
            ->add('manufacturer', ChoiceType::class, ['choices' => $manufacturer, 'choice_label' => 'name', 'required' => false])
            ->add('company', ChoiceType::class, ['choices' => $company, 'choice_label' => 'name', 'required' => false])
            ->add('scale', ChoiceType::class, ['choices' => $scale, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}, 'required' => false])
            ->add('track', ChoiceType::class, ['choices' => $track, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "track-option scale-".$choice->getScale()->getId()];}, 'required' => false])
            ->add('epoch', ChoiceType::class, ['choices' => $epoch, 'choice_label' => function ($choice, string $key, mixed $value): TranslatableMessage|string {
                return $choice->getName()." (".$choice->getStart()."-".($choice->getEnd()!="" ? $choice->getEnd():"∞").")";
            }, 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}, 'required' => false])
            ->add('subepoch', ChoiceType::class, ['choices' => $subepoch, 'choice_label' => function ($choice, string $key, mixed $value): TranslatableMessage|string {
                return $choice->getName()." (".$choice->getStart()."-".($choice->getEnd()!="" ? $choice->getEnd():"∞").")";
            }, 'choice_attr' => function ($choice) {return ['class' => "subepoch-option epoch-".$choice->getEpoch()->getId()];}, 'required' => false])
            ->add('storage', ChoiceType::class, ['choices' => $storage, 'choice_label' => 'name', 'required' => false])
            ->add('project', ChoiceType::class, ['choices' => $project, 'choice_label' => 'name', 'required' => false])
            ->add('dealer', ChoiceType::class, ['choices' => $dealer, 'choice_label' => 'name', 'required' => false])
            ->add('box', ChoiceType::class, ['choices' => $box, 'choice_label' => 'name', 'required' => false])
            ->add('modelcondition', ChoiceType::class, ['choices' => $condition, 'choice_label' => 'name', 'required' => false])
            ->add('country', ChoiceType::class, ['choices' => $country, 'choice_label' => 'name', 'required' => false])
            ->add('modelset', ChoiceType::class, ['choices' => $modelset, 'choice_label' => 'name', 'required' => false])
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
            ->add('description', TextareaType::class, ['required' => false])
            ->add('image', FileType::class, ['data_class' => null, 'required' => false, 'constraints' => [
                new File(
                    extensions: ['jpg','webp','png','svg']
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
                }
                if(isset($currentImage) && file_exists($imageDirectory."/".$currentImage)) {
                    unlink($imageDirectory."/".$currentImage);
                }
                $model->setImage($newFilename);

                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'image/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
            } elseif(isset($currentImage) && $currentImage!="") {
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
        return $this->render('collection/model.html.twig', [
            "databases" => $databases,
            "modelform" => $form->createView(),
            "model" => $model,
        ], response: $response);
    }
    #[Route('/model/{id}/details/', name: 'mbs_model_detail', methods: ['GET','POST'])]
    #[Route('/dealer/{dealer}/model/{id}/details/', name: 'mbs_dealer_model_detail', methods: ['GET','POST'])]
    #[Route('/manufacturer/{manufacturer}/model/{id}/details/', name: 'mbs_manufacturer_model_detail', methods: ['GET','POST'])]
    #[Route('/company/{company}/model/{id}/details/', name: 'mbs_company_model_detail', methods: ['GET','POST'])]
    public function detail(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
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
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render($template, [
                "databases" => $databases,
                "detailform" => $form->createView(),
                "model" => $model,
                "detail" => $detail,
            ]);
        } else {
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render($template, [
                "databases" => $databases,
                "model" => $model,
            ]);
        }

    }
    #[Route('/model/{id}/digital/', name: 'mbs_model_digital', methods: ['GET','POST'])]
    #[Route('/dealer/{dealer}/model/{id}/digital/', name: 'mbs_dealer_model_digital', methods: ['GET','POST'])]
    #[Route('/manufacturer/{manufacturer}/model/{id}/digital/', name: 'mbs_manufacturer_model_digital', methods: ['GET','POST'])]
    #[Route('/company/{company}/model/{id}/digital/', name: 'mbs_company_model_digital', methods: ['GET','POST'])]
    public function digital(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        $protocol       = $entityManager->getRepository(Protocol::class)->findBy(array("user" => [null, $user->getId()]));
        $decoder        = $entityManager->getRepository(Decoder::class)->findBy(array("user" => [null, $user->getId()]));
        $interface      = $entityManager->getRepository(Pininterface::class)->findBy(array("user" => [null, $user->getId()]));
        $functionkey    = $entityManager->getRepository(Functionkey::class)->findBy([], ['sort' => 'ASC']);

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
        $functions = [];
        $qb = $entityManager->createQueryBuilder();
        $digitalfunctions    = $qb->select('df.id', 'df.hint','d.name as decoderfunction','f.name as functionkey', 'f.sort', 'd.light', 'd.sound')->from(DigitalFunction::class, 'df')->join(Functionkey::class, 'f')->join(Decoderfunction::class, 'd')->where('df.functionkey = f.id and df.decoderfunction=d.id and df.digital = :digital')->setParameters(new ArrayCollection([new Parameter('digital',  $digital)]))->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        foreach($digitalfunctions as $digitalfunction) {
            $functions[$digitalfunction['sort']][] = ["key" => $digitalfunction['functionkey'], "name" => $digitalfunction['decoderfunction'], "sound" => $digitalfunction['sound'], "light" => $digitalfunction['light'], "hint" => $digitalfunction['hint'], "id" => $digitalfunction['id']];
        }
        ksort($functions);

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render($template, [
            "databases" => $databases,
            "digitalform" => $form->createView(),
            "model" => $model,
            "digital" => $digital,
            "functions" => $functions,
            "functionkey" => $functionkey,
        ]);
    }
    #[Route('/model/{id}/load/', name: 'mbs_model_load', methods: ['GET','POST'])]
    #[Route('/dealer/{dealer}/model/{id}/load/', name: 'mbs_dealer_model_load', methods: ['GET','POST'])]
    #[Route('/manufacturer/{manufacturer}/model/{id}/load/', name: 'mbs_manufacturer_model_load', methods: ['GET','POST'])]
    #[Route('/company/{company}/model/{id}/load/', name: 'mbs_company_model_load', methods: ['GET','POST'])]
    public function load(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        $load = $entityManager->getRepository(Modelload::class)->findBy(array("model" => $model));

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('collection/load.html.twig', [
            "databases" => $databases,
            "model" => $model,
            "load" => $load,
        ]);
    }
    #[Route('/model/load/add/', name: 'mbs_model_load_add', methods: ['POST'])]
    public function addLoad(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->get('model');
        $load = $request->get('load');
        $user = $this->security->getUser();

        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        $loaditem = $entityManager->getRepository(Model::class)->findOneBy(["id" => $load]);

        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        $modelload = new Modelload();
        $modelload->setModel($model);
        $modelload->setLoaditem($loaditem);
        $model->setUpdated(new \DateTime('now'));
        $loaditem->setUpdated(new \DateTime('now'));
        $entityManager->persist($model);
        $entityManager->persist($loaditem);
        $entityManager->persist($modelload);
        $entityManager->flush();
        return new Response('ok');
    }

    #[Route('/model/function/add/', name: 'mbs_model_function_add', methods: ['POST'])]
    public function addFunction(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->get('model');
        $key = $request->get('key');
        $df = $request->get('decoderfunction');
        $sound = $request->get('sound')=="true" ? 1 : 0;
        $light = $request->get('light')=="true" ? 1 : 0;
        $user = $this->security->getUser();
        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);


        if(!$model) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }
        $functionkey = $entityManager->getRepository(Functionkey::class)->findOneBy(['id' => $key]);
        $decoderfunction = $entityManager->getRepository(Decoderfunction::class)->findOneBy(["name" => $df, "sound" => $sound, "light" => $light]);
        if(!is_object($decoderfunction)) {
            $decoderfunction = new Decoderfunction();
            $decoderfunction->setName($df);
            $decoderfunction->setLight($light);
            $decoderfunction->setSound($sound);
            $entityManager->persist($decoderfunction);
        }
        $digitalfunction = new DigitalFunction();
        $digitalfunction->setFunctionkey($functionkey);
        $digitalfunction->setDecoderfunction($decoderfunction);
        $digitalfunction->setDigital($model->getDigital());
        $entityManager->persist($digitalfunction);
        $entityManager->flush();

        return new Response('ok-'.(int)$sound."-".(int)$light);
    }
    #[Route('/model/value/new/', name: 'mbs_value_new', format: 'json', methods: ['POST'])]
    public function newValue(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $name = $request->get('name');
        $entity = $request->get('entity');
        $parent = $request->get('parent');
        $parent = (int)$parent+1;
        $user = $this->security->getUser();

        switch($entity) {
            case "protocol":
                $template = new Protocol();
                $repository = $entityManager->getRepository(Protocol::class);
            break;
            case "pininterface":
                $template = new Pininterface();
                $repository = $entityManager->getRepository(Pininterface::class);
            break;
            case "decoder":
                $template = new Decoder();
                $repository = $entityManager->getRepository(Decoder::class);
            break;
            case "category":
                $template = new Category();
                $repository = $entityManager->getRepository(Category::class);
            break;
            case "subcategory":
                $template = new Subcategory();
                $parententity = $entityManager->getRepository(Category::class)->findOneBy(array("id" => $parent));
                $parentname = "category";
                $template->setCategory($parententity);
                $repository = $entityManager->getRepository(Subcategory::class);
            break;
            case "manufacturer":
                $template = new Manufacturer();
                $template->setLogo(0);
                $template->setVector(0);
                $repository = $entityManager->getRepository(Manufacturer::class);
            break;
            case "company":
                $template = new Company();
                $template->setLogo(0);
                $template->setVector(0);
                $repository = $entityManager->getRepository(Company::class);
            break;
            case "country":
                $template = new Country();
                $repository = $entityManager->getRepository(Country::class);
            break;
            case "storage":
                $template = new Storage();
                $repository = $entityManager->getRepository(Storage::class);
            break;
            case "box":
                $template = new Box();
                $repository = $entityManager->getRepository(Box::class);
            break;
            case "project":
                $template = new Project();
                $repository = $entityManager->getRepository(Project::class);
            break;
            case "condition":
                $template = new Condition();
                $repository = $entityManager->getRepository(Condition::class);
            break;
            case "status":
                $template = new Status();
                $repository = $entityManager->getRepository(Status::class);
            break;
            case "dealer":
                $template = new Dealer();
                $repository = $entityManager->getRepository(Dealer::class);
            break;
            case "scale":
                $template = new Scale();
                $repository = $entityManager->getRepository(Scale::class);
            break;
            case "track":
                $template = new ScaleTrack();
                $parententity = $entityManager->getRepository(Scale::class)->findOneBy(array("id" => $parent));
                $parentname = "scale";
                $template->setScale($parententity);
                $repository = $entityManager->getRepository(ScaleTrack::class);
            break;
            case "epoch":
                $template = new Epoch();
                $repository = $entityManager->getRepository(Epoch::class);
            break;
            case "subepoch":
                $template = new Subepoch();
                $parententity = $entityManager->getRepository(Epoch::class)->findOneBy(array("id" => $parent));
                $parentname = "epoch";
                $template->setEpoch($parententity);
                $repository = $entityManager->getRepository(Subepoch::class);
            break;
            case "modelset":
                $template = new Modelset();
                $repository = $entityManager->getRepository(Modelset::class);
            break;
        }

        $new = $repository->findOneBy(["name" => $name]);
        if(!is_object($new)) {
            $new = $template;
            $new->setName($name);
            if($entity!="country") {
                $new->setUser($user);
            }
            $entityManager->persist($new);
            $entityManager->flush();
        }

        if(isset($parententity)) {
            $values = $repository->findBy(array($parentname => $parententity, "user" => [null, $user->getId()]), ['name' => 'ASC']);
        } elseif($entity!="country") {
            $values = $repository->findBy(array("user" => [null, $user->getId()]), ['name' => 'ASC']);
        } else {
            $values = $repository->findBy([], ['name' => 'ASC']);
        }
        foreach($values as $value) {
            $data[$value->getId()] = $value->getName();
        }
        return new JsonResponse($data);
    }
    #[Route('/api/subcategory/', name: 'mbs_api_subcategory', format: 'json', methods: ['POST'])]
    public function subcategory(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->get('id');
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->get('id');
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->get('id');
        $user = $this->security->getUser();
        $qb = $entityManager->createQueryBuilder();
        $subepochs        = $qb->select('se')->from(Subepoch::class, 'se')->where($qb->expr()->orX($qb->expr()->isNull('se.user'), $qb->expr()->eq('se.user', ':user'),))->andWhere('se.epoch = :epoch')->setParameters(new ArrayCollection([new Parameter('user',  $user->getId()), new Parameter('epoch',  $id)]))->getQuery()->getResult();

        foreach($subepochs as $subepoch) {
            $data[$subepoch->getId()] = $subepoch->getName()." (".$subepoch->getStart()."-".($subepoch->getEnd()!=""?$subepoch->getEnd():"∞").")";
        }

        if(isset($data)) {
            return new JsonResponse($data);
        } else {
            return new Response('{}');
        }
    }
    #[Route('/api/state/', name: 'mbs_api_state', format: 'json', methods: ['POST'])]
    public function state(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->get('id');
        $user = $this->security->getUser();
        $qb = $entityManager->createQueryBuilder();
        $states        = $qb->select('s')->from(State::class, 's')->where('s.country = :country')->setParameters(new ArrayCollection([new Parameter('country',  $id)]))->getQuery()->getResult();

        foreach($states as $state) {
            $data[$state->getId()] = $state->getName();
        }

        if(isset($data)) {
            return new JsonResponse($data);
        } else {
            return new Response('{}');
        }
    }
}
