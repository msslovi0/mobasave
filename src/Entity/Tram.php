<?php

namespace App\Entity;

use App\Repository\TramRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TramRepository::class)]
#[ORM\Table(name: 'mbs_tram')]
class Tram
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nickname = null;

    #[ORM\ManyToOne(inversedBy: 'trams')]
    private ?Maker $maker = null;

    #[ORM\ManyToOne(inversedBy: 'trams')]
    private ?Axle $axle = null;

    #[ORM\ManyToOne(inversedBy: 'trams')]
    private ?Power $power = null;

    #[ORM\ManyToOne(inversedBy: 'trams')]
    private ?Coupler $coupler = null;

    /**
     * @var Collection<int, Model>
     */
    #[ORM\OneToMany(targetEntity: Model::class, mappedBy: 'tram')]
    private Collection $models;

    #[ORM\Column(length: 10)]
    private ?string $import = null;

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

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;

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
            $model->setTram($this);
        }

        return $this;
    }

    public function removeModel(Model $model): static
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getTram() === $this) {
                $model->setTram(null);
            }
        }

        return $this;
    }

    public function getImport(): ?string
    {
        return $this->import;
    }

    public function setImport(string $import): static
    {
        $this->import = $import;

        return $this;
    }
}
