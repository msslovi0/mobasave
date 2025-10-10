<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Database;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Attribute\AsTwigFunction;
use Doctrine\Persistence\ManagerRegistry;
class AppExtension
{
    public function __construct(private EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }
    #[AsTwigFunction('databasevalue')]
    public function getDatabaseValue(string $database, int $id): string
    {
        $record = $this->entityManager->getRepository('App\\Entity\\'.$database)->findOneBy(["id" => $id]);
        return $record->getName();
    }

    #[AsTwigFunction('uuid')]
    public function getUuid(string $uuid): string
    {
        return str_replace("0x","", $uuid);
    }
}
