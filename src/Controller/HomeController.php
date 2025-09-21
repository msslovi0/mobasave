<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Database;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class HomeController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }
    #[Route('/render/', name: 'mbs_render', methods: ['GET'])]
    public function renderDefaults(): Response
    {
        return $this->render('render.html.twig', []);
    }
    #[Route('/', name: 'mbs_home', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authChecker): Response
    {
        if (true === $authChecker->isGranted('ROLE_USER')) {
            $user = $this->security->getUser();
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('home/index.html.twig', [
                "databases" => $databases
            ]);
        } else {
            return $this->render('home/welcome.html.twig', [
            ]);
        }
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    }
}
