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
use App\Entity\Project;
use App\Entity\Country;
use App\Entity\Locomotive;
use App\Entity\Car;
use App\Entity\Vehicle;
use App\Entity\Tram;
use App\Entity\Container;
use App\Entity\Edition;
use App\Entity\Box;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'mbs:import')]
class ImportCommand {
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function __invoke(OutputInterface $output): int {
        $file = fopen("/Users/matthias/Desktop/Modelle_Uli.csv", "r");
        while(!feof($file)) {
            $data = fgetcsv($file,null, ";");
            if(!isset($data[0]) || !is_numeric($data[0])) {
                continue;
            }
            $database = $this->entityManager->getRepository(Database::class)->findOneBy(["id" => 2]);
            $model = $this->entityManager->getRepository(Model::Class)->findOneBy(["import" => $data[0], "modeldatabase" => $database]);
            $dealer = $this->entityManager->getRepository(Dealer::Class)->findOneBy(["name" => $data[5]]);
            $category = $this->entityManager->getRepository(Category::Class)->findOneBy(["name" => $data[3]]);
            $subcategory = $this->entityManager->getRepository(Subcategory::Class)->findOneBy(["category" => $category, "name" => $data[4]]);
            $manufacturer = $this->entityManager->getRepository(Manufacturer::Class)->findOneBy(["name" => $data[18]]);
            if(!is_object($manufacturer) && $data[18]!="") {
                $manufacturer = new Manufacturer;
                $manufacturer->setLogo(0);
                $manufacturer->setVector(0);
                $manufacturer->setName($data[18]);
                $this->entityManager->persist($manufacturer);
            }
            $scale = $this->entityManager->getRepository(Scale::Class)->findOneBy(["name" => $data[11]]);
            if($data[32]!="") {
                $project = $this->entityManager->getRepository(Project::Class)->findOneBy(["name" => $data[32]]);
                if(!is_object($project)) {
                    $project = new Project;
                    $project->setName($data[32]);
                    $this->entityManager->persist($project);
                }
            }
            if($data[14]!="") {
                $edition = $this->entityManager->getRepository(Edition::Class)->findOneBy(["name" => $data[14]]);
                if(!is_object($edition)) {
                    $edition = new Edition;
                    $edition->setName($data[14]);
                    $this->entityManager->persist($edition);
                }
            }
            $country = $this->entityManager->getRepository(Country::Class)->findOneBy(["name" => $data[24]]);
            if(!is_object($country) && $data[24]!="") {
                $country = new Country;
                $country->setName($data[24]);
                $this->entityManager->persist($country);
            }
            if($data[17]!="") {
                $company = $this->entityManager->getRepository(Company::Class)->findOneBy(["name" => $data[17]]);
                if(!is_object($company)) {
                    $company = new Company;
                    $company->setLogo(0);
                    $company->setVector(0);
                    $company->setName($data[17]);
                    $this->entityManager->persist($company);
                }
            }
            if(!is_object($model)) {
                $output->writeln('<fg=green>+ New model</> ('.$data[2].')');
                $model = new Model();
                if($data[25]!="") {
                    $model->setCreated(new \DateTime($data[25]));
                } else {
                    $model->setCreated(new \DateTime());
                }
            } else {
                $output->writeln('<fg=yellow>◇ Existing model</> ('.$data[2].')');
            }
            $model->setImport($data[0]);
            $model->setImage(basename($data[1]));
            $model->setName($data[2]);
            $model->setCategory($category);
            $model->setSubcategory($subcategory);
            if(isset($dealer)) {
                $model->setDealer($dealer);
            }
            $model->setPower($data[6]);
            if($data[7]=="Ja") {
                $model->setBox($this->entityManager->getRepository(Box::Class)->findOneBy(["id" => 1]));
            }
            if($data[8]=="Ja") {
                $model->setInstructions(1);
            }else {
                $model->setInstructions(0);
            }
            if($data[9]=="Ja") {
                $model->setParts(1);
            }else {
                $model->setParts(0);
            }
            if($data[10]=="Ja") {
                $model->setDisplaycase(1);
            }else {
                $model->setDisplaycase(0);
            }
            if($data[11]=="Ja") {
                $model->setEnhanced(1);
            }else {
                $model->setEnhanced(0);
            }
            $model->setWeathered(0);
            if($data[12]=="Ja") {
                $model->setAvailable(1);
            }else {
                $model->setAvailable(0);
            }
            $model->setQuantity($data[13]=="" ? 1 : $data[13]);
            if(isset($edition)) {
                $model->setEdition($edition);
            }
            if($data[15]!="") {
                $model->setGtin13($data[15]);
            }
            $model->setModel($data[16]);
            if(isset($company)) {
                $model->setCompany($company);
            }
            if(isset($manufacturer)) {
                $model->setManufacturer($manufacturer);
            }
            $model->setScale($scale);
            if($data[20]!="") {
                $model->setMSRP($data[20]);
            }
            if($data[21]!="") {
                $model->setPrice($data[21]);
            }
            if($data[22]!="") {
                $model->setListprice($data[22]);
            }
            if($data[25]!="" && $data[25]!="2010-01-01") {
                $model->setPurchased(new \DateTime($data[25]));
            }
            if($data[26]!="") {
                $model->setColor1($data[26]);
            }
            if($data[27]!="") {
                $model->setColor2($data[27]);
            }
            if($data[28]!="") {
                $model->setColor3($data[28]);
            }
            if(isset($project)) {
                $model->setProject($project);
            }
            if($data[33]!="") {
                $model->setNotes($data[33]);
            }
            if($data[34]!="") {
                $model->setDescription($data[34]);
            }
            $model->setModeldatabase($database);



            $model->setUpdated(new \DateTime());
            $this->entityManager->persist($model);
            $this->entityManager->flush();
        }
        return Command::SUCCESS;
    }
}
