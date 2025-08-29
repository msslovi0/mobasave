<?php

namespace App\Entity;

use App\Repository\ModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModelRepository::class)]
#[ORM\Table(name: 'mbs_model')]
class Model
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(length: 13, nullable: true)]
    private ?string $gtin13 = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $color1 = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $color2 = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $color3 = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $purchased = null;

    #[ORM\Column(nullable: true)]
    private ?float $msrp = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Subcategory $subcategory = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Manufacturer $manufacturer = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Scale $scale = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?ScaleTrack $track = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Epoch $epoch = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Subepoch $subepoch = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Storage $storage = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Dealer $dealer = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Locomotive $locomotive = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Container $container = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Car $car = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Vehicle $vehicle = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Tram $tram = null;

    /**
     * @var Collection<int, ModelLoaditem>
     */
    #[ORM\OneToMany(targetEntity: ModelLoaditem::class, mappedBy: 'model')]
    private Collection $modelLoaditems;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Digital $digital = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Database $modeldatabase = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?Country $country = null;

    public function __construct()
    {
        $this->modelLoaditems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getGtin13(): ?string
    {
        return $this->gtin13;
    }

    public function setGtin13(?string $gtin13): static
    {
        $this->gtin13 = $gtin13;

        return $this;
    }

    public function getColor1(): ?string
    {
        return $this->color1;
    }

    public function setColor1(?string $color1): static
    {
        $this->color1 = $color1;

        return $this;
    }

    public function getColor2(): ?string
    {
        return $this->color2;
    }

    public function setColor2(?string $color2): static
    {
        $this->color2 = $color2;

        return $this;
    }

    public function getColor3(): ?string
    {
        return $this->color3;
    }

    public function setColor3(?string $color3): static
    {
        $this->color3 = $color3;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPurchased(): ?\DateTime
    {
        return $this->purchased;
    }

    public function setPurchased(?\DateTime $purchased): static
    {
        $this->purchased = $purchased;

        return $this;
    }

    public function getMsrp(): ?float
    {
        return $this->msrp;
    }

    public function setMsrp(?float $msrp): static
    {
        $this->msrp = $msrp;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSubcategory(): ?Subcategory
    {
        return $this->subcategory;
    }

    public function setSubcategory(?Subcategory $subcategory): static
    {
        $this->subcategory = $subcategory;

        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getScale(): ?Scale
    {
        return $this->scale;
    }

    public function setScale(?Scale $scale): static
    {
        $this->scale = $scale;

        return $this;
    }

    public function getTrack(): ?ScaleTrack
    {
        return $this->track;
    }

    public function setTrack(?ScaleTrack $track): static
    {
        $this->track = $track;

        return $this;
    }

    public function getEpoch(): ?Epoch
    {
        return $this->epoch;
    }

    public function setEpoch(?Epoch $epoch): static
    {
        $this->epoch = $epoch;

        return $this;
    }

    public function getSubepoch(): ?Subepoch
    {
        return $this->subepoch;
    }

    public function setSubepoch(?Subepoch $subepoch): static
    {
        $this->subepoch = $subepoch;

        return $this;
    }

    public function getStorage(): ?Storage
    {
        return $this->storage;
    }

    public function setStorage(?Storage $storage): static
    {
        $this->storage = $storage;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getDealer(): ?Dealer
    {
        return $this->dealer;
    }

    public function setDealer(?Dealer $dealer): static
    {
        $this->dealer = $dealer;

        return $this;
    }

    public function getLocomotive(): ?Locomotive
    {
        return $this->locomotive;
    }

    public function setLocomotive(?Locomotive $locomotive): static
    {
        $this->locomotive = $locomotive;

        return $this;
    }

    public function getContainer(): ?Container
    {
        return $this->container;
    }

    public function setContainer(?Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): static
    {
        $this->car = $car;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getTram(): ?Tram
    {
        return $this->tram;
    }

    public function setTram(?Tram $tram): static
    {
        $this->tram = $tram;

        return $this;
    }

    /**
     * @return Collection<int, ModelLoaditem>
     */
    public function getModelLoaditems(): Collection
    {
        return $this->modelLoaditems;
    }

    public function addModelLoaditem(ModelLoaditem $modelLoaditem): static
    {
        if (!$this->modelLoaditems->contains($modelLoaditem)) {
            $this->modelLoaditems->add($modelLoaditem);
            $modelLoaditem->setModel($this);
        }

        return $this;
    }

    public function removeModelLoaditem(ModelLoaditem $modelLoaditem): static
    {
        if ($this->modelLoaditems->removeElement($modelLoaditem)) {
            // set the owning side to null (unless already changed)
            if ($modelLoaditem->getModel() === $this) {
                $modelLoaditem->setModel(null);
            }
        }

        return $this;
    }

    public function getDigital(): ?Digital
    {
        return $this->digital;
    }

    public function setDigital(?Digital $digital): static
    {
        $this->digital = $digital;

        return $this;
    }

    public function getModeldatabase(): ?Database
    {
        return $this->modeldatabase;
    }

    public function setModeldatabase(?Database $modeldatabase): static
    {
        $this->modeldatabase = $modeldatabase;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }
}
