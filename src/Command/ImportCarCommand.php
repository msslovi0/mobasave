<?php
namespace App\Command;

use App\Entity\Database;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Car;
use App\Entity\Power;
use App\Entity\Coupler;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import:car')]
class ImportCarCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Waggon.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $car = $this->entityManager->getRepository(Car::Class)->findOneBy(["import" => $data[0]]);
            if(!is_object($car)) {
                $output->writeln('<fg=green>+ New Car</> ('.$data[2].')');
                $car = new car();
            } else {
                $output->writeln('<fg=yellow>◇ Existing Car</> ('.$data[2].')');
            }
            $power = $this->entityManager->getRepository(Power::Class)->findOneBy(["name" => $data[4]]);
            if($data[5]!="") {
                $coupler = $this->entityManager->getRepository(Coupler::Class)->findOneBy(["name" => $data[5]]);
                $car->setCoupler($coupler);
            }
            $car->setImport($data[0]);
            $car->setPower($power);
            $car->setCoupler($coupler);
            $car->setClass($data[1]);
            $car->setRegistration($data[2]);
            if($data[3]!="") {
                $car->setLength($data[3]);
            }

            $this->entityManager->persist($car);
            $this->entityManager->flush();
            // die();

        }
        return Command::SUCCESS;
    }
}
