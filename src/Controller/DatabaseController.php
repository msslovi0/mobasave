<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Containertype;
use App\Entity\Database;
use App\Entity\DocumentType;
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
use App\Entity\Document;
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

    #[Route('/database/delete/{id}', name: 'mbs_database_delete', methods: ['GET'])]
    public function deleteDatabase(mixed $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        if(strlen($id)==32) {
            $database = $entityManager->getRepository(Database::class)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $database = $entityManager->getRepository(Database::class)->findOneBy(["id" => $id]);
        } else {
            $database = false;
        }
        if(count($database->getModels())>0) {
            $this->addFlash(
                'error',
                $translator->trans('database.has-models', ['count' => count($database->getModels()), 'name' => $database->getName()])
            );
            return $this->redirectToRoute('mbs_database', ['id' => str_replace("0x","",$database->getUuid()->toHex())]);
        }
        if(!$database) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        $entityManager->remove($database);
        $this->addFlash(
            'success',
            $translator->trans('database.deleted', ['name' => $database->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_home');
    }

    #[Route('/collection/search/', defaults: ['_format' => 'html'], name: 'mbs_database_search', methods: ['GET'])]
    #[Route('/collection/{id}/filter/', defaults: ['_format' => 'html'], name: 'mbs_database_filter', methods: ['GET'])]
    #[Route('/collection/autocomplete/', defaults: ['_format' => 'json'], name: 'mbs_database_autocomplete', methods: ['GET'])]
    public function search(string $_format, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $limit = $request->query->get('limit');
        $allData = $request->query->all();
        $filterdata = [];
        if(array_key_exists('filter', $allData)) {
            $filter = $allData['filter'];
            if(is_array($filter)) {
                foreach($filter as $keyValue) {
                    $parts = explode("_", $keyValue);
                    $filterdata[$parts[0]][] = $parts[1];
                }
            }
        }
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

        $filters['category']       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['status']         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['condition']      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['storage']        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['project']        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['scale']          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['manufacturer']   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['dealer']         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['company']        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $dbPrefix['category'] = 'c';
        $dbPrefix['status'] = 'status';
        $dbPrefix['condition'] = 'condition';
        $dbPrefix['storage'] = 's';
        $dbPrefix['project'] = 'p';
        $dbPrefix['scale'] = 'scale';
        $dbPrefix['manufacturer'] = 'manu';
        $dbPrefix['dealer'] = 'd';
        $dbPrefix['company'] = 'co';

        $query = $request->get('search');
        $qb = $entityManager->createQueryBuilder();
        $result = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.locomotive','l','WITH','m.locomotive = l.id')
            ->leftJoin('m.car','car','WITH','m.car = car.id')
            ->leftJoin('m.container','o','WITH','m.container = o.id')
            ->leftJoin('m.vehicle','v','WITH','m.vehicle = v.id')
            ->leftJoin('m.tram','t','WITH','m.tram = t.id')
            ->leftJoin('m.dealer','d','WITH','m.dealer = d.id')
            ->leftJoin('m.manufacturer','manu','WITH','m.manufacturer = manu.id')
            ->leftJoin('m.company','co','WITH','m.company = co.id')
            ->leftJoin('m.storage','s','WITH','m.storage = s.id')
            ->leftJoin('l.maker','lm','WITH','l.maker = lm.id')
            ->leftJoin('v.maker','vm','WITH','v.maker = vm.id')
            ->leftJoin('t.maker','tm','WITH','t.maker = tm.id')
            ->leftJoin('m.category','c','WITH','m.category = c.id')
            ->leftJoin('m.subcategory','sub','WITH','m.subcategory = sub.id')
            ->leftJoin('o.containertype','ot','WITH','o.containertype = ot.id')
            ->leftJoin('m.status','status','WITH','m.status = status.id')
            ->leftJoin('m.modelcondition','condition','WITH','m.modelcondition = condition.id')
            ->leftJoin('m.project','p','WITH','m.project = p.id')
            ->leftJoin('m.scale','scale','WITH','m.scale = scale.id')
            ->where(
                $qb->expr()->like('m.name', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('m.gtin13', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('m.model', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('d.name', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('manu.name', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('co.name', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('s.name', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('l.class', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('l.registration', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('l.nickname', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('car.class', $qb->expr()->literal('%' . $query . '%')),
            )->orWhere(
                $qb->expr()->like('car.registration', $qb->expr()->literal('%' . $query . '%')),
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
            )->andWhere('m.modeldatabase in (:databases)')->setParameters(new ArrayCollection([new Parameter('databases',  $user->getUserdatabases())]));
            if($_format!="json") {
                $result->addOrderBy($request->getSession()->get('sortcolumn'), $request->getSession()->get('sortorder'));
            }

            if(count($filterdata)>0) {
                foreach($filterdata as $key => $value) {
                    $values = implode(',',$value);
                    $result->andWhere($dbPrefix[$key].'.id in ('.$values.')');
                }
//                print_r($filterdata);
            }

            $result = $result->getQuery()->getResult();

        $pagination = $paginator->paginate(
            $result,
            $request->query->getInt('page', 1), /* page number */
            $_format=="json" ? 10 : $limit /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('collection/list.'.$_format.'.twig', [
            "databases" => $databases,
            "models" => $pagination,
            "total" => count($result),
            "filters" => $filters,
            "filterdata" => $filterdata,
        ]);
    }
    #[Route('/collection/{id}', name: 'mbs_database', methods: ['GET'])]
    public function index(int $id, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
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

        $database = $entityManager->getRepository(Database::class)->findOneBy(["id" => $id]);
        if(!is_object($database)) {
            return $this->redirectToRoute('mbs_home');
        }
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

        $filters['category']       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['status']         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['condition']      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['storage']        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['project']        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['scale']          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['manufacturer']   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['dealer']         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $filters['company']        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $qb = $entityManager->createQueryBuilder();
        $models = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.category','c')
            ->leftJoin('m.subcategory','sub')
            ->leftJoin('m.status','status')
            ->leftJoin('m.storage','s')
            ->leftJoin('m.manufacturer','manu')
            ->where('m.modeldatabase = :database')
            ->setParameters(new ArrayCollection([new Parameter('database',  $id)]))
            ->addOrderBy($request->getSession()->get('sortcolumn'), $request->getSession()->get('sortorder'))
            ->getQuery()
            ->getResult();

        $pagination = $paginator->paginate(
            $models,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('collection/list.html.twig', [
            "databases" => $databases,
            "database" => $database,
            "models" => $pagination,
            "filters" => $filters,
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
        if(is_object($digital)) {
            foreach($digital->getDigitalFunctions() as $digitalFunction) {
                $entityManager->remove($digitalFunction);
            }
            $entityManager->remove($digital);
        }
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

        if(!is_object($database)) {
            return $this->redirectToRoute('mbs_home');
        }

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
        $country        = $entityManager->getRepository(Country::class)->findBy([], ["name" => "ASC"]);
        $modelset       = $entityManager->getRepository(Modelset::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $model = new Model();

        $form = $this->createFormBuilder($model)
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, ['choices' => $status, 'choice_label' => 'name'])
            ->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('subcategory', ChoiceType::class, ['choices' => $subcategory, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "subcategory-option category-".$choice->getCategory()->getId()];}, 'required' => false])
            ->add('manufacturer', ChoiceType::class, ['choices' => $manufacturer, 'choice_label' => 'name', 'required' => false, 'choice_attr' => function ($choice) {return ['data-gtin-base' => $choice->getGtinBase(), 'data-gtin-mode' => $choice->getGtinMode(), 'data-image' => $choice->getImage()];}])
            ->add('company', ChoiceType::class, ['choices' => $company, 'choice_label' => 'name', 'required' => false, 'choice_attr' => function ($choice) {return ['data-image' => $choice->getImage(), 'data-color1' => $choice->getColor1(), 'data-color2' => $choice->getColor2(), 'data-color3' => $choice->getColor3(), 'data-country' => is_object($choice->getCountry()) ? $choice->getCountry()->getIso2() : null];}])
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
            ->add('dealer', ChoiceType::class, ['choices' => $dealer, 'choice_label' => 'name', 'required' => false, 'choice_attr' => function ($choice) {return ['data-image' => $choice->getImage()];}])
            ->add('box', ChoiceType::class, ['choices' => $box, 'choice_label' => 'name', 'required' => false])
            ->add('modelcondition', ChoiceType::class, ['choices' => $condition, 'choice_label' => 'name', 'required' => false])
            ->add('country', ChoiceType::class, ['choices' => $country, 'choice_label' => 'name', 'required' => false, 'choice_attr' => function ($choice) {return ['data-image' => $choice->getIso2().".svg", 'data-iso' => $choice->getIso2()];}])
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
    #[Route('/storage/{storage}/model/{id}', name: 'mbs_storage_model', methods: ['GET','POST'])]
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
        $country        = $entityManager->getRepository(Country::class)->findBy([], ["name" => "ASC"]);
        $modelset       = $entityManager->getRepository(Modelset::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $form = $this->createFormBuilder($model)
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, ['choices' => $status, 'choice_label' => 'name'])
            ->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('subcategory', ChoiceType::class, ['choices' => $subcategory, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "subcategory-option category-".$choice->getCategory()->getId()];}, 'required' => false])
            ->add('manufacturer', ChoiceType::class, ['choices' => $manufacturer, 'choice_label' => 'name', 'required' => false, 'choice_attr' => function ($choice) {return ['data-gtin-base' => $choice->getGtinBase(), 'data-gtin-mode' => $choice->getGtinMode(), 'data-image' => $choice->getImage()];}])
            ->add('company', ChoiceType::class, ['choices' => $company, 'choice_label' => function ($choice): TranslatableMessage|string {
                return $choice->getAbbr()!="" ? $choice->getAbbr()." (".$choice->getName().")" : $choice->getName();}, 'required' => false, 'choice_attr' => function ($choice) {return ['data-image' => $choice->getImage(), 'data-color1' => $choice->getColor1(), 'data-color2' => $choice->getColor2(), 'data-color3' => $choice->getColor3(), 'data-country' => is_object($choice->getCountry()) ? $choice->getCountry()->getIso2() : null];}])
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
            ->add('dealer', ChoiceType::class, ['choices' => $dealer, 'choice_label' => 'name', 'required' => false, 'choice_attr' => function ($choice) {return ['data-image' => $choice->getImage()];}])
            ->add('box', ChoiceType::class, ['choices' => $box, 'choice_label' => 'name', 'required' => false])
            ->add('modelcondition', ChoiceType::class, ['choices' => $condition, 'choice_label' => 'name', 'required' => false])
            ->add('country', ChoiceType::class, ['choices' => $country, 'choice_label' => 'name', 'required' => false, 'choice_attr' => function ($choice) {return ['data-image' => $choice->getIso2().".svg", 'data-iso' => $choice->getIso2()];}])
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
                if(!is_object($detail)) {
                    $detail = new Locomotive();
                    $model->setCar($detail);
                }
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
                if(!is_object($detail)) {
                    $detail = new Car();
                    $model->setCar($detail);
                }
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
                if(!is_object($detail)) {
                    $detail = new Container();
                    $model->setCar($detail);
                }
                $containertype = $entityManager->getRepository(Containertype::class)->findBy(array("user" => [null, $user->getId()]));
                $template = "collection/".$model->getCategory()->getClass().".html.twig";
                $form = $this->createFormBuilder($detail)
                    ->add('registration', TextType::class, ['required' => false])
                    ->add('length', NumberType::class, ['required' => false])
                    ->add('containertype', ChoiceType::class, ['choices' => $containertype, 'choice_label' => 'name', 'required' => false]);
            break;
            case 6:
                $detail = $model->getVehicle();
                if(!is_object($detail)) {
                    $detail = new Vehicle();
                    $model->setCar($detail);
                }
                $template = "collection/".$model->getCategory()->getClass().".html.twig";
                $form = $this->createFormBuilder($detail)
                    ->add('class', TextType::class, ['required' => false])
                    ->add('registration', TextType::class, ['required' => false])
                    ->add('year', NumberType::class, ['required' => false, 'attr' => ['maxlength' => 4]])
                    ->add('maker', ChoiceType::class, ['choices' => $maker, 'choice_label' => 'name', 'required' => false]);
            break;
            case 7:
                $detail = $model->getTram();
                if(!is_object($detail)) {
                    $detail = new Tram();
                    $model->setCar($detail);
                }
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
    #[Route('/model/{id}/duplicate/', name: 'mbs_model_duplicate', methods: ['GET'])]
    public function duplicate(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/image')] string $imageDirectory): Response
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

        $duplicate = new Model();
        $duplicate->setName($model->getName());
        $duplicate->setCategory($model->getCategory());
        $duplicate->setSubcategory($model->getSubcategory());
        $duplicate->setManufacturer($model->getManufacturer());
        $duplicate->setCompany($model->getCompany());
        $duplicate->setScale($model->getScale());
        $duplicate->setTrack($model->getTrack());
        $duplicate->setEpoch($model->getEpoch());
        $duplicate->setSubepoch($model->getSubepoch());
        $duplicate->setStorage($model->getStorage());
        $duplicate->setProject($model->getProject());
        $duplicate->setDealer($model->getDealer());
        $duplicate->setName($model->getName());
        $duplicate->setModel($model->getModel());
        $duplicate->setGtin13($model->getGtin13());
        $duplicate->setColor1($model->getColor1());
        $duplicate->setColor2($model->getColor2());
        $duplicate->setColor3($model->getColor3());
        $duplicate->setQuantity($model->getQuantity());
        $duplicate->setPurchased(new \DateTime('now'));
        $duplicate->setMsrp($model->getMsrp());
        $duplicate->setPrice($model->getPrice());
        $duplicate->setNotes($model->getNotes());
        if(is_object($model->getLocomotive())) {
            $duplicateLocomotive = new Locomotive();
            $duplicateLocomotive->setMaker($model->getLocomotive()->getMaker());
            $duplicateLocomotive->setAxle($model->getLocomotive()->getAxle());
            $duplicateLocomotive->setPower($model->getLocomotive()->getPower());
            $duplicateLocomotive->setCoupler($model->getLocomotive()->getCoupler());
            $duplicateLocomotive->setClass($model->getLocomotive()->getClass());
            $duplicateLocomotive->setRegistration($model->getLocomotive()->getRegistration());
            $duplicateLocomotive->setLength($model->getLocomotive()->getLength());
            $duplicateLocomotive->setDigital($model->getLocomotive()->getDigital());
            $duplicateLocomotive->setSound($model->getLocomotive()->getSound());
            $duplicateLocomotive->setSmoke($model->getLocomotive()->getSmoke());
            $duplicateLocomotive->setDccready($model->getLocomotive()->getDccready());
            $duplicateLocomotive->setNickname($model->getLocomotive()->getNickname());
            $entityManager->persist($duplicateLocomotive);
            $duplicate->setLocomotive($duplicateLocomotive);
        }
        if(is_object($model->getContainer())) {
            $duplicateContainer = new Container();
            $duplicateContainer->setContainertype($model->getContainer()->getContainertype());
            $duplicateContainer->setRegistration($model->getContainer()->getRegistration());
            $duplicateContainer->setLength($model->getContainer()->getLength());
            $entityManager->persist($duplicateContainer);
            $duplicate->setContainer($duplicateContainer);
        }
        if(is_object($model->getCar())) {
            $duplicateCar = new Car();
            $duplicateCar->setRegistration($model->getCar()->getRegistration());
            $duplicateCar->setLength($model->getCar()->getLength());
            $duplicateCar->setPower($model->getCar()->getPower());
            $duplicateCar->setCoupler($model->getCar()->getCoupler());
            $duplicateCar->setClass($model->getCar()->getClass());
            $entityManager->persist($duplicateCar);
            $duplicate->setCar($duplicateCar);
        }
        if(is_object($model->getVehicle())) {
            $duplicateVehicle = new Vehicle();
            $duplicateVehicle->setRegistration($model->getVehicle()->getRegistration());
            $duplicateVehicle->setMaker($model->getVehicle()->getMaker());
            $duplicateVehicle->setClass($model->getVehicle()->getClass());
            $duplicateVehicle->setYear($model->getVehicle()->getYear());
            $entityManager->persist($duplicateVehicle);
            $duplicate->setVehicle($duplicateVehicle);
        }
        if(is_object($model->getTram())) {
            $duplicateTram = new Tram();
            $duplicateTram->setRegistration($model->getTram()->getRegistration());
            $duplicateTram->setMaker($model->getTram()->getMaker());
            $duplicateTram->setClass($model->getTram()->getClass());
            $duplicateTram->setPower($model->getTram()->getPower());
            $duplicateTram->setCoupler($model->getTram()->getCoupler());
            $duplicateTram->setAxle($model->getTram()->getAxle());
            $duplicateTram->setLength($model->getTram()->getLength());
            $duplicateTram->setNickname($model->getTram()->getNickname());
            $entityManager->persist($duplicateTram);
            $duplicate->setTram($duplicateTram);
        }
        if(is_object($model->getDigital())) {
            $duplicateDigital = new Digital();
            $duplicateDigital->setAddress($model->getDigital()->getAddress());
            $duplicateDigital->setProtocol($model->getDigital()->getProtocol());
            $duplicateDigital->setDecoder($model->getDigital()->getDecoder());
            $duplicateDigital->setPininterface($model->getDigital()->getPininterface());
            $entityManager->persist($duplicateDigital);
            $duplicate->setDigital($duplicateDigital);
            foreach($model->getDigital()->getDigitalFunctions() as $digitalFunction) {
                $duplicateDigitalFunction = new DigitalFunction();
                $duplicateDigitalFunction->setDigital($duplicateDigital);
                $duplicateDigitalFunction->setFunctionkey($digitalFunction->getFunctionkey());
                $duplicateDigitalFunction->setDecoderfunction($digitalFunction->getDecoderfunction());
                $duplicateDigitalFunction->setHint($digitalFunction->getHint());
                $entityManager->persist($duplicateDigitalFunction);
            }
        }
        $duplicate->setModeldatabase($model->getModeldatabase());
        $duplicate->setCountry($model->getCountry());
        $duplicate->setCreated(new \DateTime('now'));
        $duplicate->setUpdated(new \DateTime('now'));
        $image = $model->getImage();
        $originalFilename = pathinfo($image, PATHINFO_FILENAME);
        $originalExtension = pathinfo($image, PATHINFO_EXTENSION);
        $originalFileParts = explode("-", $originalFilename);
        $safeFilename = $slugger->slug($originalFileParts[0]);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$originalExtension;
        copy($imageDirectory.'/'.$model->getImage(), $imageDirectory.'/'.$newFilename);
        if($this->getParameter('remote_ssh')!="") {
            try {
                exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'image/'.$newFilename);
            } catch (Exception $e) {
            }
        }
        $duplicate->setImage($newFilename);
        $duplicate->setInstructions($model->isInstructions());
        $duplicate->setParts($model->isParts());
        $duplicate->setDisplaycase($model->isDisplaycase());
        $duplicate->setWeathered($model->isWeathered());
        $duplicate->setEnhanced($model->isEnhanced());
        $duplicate->setStatus($model->getStatus());
        $duplicate->setBox($model->getBox());
        $duplicate->setModelcondition($model->getModelcondition());
        $duplicate->setListprice($model->getListprice());
        $duplicate->setDescription($model->getDescription());
        $duplicate->setAvailable($model->isAvailable());
        $duplicate->setPower($model->getPower());
        $duplicate->setEdition($model->getEdition());
        $entityManager->persist($duplicate);
        $entityManager->flush();
        $this->addFlash(
            'success',
            $translator->trans('model.duplicated', ['name' => $duplicate->getName()])
        );
        return $this->redirectToRoute('mbs_model', ['id' => $duplicate->getId()]);

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


    #[Route('/model/{id}/document/', name: 'mbs_model_document', methods: ['GET','POST'])]
    #[Route('/dealer/{dealer}/model/{id}/document/', name: 'mbs_dealer_model_document', methods: ['GET','POST'])]
    #[Route('/manufacturer/{manufacturer}/model/{id}/document/', name: 'mbs_manufacturer_model_document', methods: ['GET','POST'])]
    #[Route('/company/{company}/model/{id}/document/', name: 'mbs_company_model_document', methods: ['GET','POST'])]
    public function document(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/document')] string $documentDirectory): Response
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
        $documenttype = $entityManager->getRepository(DocumentType::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $document = new Document();
        $form = $this->createFormBuilder($document)
            ->add('documenttype', ChoiceType::class, ['choices' => $documenttype, 'choice_label' => 'name'])
            ->add('file', FileType::class, ['data_class' => null, 'required' => false, 'constraints' => [
                new File(
                    extensions: ['jpg', 'webp', 'png', 'svg', 'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']
                )
            ]])
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);

        $form = $form->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $documentFile = $form->get('file')->getData();

            if($documentFile) {
                $originalFilename = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$documentFile->guessExtension();
                try {
                    $documentFile->move($documentDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $translator->trans('upload.failed', ['message' => $e->getMessage()])
                    );
                    return $this->redirectToRoute('mbs_model_document', ['id' => $model->getId()]);
                }
                $document->setFile($newFilename);

                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$documentDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'document/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
                $document->setName($originalFilename);
            } else {
                $this->addFlash(
                    'error',
                    $translator->trans('upload.failed', ['message' => 'ABC'])
                );
                return $this->redirectToRoute('mbs_model_document', ['id' => $model->getId()]);
            }
            $document->setModel($model);
            $entityManager->persist($document);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('upload.success')
            );
            return $this->redirectToRoute('mbs_model_document', ['id' => $model->getId()]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->addFlash(
                'error',
                $translator->trans('document.resubmit', ['name' => $database->getName()])
            );
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_OK);
        }


        $documents = $model->getDocuments();

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);

        return $this->render('collection/document.html.twig', [
            "databases" => $databases,
            "model" => $model,
            "documents" => $documents,
            "documentform" => $form->createView(),
        ]);
    }
    #[Route('/model/document/delete/{id}', name: 'mbs_model_document_delete', methods: ['GET'])]
    public function deleteDocument(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request, #[Autowire('%kernel.project_dir%/public/data/document')] string $documentDirectory)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $document = $entityManager->getRepository(Document::class)->findOneBy(["id" =>$id]);
        $user = $this->security->getUser();
        $model = $document->getModel();
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

        if(file_exists($documentDirectory."/".$document->getFile())) {
            unlink($documentDirectory."/".$document->getFile());
        }

        $model->setUpdated(new \DateTime());
        $entityManager->remove($document);
        $entityManager->persist($model);
        $this->addFlash(
            'success',
            $translator->trans('model.document.deleted', ['name' => $document->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_model_document', ['id' => $model->getId()]);

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
                $template->setLogo(0);
                $template->setVector(0);
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

    #[Route('/api/checksum/', name: 'mbs_api_checksum', format: 'json', methods: ['POST'])]
    public function checksum(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $value = $request->get('value');

        $char2num = ['A' => 10, 'B' => 12, 'C' => 13, 'D' => 14, 'E' => 15, 'F' => 16, 'G' => 17, 'H' => 18, 'I' => 19, 'J' => 20, 'K' => 21, 'L' => 23, 'M' => 24, 'N' => 25, 'O' => 26, 'P' => 27, 'Q' => 28, 'R' => 29, 'S' => 30, 'T' => 31, 'U' => 32, 'V' => 34, 'W' => 35, 'X' => 36, 'Y' => 37, 'Z' => 38];

        $acc = 0;
        $num = str_split($value);
        for($i=0;$i<10;$i++){
            if($i<4) $acc += ($char2num[$num[$i]]*pow(2,$i));
            else $acc += $num[$i]*pow(2,$i);
        }
        $rem = $acc % 11;
        if ($rem == 10) $rem = 0;
        $return['checksum'] = $rem;
        if(strlen($value)==11 && $num[10]==$rem) {
            $return['success'] = true;
            $return['message'] = $translator->trans('model.registration.valid');
        } elseif(strlen($value)==11 && $num[10]!=$rem) {
            $return['success'] = false;
            $return['message'] = $translator->trans('model.registration.invalid', ['checksum' => $rem, 'value' => $num[10]]);
        } elseif(strlen($value)==10) {
            $return['success'] = false;
            $return['message'] = $translator->trans('model.registration.checksum', ['checksum' => $rem]);
        } else {
            $return['success'] = false;
            $return['message'] = $translator->trans('model.registration.error');

        }
        return new JsonResponse($return);
    }

}
