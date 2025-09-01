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
        if(!$database)
        {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $this->render('status/notfound.html.twig', response: $response);
        }
        if(is_object($user) and $database->getUser()->getId()!=$user->getId())
        {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $this->render('status/forbidden.html.twig', response: $response);
        }
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
    #[Route('/model/{id}', name: 'mbs_model', methods: ['GET'])]
    public function model(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $model = $entityManager->getRepository(Model::class)->findOneBy(["id" => $id]);
        if(!$model)
        {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $this->render('status/notfound.html.twig', response: $response);
        }
        if(is_object($user) and $model->getModeldatabase()->getUser()->getId()!=$user->getId())
        {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $this->render('status/forbidden.html.twig', response: $response);
        }
        $status         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, 1]));
        $category       = $entityManager->getRepository(Category::class)->findAll();
        $subcategory    = $entityManager->getRepository(Subcategory::class)->findAll();
        $manufacturer   = $entityManager->getRepository(Manufacturer::class)->findAll();
        $company        = $entityManager->getRepository(Company::class)->findAll();
        $scale          = $entityManager->getRepository(Scale::class)->findAll();
        $track          = $entityManager->getRepository(ScaleTrack::class)->findAll();
        $Epoch          = $entityManager->getRepository(Epoch::class)->findAll();
        $subepoch       = $entityManager->getRepository(Subepoch::class)->findAll();
        $storage        = $entityManager->getRepository(Storage::class)->findAll();
        $project        = $entityManager->getRepository(Project::class)->findAll();
        $dealer         = $entityManager->getRepository(Dealer::class)->findAll();
        $country        = $entityManager->getRepository(Country::class)->findAll();

        $form = $this->createFormBuilder($model)
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, ['choices' => $status, 'choice_label' => 'name'])
            ->add('category', ChoiceType::class, ['choices' => $category, 'choice_label' => 'name'])
            ->add('subcategory', ChoiceType::class, ['choices' => $subcategory, 'choice_label' => 'name'])
            ->add('manufacturer', ChoiceType::class, ['choices' => $manufacturer, 'choice_label' => 'name'])
            ->add('color1', ColorType::class)
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        return $this->render('collection/model.html.twig', [
            "model" => $form->createView()
        ]);
    }

}
