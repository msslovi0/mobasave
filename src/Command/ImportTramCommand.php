<?php
namespace App\Command;

use App\Entity\Database;
use App\Entity\Maker;
use App\Entity\Vehicle;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Tram;
use App\Entity\Axle;
use App\Entity\Power;
use App\Entity\Subcategory;
use App\Entity\Company;
use App\Entity\Manufacturer;
use App\Entity\Scale;
use App\Entity\Storage;
use App\Entity\Epoch;
use App\Entity\Project;
use App\Entity\Country;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import:tram')]
class ImportTramCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Straßenbahn.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $tram = $this->entityManager->getRepository(Tram::Class)->findOneBy(["import" => $data[0]]);
            if(!is_object($tram)) {
                $output->writeln('<fg=green>+ New Tram</> ('.$data[3].')');
                $tram = new Tram();
            } else {
                $output->writeln('<fg=yellow>◇ Existing Tram</> ('.$data[3].')');
            }
            if($data[1]!="") {
                $maker = $this->entityManager->getRepository(Maker::Class)->findOneBy(["name" => $data[1]]);
                $tram->setMaker($maker);
            }
            if($data[4]!="") {
                $axle = $this->entityManager->getRepository(Axle::Class)->findOneBy(["name" => $data[4]]);
                $tram->setAxle($axle);
            }
            $power = $this->entityManager->getRepository(Power::Class)->findOneBy(["name" => $data[5]]);
            $tram->setPower($power);
            $tram->setImport($data[0]);
            $tram->setClass($data[2]);
            if($data[6]!="") {
                $tram->setLength($data[6]);
            }
            if($data[3]!="") {
                $tram->setRegistration($data[3]);
            }

            $this->entityManager->persist($tram);
            $this->entityManager->flush();
            // die();

        }
        return Command::SUCCESS;
    }
}
