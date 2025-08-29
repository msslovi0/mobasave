<?php
namespace App\Command;

use App\Entity\Database;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Model;
use App\Entity\Dealer;
use App\Entity\Category;
use App\Entity\Subcategory;
use App\Entity\Company;
use App\Entity\Manufacturer;
use App\Entity\Scale;
use App\Entity\Storage;
use App\Entity\Epoch;
use App\Entity\Project;
use App\Entity\Country;
use App\Entity\Locomotive;
use App\Entity\Car;
use App\Entity\Vehicle;
use App\Entity\Tram;
use App\Entity\Container;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import')]
class ImportCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Modelle.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $database = $this->entityManager->getRepository(Database::class)->findOneBy(["id" => 1]);
            $model = $this->entityManager->getRepository(Model::Class)->findOneBy(["import" => $data[0]]);
            $dealer = $this->entityManager->getRepository(Dealer::Class)->findOneBy(["name" => $data[5]]);
            $category = $this->entityManager->getRepository(Category::Class)->findOneBy(["name" => $data[3]]);
            $subcategory = $this->entityManager->getRepository(Subcategory::Class)->findOneBy(["category" => $category, "name" => $data[4]]);
            $manufacturer = $this->entityManager->getRepository(Manufacturer::Class)->findOneBy(["name" => $data[10]]);
            $scale = $this->entityManager->getRepository(Scale::Class)->findOneBy(["name" => $data[11]]);
            if($data[14]!="") {
                $storage = $this->entityManager->getRepository(Storage::Class)->findOneBy(["name" => $data[14]]);
            }
            $epoch = $this->entityManager->getRepository(Epoch::Class)->findOneBy(["name" => $data[15]]);
            if($data[16]!="") {
                $project = $this->entityManager->getRepository(Project::Class)->findOneBy(["name" => $data[16]]);
            }
            $country = $this->entityManager->getRepository(Country::Class)->findOneBy(["name" => $data[19]]);
            if($data[9]!="") {
                $company = $this->entityManager->getRepository(Company::Class)->findOneBy(["name" => $data[9]]);
            }
            if(!is_object($model)) {
                $output->writeln('<fg=green>+ New model</> ('.$data[2].')');
                $model = new Model();
                $model->setCreated(new \DateTime($data[27]));
            } else {
                $output->writeln('<fg=yellow>◇ Existing model</> ('.$data[2].')');
            }
            if($data[29]!="") {
                $locomotive = $this->entityManager->getRepository(Locomotive::Class)->findOneBy(["import" => $data[29]]);
                $model->setLocomotive($locomotive);
            }
            if($data[30]!="") {
                $container = $this->entityManager->getRepository(Container::Class)->findOneBy(["import" => $data[30]]);
                $model->setContainer($container);
            }
            if($data[31]!="") {
                $car = $this->entityManager->getRepository(Car::Class)->findOneBy(["import" => $data[31]]);
                $model->setCar($car);
            }
            if($data[32]!="") {
                $vehicle = $this->entityManager->getRepository(Vehicle::Class)->findOneBy(["import" => $data[32]]);
                $model->setVehicle($vehicle);
            }
            if($data[33]!="") {
                $tram = $this->entityManager->getRepository(Tram::Class)->findOneBy(["import" => $data[33]]);
                $model->setTram($tram);
            }
            $model->setImage(basename($data[1]));
            $model->setName($data[2]);
            $model->setImport($data[0]);
            $model->setQuantity($data[6]=="" ? 1 : $data[6]);
            $model->setCategory($category);
            $model->setSubcategory($subcategory);
            $model->setManufacturer($manufacturer);
            if(isset($company)) {
                $model->setCompany($company);
            }
            $model->setScale($scale);
            $model->setEpoch($epoch);
            if(isset($project)) {
                $model->setProject($project);
            }
            if(isset($storage)) {
                $model->setStorage($storage);
            }
            $model->setDealer($dealer);
            $model->setModel($data[8]);
            if($data[7]!="") {
                $model->setGtin13($data[7]);
            }
            if($data[21]!="") {
                $model->setColor1($data[21]);
            }
            if($data[22]!="") {
                $model->setColor2($data[22]);
            }
            if($data[23]!="") {
                $model->setColor3($data[23]);
            }
            if($data[20]!="2010-01-01") {
                $model->setPurchased(new \DateTime($data[20]));
            }
            if($data[12]!="") {
                $model->setPrice($data[12]);
            }
            if($data[13]!="") {
                $model->setMsrp($data[13]);
            }
            if($data[24]!="") {
                $model->setNotes($data[24]);
            }
            $model->setCountry($country);
            $model->setModeldatabase($database);



            $model->setUpdated(new \DateTime());
            $this->entityManager->persist($model);
            $this->entityManager->flush();
        }
        return Command::SUCCESS;
    }
}
