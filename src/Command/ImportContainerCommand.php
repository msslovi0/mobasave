<?php
namespace App\Command;

use App\Entity\Container;
use App\Entity\Coupler;
use App\Entity\Database;
use App\Entity\Power;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Containertype;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import:container')]
class ImportContainerCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Container.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $container = $this->entityManager->getRepository(Container::Class)->findOneBy(["import" => $data[0]]);
            if(!is_object($container)) {
                $output->writeln('<fg=green>+ New Container</> ('.$data[1].')');
                $container = new Container();
            } else {
                $output->writeln('<fg=yellow>◇ Existing Container</> ('.$data[1].')');
            }
            if($data[2]!="") {
                $type = $this->entityManager->getRepository(Containertype::Class)->findOneBy(["name" => $data[2]]);
                $container->setContainertype($type);
            }
            $container->setImport($data[0]);
            $container->setRegistration($data[1]);
            if($data[3]!="") {
                $container->setLength($data[3]);
            }

            $this->entityManager->persist($container);
            $this->entityManager->flush();
            // die();

        }
        return Command::SUCCESS;
    }
}
