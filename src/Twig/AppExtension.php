<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Database;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Routing\RouterInterface;
use Twig\Attribute\AsTwigFunction;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
class AppExtension
{
    public function __construct(private EntityManagerInterface $entityManager, private RouterInterface $router) {
        $this->entityManager = $entityManager;
        $this->router = $router;
    }
    #[AsTwigFunction('databasevalue')]
    public function getDatabaseValue(string $database, mixed $id): string
    {
        if(strlen($id)==32) {
            $field = "uuid";
            $value = hex2bin($id);
        } else {
            $field = "id";
            $value = $id;
        }

        $record = $this->entityManager->getRepository('App\\Entity\\'.$database)->findOneBy([$field => $value]);
        return $record->getName();
    }

    #[AsTwigFunction('uuid')]
    public function getUuid(string $uuid): string
    {
        return str_replace("0x","", $uuid);
    }

    #[AsTwigFunction('route_exists')]
    public function routeExists(string $route): bool
    {
        try {
            $url = $this->router->generate($route);
            return true;
        } catch (RouteNotFoundException $e) {
            return false;
        }

    }
}
