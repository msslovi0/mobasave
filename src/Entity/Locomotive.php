<?php

namespace App\Entity;

use App\Repository\LocomotiveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocomotiveRepository::class)]
#[ORM\Table(name: 'mbs_locomotive')]
class Locomotive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $class = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registration = null;

    #[ORM\Column(nullable: true)]
    private ?float $length = null;

    #[ORM\Column(type: Types::BINARY)]
    private $digital = null;

    #[ORM\Column(type: Types::BINARY)]
    private $sound = null;

    #[ORM\Column(type: Types::BINARY)]
    private $smoke = null;

    #[ORM\Column(type: Types::BINARY)]
    private $dccready = null;

    #[ORM\ManyToOne(inversedBy: 'locomotives')]
    private ?Maker $maker = null;

    #[ORM\ManyToOne(inversedBy: 'locomotives')]
    private ?Axle $axle = null;

    #[ORM\ManyToOne(inversedBy: 'locomotives')]
    private ?Power $power = null;

    #[ORM\ManyToOne(inversedBy: 'locomotives')]
    private ?Coupler $coupler = null;

    /**
     * @var Collection<int, Model>
     */
    #[ORM\OneToMany(targetEntity: Model::class, mappedBy: 'locomotive')]
    private Collection $models;

    public function __construct()
    {
        $this->models = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    public function setRegistration(?string $registration): static
    {
        $this->registration = $registration;

        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getDigital()
    {
        return $this->digital;
    }

    public function setDigital($digital): static
    {
        $this->digital = $digital;

        return $this;
    }

    public function getSound()
    {
        return $this->sound;
    }

    public function setSound($sound): static
    {
        $this->sound = $sound;

        return $this;
    }

    public function getSmoke()
    {
        return $this->smoke;
    }

    public function setSmoke($smoke): static
    {
        $this->smoke = $smoke;

        return $this;
    }

    public function getDccready()
    {
        return $this->dccready;
    }

    public function setDccready($dccready): static
    {
        $this->dccready = $dccready;

        return $this;
    }

    public function getMaker(): ?Maker
    {
        return $this->maker;
    }

    public function setMaker(?Maker $maker): static
    {
        $this->maker = $maker;

        return $this;
    }

    public function getAxle(): ?Axle
    {
        return $this->axle;
    }

    public function setAxle(?Axle $axle): static
    {
        $this->axle = $axle;

        return $this;
    }

    public function getPower(): ?Power
    {
        return $this->power;
    }

    public function setPower(?Power $power): static
    {
        $this->power = $power;

        return $this;
    }

    public function getCoupler(): ?Coupler
    {
        return $this->coupler;
    }

    public function setCoupler(?Coupler $coupler): static
    {
        $this->coupler = $coupler;

        return $this;
    }

    /**
     * @return Collection<int, Model>
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(Model $model): static
    {
        if (!$this->models->contains($model)) {
            $this->models->add($model);
            $model->setLocomotive($this);
        }

        return $this;
    }

    public function removeModel(Model $model): static
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getLocomotive() === $this) {
                $model->setLocomotive(null);
            }
        }

        return $this;
    }
}
