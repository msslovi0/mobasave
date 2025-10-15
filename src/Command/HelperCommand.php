<?php
namespace App\Command;

use App\Entity\Box;
use App\Entity\Coupler;
use App\Entity\Decoder;
use App\Entity\Edition;
use App\Entity\Pininterface;
use App\Entity\Power;
use App\Entity\Protocol;
use App\Entity\Project;
use App\Entity\Status;
use App\Entity\Condition;
use App\Entity\Modelset;
use App\Entity\Containertype;
use App\Entity\Category;
use App\Entity\Subcategory;
use App\Entity\Scale;
use App\Entity\ScaleTrack;
use App\Entity\Epoch;
use App\Entity\Maker;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use App\Entity\Car;
use App\Entity\Manufacturer;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:helper')]
class HelperCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {

        $models = $this->entityManager->getRepository(Maker::Class)->findAll();
        foreach($models as $model) {
            $uuid = Uuid::v4();
            $model->setUUID($uuid);
            $this->entityManager->persist($model);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
