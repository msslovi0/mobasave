<?php

namespace App\Entity;

use App\Repository\PowerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PowerRepository::class)]
#[ORM\Table(name: 'mbs_power')]
class Power
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Waggon>
     */
    #[ORM\OneToMany(targetEntity: Waggon::class, mappedBy: 'power')]
    private Collection $waggons;

    /**
     * @var Collection<int, Tram>
     */
    #[ORM\OneToMany(targetEntity: Tram::class, mappedBy: 'power')]
    private Collection $trams;

    /**
     * @var Collection<int, Car>
     */
    #[ORM\OneToMany(targetEntity: Car::class, mappedBy: 'power')]
    private Collection $cars;

    public function __construct()
    {
        $this->waggons = new ArrayCollection();
        $this->trams = new ArrayCollection();
        $this->cars = new ArrayCollection();
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

    /**
     * @return Collection<int, Waggon>
     */
    public function getWaggons(): Collection
    {
        return $this->waggons;
    }

    public function addWaggon(Waggon $waggon): static
    {
        if (!$this->waggons->contains($waggon)) {
            $this->waggons->add($waggon);
            $waggon->setPower($this);
        }

        return $this;
    }

    public function removeWaggon(Waggon $waggon): static
    {
        if ($this->waggons->removeElement($waggon)) {
            // set the owning side to null (unless already changed)
            if ($waggon->getPower() === $this) {
                $waggon->setPower(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tram>
     */
    public function getTrams(): Collection
    {
        return $this->trams;
    }

    public function addTram(Tram $tram): static
    {
        if (!$this->trams->contains($tram)) {
            $this->trams->add($tram);
            $tram->setPower($this);
        }

        return $this;
    }

    public function removeTram(Tram $tram): static
    {
        if ($this->trams->removeElement($tram)) {
            // set the owning side to null (unless already changed)
            if ($tram->getPower() === $this) {
                $tram->setPower(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Car>
     */
    public function getCars(): Collection
    {
        return $this->cars;
    }

    public function addCar(Car $car): static
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
            $car->setPower($this);
        }

        return $this;
    }

    public function removeCar(Car $car): static
    {
        if ($this->cars->removeElement($car)) {
            // set the owning side to null (unless already changed)
            if ($car->getPower() === $this) {
                $car->setPower(null);
            }
        }

        return $this;
    }
}
