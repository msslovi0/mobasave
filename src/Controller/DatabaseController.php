<?php
// src/Controller/LuckyController.php
namespace App\Controller;

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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

        $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $database]);

        $pagination = $paginator->paginate(
            $models,
            $request->query->getInt('page', 1), /* page number */
            100 /* limit per page */
        );

        return $this->render('collection/list.html.twig', [
            "models" => $pagination
        ]);
    }
    #[Route('/model/add/', name: 'mbs_model_add', methods: ['GET','POST'])]
    public function update(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
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

        $status         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, 1]));
        $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, 1]));
        $subcategory    = $entityManager->getRepository(Subcategory::class)->findBy(array("user" => [null, 1]));
        $manufacturer   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, 1]));
        $company        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, 1]));
        $scale          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, 1]));
        $track          = $entityManager->getRepository(ScaleTrack::class)->findBy(array("user" => [null, 1]));
        $epoch          = $entityManager->getRepository(Epoch::class)->findBy(array("user" => [null, 1]));
        $subepoch       = $entityManager->getRepository(Subepoch::class)->findBy(array("user" => [null, 1]));
        $storage        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, 1]));
        $project        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, 1]));
        $dealer         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, 1]));
        $box            = $entityManager->getRepository(Box::class)->findBy(array("user" => [null, 1]));
        $condition      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, 1]));
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
            ->add('save', SubmitType::class, ['label' => 'Save']);

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
        $status         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, 1]));
        $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, 1]));
        $subcategory    = $entityManager->getRepository(Subcategory::class)->findBy(array("user" => [null, 1]));
        $manufacturer   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, 1]));
        $company        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, 1]));
        $scale          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, 1]));
        $track          = $entityManager->getRepository(ScaleTrack::class)->findBy(array("user" => [null, 1]));
        $epoch          = $entityManager->getRepository(Epoch::class)->findBy(array("user" => [null, 1]));
        $subepoch       = $entityManager->getRepository(Subepoch::class)->findBy(array("user" => [null, 1]));
        $storage        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, 1]));
        $project        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, 1]));
        $dealer         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, 1]));
        $box            = $entityManager->getRepository(Box::class)->findBy(array("user" => [null, 1]));
        $condition      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, 1]));
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
            ->add('save', SubmitType::class, ['label' => 'Save']);

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

}
