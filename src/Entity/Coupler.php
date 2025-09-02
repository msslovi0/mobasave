<?php

namespace App\Entity;

use App\Repository\CouplerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouplerRepository::class)]
#[ORM\Table(name: 'mbs_coupler')]
class Coupler
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Tram>
     */
    #[ORM\OneToMany(targetEntity: Tram::class, mappedBy: 'coupler')]
    private Collection $trams;

    /**
     * @var Collection<int, Car>
     */
    #[ORM\OneToMany(targetEntity: Car::class, mappedBy: 'coupler')]
    private Collection $cars;

    /**
     * @var Collection<int, Locomotive>
     */
    #[ORM\OneToMany(targetEntity: Locomotive::class, mappedBy: 'coupler')]
    private Collection $locomotives;

    #[ORM\ManyToOne(inversedBy: 'couplers')]
    private ?User $user = null;

    public function __construct()
    {
        $this->trams = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->locomotives = new ArrayCollection();
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
            $tram->setCoupler($this);
        }

        return $this;
    }

    public function removeTram(Tram $tram): static
    {
        if ($this->trams->removeElement($tram)) {
            // set the owning side to null (unless already changed)
            if ($tram->getCoupler() === $this) {
                $tram->setCoupler(null);
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
            $car->setCoupler($this);
        }

        return $this;
    }

    public function removeCar(Car $car): static
    {
        if ($this->cars->removeElement($car)) {
            // set the owning side to null (unless already changed)
            if ($car->getCoupler() === $this) {
                $car->setCoupler(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Locomotive>
     */
    public function getLocomotives(): Collection
    {
        return $this->locomotives;
    }

    public function addLocomotive(Locomotive $locomotive): static
    {
        if (!$this->locomotives->contains($locomotive)) {
            $this->locomotives->add($locomotive);
            $locomotive->setCoupler($this);
        }

        return $this;
    }

    public function removeLocomotive(Locomotive $locomotive): static
    {
        if ($this->locomotives->removeElement($locomotive)) {
            // set the owning side to null (unless already changed)
            if ($locomotive->getCoupler() === $this) {
                $locomotive->setCoupler(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
