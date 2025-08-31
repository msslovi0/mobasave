<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Database;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class DatabaseController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }
    #[Route('/collection/{id}', name: 'mbs_database', methods: ['GET'])]
    public function index(int $id, EntityManagerInterface $entityManager): Response
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
        return $this->render('collection/list.html.twig', [
            "database" => $database
        ]);
    }
}
