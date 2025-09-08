<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Database;
use App\Entity\Manufacturer;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManufacturerController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/manufacturer/{id}', name: 'mbs_manufacturer', methods: ['GET', 'POST'])]
    public function manufacturer(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/image')] string $imageDirectory): Response
    {


    }

    #[Route('/manufacturer/', name: 'mbs_manufacturer_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $manufacturers = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]));

        $pagination = $paginator->paginate(
            $manufacturers,
            $request->query->getInt('page', 1), /* page number */
            100 /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('manufacturer/list.html.twig', [
            "databases" => $databases,
            "manufacturers" => $pagination
        ]);
    }
}
