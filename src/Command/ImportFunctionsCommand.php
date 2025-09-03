<?php
namespace App\Command;

use App\Entity\Database;
use App\Entity\DigitalFunction;
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
use App\Entity\Decoderfunction;
use App\Entity\Functionkey;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import:functions')]
class ImportFunctionsCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Funktionen.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $digital = $this->entityManager->getRepository(Digital::Class)->findOneBy(["import" => $data[4]]);
            $locomotive = $this->entityManager->getRepository(Locomotive::Class)->findOneBy(["dimport" => $digital->getImport()]);
            $model = $this->entityManager->getRepository(Model::Class)->findOneBy(["locomotive" => $locomotive]);
            $functionkey = $this->entityManager->getRepository(Functionkey::Class)->findOneBy(["name" => $data[1]]);
            $decoderfunction = $this->entityManager->getRepository(Decoderfunction::Class)->findOneBy(["name" => $data[3]]);
            if(!is_object($decoderfunction)) {
                $output->writeln('<fg=green>+ New Decoder Function</> ('.$data[3].')');
                $decoderfunction = new Decoderfunction();
                $decoderfunction->setName($data[3]);
                $decoderfunction->setLight(0);
                $decoderfunction->setSound(0);
                $this->entityManager->persist($decoderfunction);
                $this->entityManager->flush();
            }
            if(!is_object($functionkey)) {
                $output->writeln('<fg=green>+ New Function Key</> ('.$data[1].')');
                $functionkey = new Functionkey();
                $functionkey->setName($data[1]);
                $this->entityManager->persist($functionkey);
                $this->entityManager->flush();
            }

            $digitalfunction = $this->entityManager->getRepository(Digitalfunction::Class)->findOneBy(["digital" => $digital, "functionkey" => $functionkey, "decoderfunction" => $decoderfunction]);

            if(!is_object($digitalfunction)) {
                $output->writeln('<fg=green>+ New Digital Function</> ('.$model->getName().', '.$functionkey->getName().', '.$decoderfunction->getName().'))');
                $digitalfunction = new DigitalFunction();
            } else {
                $output->writeln('<fg=yellow>◇ Existing Digital Function</> ('.$model->getName().', '.$functionkey->getName().', '.$decoderfunction->getName().')');
            }
            $digitalfunction->setDigital($digital);
            $digitalfunction->setFunctionkey($functionkey);
            $digitalfunction->setDecoderfunction($decoderfunction);
            $this->entityManager->persist($digitalfunction);
            $this->entityManager->flush();
//            die();

        }
        return Command::SUCCESS;
    }
}
