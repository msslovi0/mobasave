<?php
namespace App\Command;

use App\Entity\Database;
use App\Entity\Pininterface;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Locomotive;
use App\Entity\Digital;
use App\Entity\Protocol;
use App\Entity\Decoder;
use App\Entity\Model;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import:digital')]
class ImportDigitalCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Digital.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $digital = $this->entityManager->getRepository(Digital::Class)->findOneBy(["import" => $data[0]]);
            $locomotive = $this->entityManager->getRepository(Locomotive::Class)->findOneBy(["dimport" => $data[0]]);
            $model = $this->entityManager->getRepository(Model::Class)->findOneBy(["locomotive" => $locomotive]);

            if(!is_object($digital)) {
                $output->writeln('<fg=green>+ New Digital info</> ('.$model->getName().')');
                $digital = new Digital();
            } else {
                $output->writeln('<fg=yellow>◇ Existing Digital Info</> ('.$model->getName().')');
            }
            $protocol = $this->entityManager->getRepository(Protocol::Class)->findOneBy(["name" => $data[2]]);
            if($data[3]!="") {
                $interface = $this->entityManager->getRepository(Pininterface::Class)->findOneBy(["name" => $data[3]]);
                $digital->setPininterface($interface);
            }
            if($data[4]!="") {
                $decoder = $this->entityManager->getRepository(Decoder::Class)->findOneBy(["name" => $data[4]]);
                $digital->setDecoder($decoder);
            }
            $model->setDigital($digital);
            $digital->setImport($data[0]);
            if($data[1]!="") {
                $digital->setAddress((int)$data[1]);
            }
            $digital->setProtocol($protocol);

            $this->entityManager->persist($digital);
            $this->entityManager->persist($model);
            $this->entityManager->flush();
//            die();

        }
        return Command::SUCCESS;
    }
}
