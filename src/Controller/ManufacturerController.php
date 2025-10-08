<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Country;
use App\Entity\Database;
use App\Entity\Manufacturer;
use App\Entity\Model;
use App\Entity\State;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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

class ManufacturerController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/manufacturer/add/', name: 'mbs_manufacturer_add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/logo/manufacturer')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $country        = $entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);
        $state          = $entityManager->getRepository(State::class)->findBy([], ['name' => 'ASC']);

        $manufacturer = new Manufacturer();

        $form = $this->createFormBuilder($manufacturer)
            ->add('name', TextType::class)
            ->add('email', EmailType::class, ['required' => false])
            ->add('url', UrlType::class, ['required' => false])
            ->add('street', TextType::class, ['required' => false])
            ->add('extra', TextType::class, ['required' => false])
            ->add('zip', TextType::class, ['required' => false, 'attr' => ['maxlength' => 10]])
            ->add('city', TextType::class, ['required' => false])
            ->add('facebook', UrlType::class, ['required' => false])
            ->add('instagram', UrlType::class, ['required' => false])
            ->add('youtube', UrlType::class, ['required' => false])
            ->add('tiktok', UrlType::class, ['required' => false])
            ->add('twitter', UrlType::class, ['required' => false])
            ->add('linkedin', UrlType::class, ['required' => false])
            ->add('abbr2', TextType::class, ['required' => false, 'attr' => ['maxlength' => 2]])
            ->add('abbr3', TextType::class, ['required' => false, 'attr' => ['maxlength' => 3]])
            ->add('gtin_base', IntegerType::class, ['required' => false, 'attr' => ['maxlength' => 7]])
            ->add('gtin_mode', ChoiceType::class, ['required' => true, 'choices' => [$translator->trans('global.gtin.calc') => 'calc' ,$translator->trans('global.gtin.use') => 'use', $translator->trans('global.gtin.ignore') => null]])
            ->add('country', ChoiceType::class, ['required' => false, 'choices' => $country, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('state', ChoiceType::class, ['required' => false, 'choices' => $state, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "state-option country-".$choice->getCountry()->getId()];}])
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
                    return $this->redirectToRoute('mbs_manufacturer', ['id' => $manufacturer->getId()]);
                    // ... handle exception if something happens during file upload
                }
                $manufacturer->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'logo/manufacturer/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
                if($imageFile->guessExtension()=='svg') {
                    $manufacturer->setVector(1);
                }
                $manufacturer->setLogo(1);
            } elseif(isset($currentImage) && $currentImage!="") {
                $manufacturer->setImage($currentImage);
            }
            $manufacturer->setLogo(0);
            $manufacturer->setVector(0);
            $manufacturer->setUser($user);
            $entityManager->persist($manufacturer);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('manufacturer.saved', ['name' => $manufacturer->getName()])
            );
            return $this->redirectToRoute('mbs_manufacturer', ['id' => $manufacturer->getId()]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('manufacturer/manufacturer.html.twig', [
            "disabled" => false,
            "databases" => $databases,
            "manufacturerform" => $form->createView(),
            "manufacturer" => $manufacturer,
        ]);
    }

    #[Route('/manufacturer/{id}', name: 'mbs_manufacturer', methods: ['GET', 'POST'])]
    public function manufacturer(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/logo/manufacturer')] string $imageDirectory, AuthorizationCheckerInterface $authChecker): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $manufacturer = $entityManager->getRepository(Manufacturer::class)->findOneBy(["id" => $id]);

        if(!$manufacturer) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and is_object($manufacturer->getUser()) and $manufacturer->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        if($manufacturer->getImage()!="") {
            $currentImage = $manufacturer->getImage();
        }

        if(true === $authChecker->isGranted('ROLE_ADMIN') || $manufacturer->getUser()==$user) {
            $disabled = false;
        } else {
            $disabled = true;
        }

        $country        = $entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);
        $state          = $entityManager->getRepository(State::class)->findBy([], ['name' => 'ASC']);

        $form = $this->createFormBuilder($manufacturer)
            ->add('name', TextType::class, ['disabled' => $disabled])
            ->add('email', EmailType::class, ['required' => false, 'disabled' => $disabled])
            ->add('url', UrlType::class, ['required' => false, 'disabled' => $disabled])
            ->add('street', TextType::class, ['required' => false, 'disabled' => $disabled])
            ->add('extra', TextType::class, ['required' => false, 'disabled' => $disabled])
            ->add('zip', TextType::class, ['required' => false, 'disabled' => $disabled, 'attr' => ['maxlength' => 10]])
            ->add('city', TextType::class, ['required' => false, 'disabled' => $disabled])
            ->add('facebook', UrlType::class, ['required' => false, 'disabled' => $disabled])
            ->add('instagram', UrlType::class, ['required' => false, 'disabled' => $disabled])
            ->add('youtube', UrlType::class, ['required' => false, 'disabled' => $disabled])
            ->add('tiktok', UrlType::class, ['required' => false, 'disabled' => $disabled])
            ->add('twitter', UrlType::class, ['required' => false, 'disabled' => $disabled])
            ->add('linkedin', UrlType::class, ['required' => false, 'disabled' => $disabled])
            ->add('abbr2', TextType::class, ['required' => false, 'disabled' => $disabled, 'attr' => ['maxlength' => 2]])
            ->add('abbr3', TextType::class, ['required' => false, 'disabled' => $disabled, 'attr' => ['maxlength' => 3]])
            ->add('gtin_base', IntegerType::class, ['required' => false, 'disabled' => $disabled, 'attr' => ['maxlength' => 7]])
            ->add('gtin_mode', ChoiceType::class, ['required' => true, 'disabled' => $disabled, 'choices' => [$translator->trans('global.gtin.calc') => 'calc' ,$translator->trans('global.gtin.use') => 'use', $translator->trans('global.gtin.ignore') => null]])
            ->add('country', ChoiceType::class, ['required' => false, 'disabled' => $disabled, 'choices' => $country, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('state', ChoiceType::class, ['required' => false, 'disabled' => $disabled, 'choices' => $state, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['class' => "state-option country-".$choice->getCountry()->getId()];}])
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
                    return $this->redirectToRoute('mbs_manufacturer', ['id' => $manufacturer->getId()]);
                    // ... handle exception if something happens during file upload
                }
                if(isset($currentImage) && file_exists($imageDirectory."/".$currentImage)) {
                    unlink($imageDirectory."/".$currentImage);
                }
                $manufacturer->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'logo/manufacturer/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
                if($extension=='svg') {
                    $manufacturer->setVector(1);
                }
                $manufacturer->setLogo(1);
            } elseif(isset($currentImage) && $currentImage!="") {
                $manufacturer->setImage($currentImage);
            }
            $entityManager->persist($manufacturer);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('manufacturer.saved', ['name' => $manufacturer->getName()])
            );
            return $this->redirectToRoute('mbs_manufacturer', ['id' => $manufacturer->getId()]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $databases, "manufacturer" => $manufacturer]);
        return $this->render('manufacturer/manufacturer.html.twig', [
            "models" => $models,
            "databases" => $databases,
            "manufacturerform" => $form->createView(),
            "manufacturer" => $manufacturer,
            "disabled" => $disabled,
        ]);
    }

    #[Route('/manufacturer/{id}/models', name: 'mbs_manufacturer_models', methods: ['GET', 'POST'])]
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

        $manufacturer = $entityManager->getRepository(Manufacturer::class)->find($id);
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $qb = $entityManager->createQueryBuilder();
        $models = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.category','c')
            ->leftJoin('m.subcategory','sub')
            ->leftJoin('m.status','status')
            ->leftJoin('m.storage','s')
            ->leftJoin('m.manufacturer','manu')
            ->where('m.modeldatabase in (:databases)')
            ->andWhere('m.manufacturer = :manufacturer')
            ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('manufacturer', $manufacturer)]))
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

    #[Route('/manufacturer/', name: 'mbs_manufacturer_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $limit = $request->query->get('limit');
        $sortcolumn = $request->query->get('sortcolumn');
        $sortorder = $request->query->get('sortorder');
        $limits = $this->getParameter('limits');
        $sortcolumns = $this->getParameter('manufacturer.sortcolumns');
        if($sortcolumn!="" && in_array($sortcolumn, $sortcolumns)) {
            $request->getSession()->set('sortcolumn', $sortcolumn);
        } else {
            $request->getSession()->set('sortcolumn', $this->getParameter('manufacturer.sortcolumn'));
        }
        if($sortorder!="" && in_array($sortorder, ['asc', 'desc'])) {
            $request->getSession()->set('sortorder', $sortorder);
        } else {
            $request->getSession()->set('sortorder', $this->getParameter('manufacturer.sortorder'));
        }
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }
        $manufacturers = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]), [$request->getSession()->get('sortcolumn') => $request->getSession()->get('sortorder'), $this->getParameter('company.sortcolumn') => $this->getParameter('company.sortorder')]);

        $pagination = $paginator->paginate(
            $manufacturers,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('manufacturer/list.html.twig', [
            "databases" => $databases,
            "manufacturers" => $pagination
        ]);
    }

    #[Route('/manufacturer/delete/{id}', name: 'mbs_manufacturer_delete', methods: ['GET'])]
    public function delete(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $manufacturer = $entityManager->getRepository(Manufacturer::class)->findOneBy(["id" => $id]);
        if(count($manufacturer->getModels())>0) {
            $this->addFlash(
                'error',
                $translator->trans('manufacturer.has-models', ['count' => count($manufacturer->getModels()), 'name' => $manufacturer->getName()])
            );
            return $this->redirectToRoute('mbs_manufacturer', ['id' => $manufacturer->getId()]);
        }
        if(!$manufacturer) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        $entityManager->remove($manufacturer);
        $this->addFlash(
            'success',
            $translator->trans('manufacturer.deleted', ['name' => $manufacturer->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_manufacturer_list');

    }

}
