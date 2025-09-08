<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Database;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CompanyController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }
    #[Route('/company/', name: 'mbs_company_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('home/index.html.twig', [
            "databases" => $databases
        ]);
    }
}
