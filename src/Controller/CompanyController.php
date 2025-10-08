<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Country;
use App\Entity\Database;
use App\Entity\Company;
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
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/company/add/', name: 'mbs_company_add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/logo/company')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $country        = $entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);
        $state          = $entityManager->getRepository(State::class)->findBy([], ['name' => 'ASC']);

        $company = new Company();

        $form = $this->createFormBuilder($company)
            ->add('name', TextType::class)
            ->add('country', ChoiceType::class, ['required' => false, 'choices' => $country, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('state', ChoiceType::class, ['required' => false, 'choices' => $state, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "state-option country-".$choice->getCountry()->getId()];}])
            ->add('color1', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('color2', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('color3', ColorType::class, ['required' => false, 'attr' => ['alpha' => true]])
            ->add('image', FileType::class, ['required' => false, 'data_class' => null, 'empty_data' => ''])
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension();
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $translator->trans('upload.failed', ['message' => $e->getMessage()])
                    );
                    return $this->redirectToRoute('mbs_company', ['id' => $company->getId()]);
                    // ... handle exception if something happens during file upload
                }
                if(isset($currentImage) && file_exists($imageDirectory."/".$currentImage)) {
                    unlink($imageDirectory."/".$currentImage);
                }
                $company->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'logo/company/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
                if($extension=='svg') {
                    $company->setVector(1);
                }
                $company->setLogo(1);
            } elseif(isset($currentImage) && $currentImage!="") {
                $company->setImage($currentImage);
            }
            $company->setLogo(0);
            $company->setVector(0);
            $company->setUser($user);
            $entityManager->persist($company);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('company.saved', ['name' => $company->getName()])
            );
            return $this->redirectToRoute('mbs_company', ['id' => $company->getId()]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('company/company.html.twig', [
            "disabled" => false,
            "databases" => $databases,
            "companyform" => $form->createView(),
            "company" => $company,
        ]);
    }

    #[Route('/company/{id}', name: 'mbs_company', methods: ['GET', 'POST'])]
    public function company(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/logo/company')] string $imageDirectory, AuthorizationCheckerInterface $authChecker): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $company = $entityManager->getRepository(Company::class)->findOneBy(["id" => $id]);

        if(!$company) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and is_object($company->getUser()) and $company->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        if($company->getImage()!="") {
            $currentImage = $company->getImage();
        }

        $country        = $entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);
        $state          = $entityManager->getRepository(State::class)->findBy([], ['name' => 'ASC']);

        if(true === $authChecker->isGranted('ROLE_ADMIN') || $company->getUser()==$user) {
            $disabled = false;
        } else {
            $disabled = true;
        }

        $form = $this->createFormBuilder($company)
            ->add('name', TextType::class, ['disabled' => $disabled])
            ->add('country', ChoiceType::class, ['required' => false, 'disabled' => $disabled, 'choices' => $country, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('state', ChoiceType::class, ['required' => false, 'disabled' => $disabled, 'choices' => $state, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "state-option country-".$choice->getCountry()->getId()];}])
            ->add('color1', ColorType::class, ['required' => false, 'disabled' => $disabled, 'attr' => ['alpha' => true]])
            ->add('color2', ColorType::class, ['required' => false, 'disabled' => $disabled, 'attr' => ['alpha' => true]])
            ->add('color3', ColorType::class, ['required' => false, 'disabled' => $disabled, 'attr' => ['alpha' => true]])
            ->add('image', FileType::class, ['required' => false, 'disabled' => $disabled, 'data_class' => null, 'empty_data' => ''])
            ->add('save', SubmitType::class, ['disabled' => $disabled, 'label' => $translator->trans('global.save')]);

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $disabled===false) {
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
                    return $this->redirectToRoute('mbs_company', ['id' => $company->getId()]);
                    // ... handle exception if something happens during file upload
                }
                $company->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'logo/company/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
                if($extension=='svg') {
                    $company->setVector(1);
                }
                $company->setLogo(1);
            } elseif(isset($currentImage) && $currentImage!="") {
                $company->setImage($currentImage);
            }
            $entityManager->persist($company);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('company.saved', ['name' => $company->getName()])
            );
            return $this->redirectToRoute('mbs_company', ['id' => $company->getId()]);
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
        $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $databases, "company" => $company]);
        return $this->render('company/company.html.twig', [
            "models" => $models,
            "databases" => $databases,
            "companyform" => $form->createView(),
            "company" => $company,
            "disabled" => $disabled,
        ]);
    }

    #[Route('/company/', name: 'mbs_company_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $limit = $request->query->get('limit');
        $sortcolumn = $request->query->get('sortcolumn');
        $sortorder = $request->query->get('sortorder');
        $limits = $this->getParameter('limits');
        $sortcolumns = $this->getParameter('company.sortcolumns');
        if($sortcolumn!="" && in_array($sortcolumn, $sortcolumns)) {
            $request->getSession()->set('sortcolumn', $sortcolumn);
        } else {
            $request->getSession()->set('sortcolumn', $this->getParameter('company.sortcolumn'));
        }
        if($sortorder!="" && in_array($sortorder, ['asc', 'desc'])) {
            $request->getSession()->set('sortorder', $sortorder);
        } else {
            $request->getSession()->set('sortorder', $this->getParameter('company.sortorder'));
        }
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }

        $companies = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, $user->getId()]), [$request->getSession()->get('sortcolumn') => $request->getSession()->get('sortorder'), $this->getParameter('company.sortcolumn') => $this->getParameter('company.sortorder')]);

        $pagination = $paginator->paginate(
            $companies,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('company/list.html.twig', [
            "databases" => $databases,
            "companies" => $pagination
        ]);
    }

    #[Route('/company/{id}/models', name: 'mbs_company_models', methods: ['GET', 'POST'])]
    public function models(int $id, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
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

        $company = $entityManager->getRepository(Company::class)->find($id);
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $qb = $entityManager->createQueryBuilder();
        $models = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.category','c')
            ->leftJoin('m.subcategory','sub')
            ->leftJoin('m.status','status')
            ->leftJoin('m.storage','s')
            ->leftJoin('m.manufacturer','manu')
            ->where('m.modeldatabase in (:databases)')
            ->andWhere('m.company = :company')
            ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('company', $company)]))
            ->addOrderBy($request->getSession()->get('sortcolumn'), $request->getSession()->get('sortorder'))
            ->getQuery()
            ->getResult();
        $pagination = $paginator->paginate(
            $models,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        return $this->render('collection/list.html.twig', [
            "databases" => $databases,
            "models" => $pagination
        ]);
    }

    #[Route('/company/delete/{id}', name: 'mbs_company_delete', methods: ['GET'])]
    public function delete(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $company = $entityManager->getRepository(Company::class)->findOneBy(["id" => $id]);
        if(count($company->getModels())>0) {
            $this->addFlash(
                'error',
                $translator->trans('company.has-models', ['count' => count($company->getModels()), 'name' => $company->getName()])
            );
            return $this->redirectToRoute('mbs_company', ['id' => $company->getId()]);
        }
        if(!$company) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        $entityManager->remove($company);
        $this->addFlash(
            'success',
            $translator->trans('company.deleted', ['name' => $company->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_company_list');

    }

}

