<?php
namespace App\Command;

use App\Entity\Car;
use App\Entity\Database;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Vehicle;
use App\Entity\Maker;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import:vehicle')]
class ImportVehicleCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Fahrzeuge.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $vehicle = $this->entityManager->getRepository(Vehicle::Class)->findOneBy(["import" => $data[0]]);
            if(!is_object($vehicle)) {
                $output->writeln('<fg=green>+ New Vehicle</> ('.$data[1].')');
                $vehicle = new Vehicle();
            } else {
                $output->writeln('<fg=yellow>◇ Existing Vehicle</> ('.$data[1].')');
            }
            if($data[4]!="") {
                $maker = $this->entityManager->getRepository(Maker::Class)->findOneBy(["name" => $data[4]]);
                $vehicle->setMaker($maker);
            }
            $vehicle->setImport($data[0]);
            $vehicle->setClass($data[1]);
            if($data[2]!="") {
                $vehicle->setYear($data[2]);
            }
            if($data[3]!="") {
                $vehicle->setRegistration($data[3]);
            }

            $this->entityManager->persist($vehicle);
            $this->entityManager->flush();
            // die();

        }
        return Command::SUCCESS;
    }
}
