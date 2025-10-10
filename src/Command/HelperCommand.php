<?php
namespace App\Command;

use App\Entity\Database;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use App\Entity\Car;
use App\Entity\Manufacturer;
use App\Entity\Storage;
use App\Entity\Coupler;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:helper')]
class HelperCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {

        $models = $this->entityManager->getRepository(Database::Class)->findAll();
        foreach($models as $model) {
            $uuid = Uuid::v4();
            $model->setUUID($uuid);
            $this->entityManager->persist($model);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
