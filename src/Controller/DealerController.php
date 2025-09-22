<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Country;
use App\Entity\Database;
use App\Entity\Dealer;
use App\Entity\State;
use App\Entity\Model;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class DealerController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/dealer/add/', name: 'mbs_dealer_add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/logo/dealer')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $country        = $entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);
        $state          = $entityManager->getRepository(State::class)->findBy([], ['name' => 'ASC']);

        $dealer = new Dealer();

        $form = $this->createFormBuilder($dealer)
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
                $extension = $imageFile->guessExtension();
                $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;
                try {
                    $imageFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $translator->trans('upload.failed', ['message' => $e->getMessage()])
                    );
                    return $this->redirectToRoute('mbs_dealer', ['id' => $dealer->getId()]);
                    // ... handle exception if something happens during file upload
                }
                $dealer->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'logo/dealer/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
                if($extension=='svg') {
                    $dealer->setVector(1);
                }
                $dealer->setLogo(1);
            } elseif(isset($currentImage) && $currentImage!="") {
                $dealer->setImage($currentImage);
            }
            $dealer->setLogo(0);
            $dealer->setVector(0);
            $entityManager->persist($dealer);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('dealer.saved', ['name' => $dealer->getName()])
            );
            return $this->redirectToRoute('mbs_dealer', ['id' => $dealer->getId()]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('dealer/dealer.html.twig', [
            "databases" => $databases,
            "dealerform" => $form->createView(),
            "dealer" => $dealer,
        ]);
    }

    #[Route('/dealer/{id}', name: 'mbs_dealer', methods: ['GET', 'POST'])]
    public function dealer(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/logo/dealer')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $dealer = $entityManager->getRepository(Dealer::class)->findOneBy(["id" => $id]);
        if($dealer->getImage()!="") {
            $currentImage = $dealer->getImage();
        }

        $country        = $entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);
        $state          = $entityManager->getRepository(State::class)->findBy([], ['name' => 'ASC']);

        $form = $this->createFormBuilder($dealer)
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
                $extension = $imageFile->guessExtension();
                $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;
                try {
                    $imageFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $translator->trans('upload.failed', ['message' => $e->getMessage()])
                    );
                    return $this->redirectToRoute('mbs_dealer', ['id' => $dealer->getId()]);
                    // ... handle exception if something happens during file upload
                }
                if(isset($currentImage) && file_exists($imageDirectory."/".$currentImage)) {
                    unlink($imageDirectory."/".$currentImage);
                }
                $dealer->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'logo/dealer/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
                if($extension=='svg') {
                    $dealer->setVector(1);
                }
                $dealer->setLogo(1);
            } elseif(isset($currentImage) && $currentImage!="") {
                $dealer->setImage($currentImage);
            }
            $entityManager->persist($dealer);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('dealer.saved', ['name' => $dealer->getName()])
            );
            return $this->redirectToRoute('mbs_dealer', ['id' => $dealer->getId()]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('dealer/dealer.html.twig', [
            "databases" => $databases,
            "dealerform" => $form->createView(),
            "dealer" => $dealer,
        ]);
    }

    #[Route('/dealer/{id}/models', name: 'mbs_dealer_models', methods: ['GET', 'POST'])]
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

        $dealer = $entityManager->getRepository(Dealer::class)->find($id);
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $qb = $entityManager->createQueryBuilder();
        $models = $qb->select('m')->from(Model::class, 'm')
            ->leftJoin('m.category','c')
            ->leftJoin('m.subcategory','sub')
            ->leftJoin('m.status','status')
            ->leftJoin('m.storage','s')
            ->leftJoin('m.manufacturer','manu')
            ->where('m.modeldatabase in (:databases)')
            ->andWhere('m.dealer = :dealer')
            ->setParameters(new ArrayCollection([new Parameter('databases',  $databases), new Parameter('dealer', $dealer)]))
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

    #[Route('/dealer/', name: 'mbs_dealer_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $limit = $request->query->get('limit');
        $sortcolumn = $request->query->get('sortcolumn');
        $sortorder = $request->query->get('sortorder');
        $limits = $this->getParameter('limits');
        $sortcolumns = $this->getParameter('dealer.sortcolumns');
        if($sortcolumn!="" && in_array($sortcolumn, $sortcolumns)) {
            $request->getSession()->set('sortcolumn', $sortcolumn);
        } else {
            $request->getSession()->set('sortcolumn', $this->getParameter('dealer.sortcolumn'));
        }
        if($sortorder!="" && in_array($sortorder, ['asc', 'desc'])) {
            $request->getSession()->set('sortorder', $sortorder);
        } else {
            $request->getSession()->set('sortorder', $this->getParameter('dealer.sortorder'));
        }
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }

        $dealers = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, $user->getId()]), [$request->getSession()->get('sortcolumn') => $request->getSession()->get('sortorder')]);

        $pagination = $paginator->paginate(
            $dealers,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('dealer/list.html.twig', [
            "databases" => $databases,
            "dealers" => $pagination
        ]);
    }

    #[Route('/dealer/delete/{id}', name: 'mbs_dealer_delete', methods: ['GET'])]
    public function delete(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $dealer = $entityManager->getRepository(Dealer::class)->findOneBy(["id" => $id]);
        if(count($dealer->getModels())>0) {
            $this->addFlash(
                'error',
                $translator->trans('dealer.has-models', ['count' => count($dealer->getModels()), 'name' => $dealer->getName()])
            );
            $entityManager->flush();
            return $this->redirectToRoute('mbs_dealer', ['id' => $dealer->getId()]);
        }
        if(!$dealer) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        $entityManager->remove($dealer);
        $this->addFlash(
            'success',
            $translator->trans('dealer.deleted', ['name' => $dealer->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_dealer_list');

    }

}

