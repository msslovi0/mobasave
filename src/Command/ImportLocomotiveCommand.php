<?php
namespace App\Command;

use App\Entity\Database;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Locomotive;
use App\Entity\Axle;
use App\Entity\Power;
use App\Entity\Coupler;
use App\Entity\Maker;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import:locomotive')]
class ImportLocomotiveCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Lokomotive.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $locomotive = $this->entityManager->getRepository(Locomotive::Class)->findOneBy(["import" => $data[0]]);
            if(!is_object($locomotive)) {
                $output->writeln('<fg=green>+ New locomotive</> ('.$data[2].')');
                $locomotive = new Locomotive();
            } else {
                $output->writeln('<fg=yellow>◇ Existing locomotive</> ('.$data[2].')');
            }
            $maker = $this->entityManager->getRepository(Maker::Class)->findOneBy(["name" => $data[12]]);
            $power = $this->entityManager->getRepository(Power::Class)->findOneBy(["name" => $data[4]]);
            $axle = $this->entityManager->getRepository(Axle::Class)->findOneBy(["name" => $data[5]]);
            if($data[6]!="") {
                $coupler = $this->entityManager->getRepository(Coupler::Class)->findOneBy(["name" => $data[6]]);
                $locomotive->setCoupler($coupler);
            }
            $locomotive->setImport($data[0]);
            $locomotive->setMaker($maker);
            $locomotive->setPower($power);
            $locomotive->setAxle($axle);
            $locomotive->setClass($data[1]);
            $locomotive->setRegistration($data[2]);
            if($data[3]!="") {
                $locomotive->setLength($data[3]);
            }
            if($data[7]!="") {
                $locomotive->setNickname($data[7]);
            }
            if($data[8]=='Ja') {
                $locomotive->setDigital(1);
            } else {
                $locomotive->setDigital(0);
            }
            if($data[9]=='Ja') {
                $locomotive->setSound(1);
            } else {
                $locomotive->setSound(0);
            }
            if($data[10]=='Ja') {
                $locomotive->setSmoke(1);
            } else {
                $locomotive->setSmoke(0);
            }
            if($data[11]=='Ja') {
                $locomotive->setDccready(1);
            } else {
                $locomotive->setDccready(0);
            }

            $this->entityManager->persist($locomotive);
            $this->entityManager->flush();
            // die();

        }
        return Command::SUCCESS;
    }
}
