<?php

namespace App\Entity;

use App\Repository\AxleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AxleRepository::class)]
#[ORM\Table(name: 'mbs_axle')]
class Axle
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
    #[ORM\OneToMany(targetEntity: Tram::class, mappedBy: 'axle')]
    private Collection $trams;

    /**
     * @var Collection<int, Locomotive>
     */
    #[ORM\OneToMany(targetEntity: Locomotive::class, mappedBy: 'axle')]
    private Collection $locomotives;

    public function __construct()
    {
        $this->trams = new ArrayCollection();
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
            $tram->setAxle($this);
        }

        return $this;
    }

    public function removeTram(Tram $tram): static
    {
        if ($this->trams->removeElement($tram)) {
            // set the owning side to null (unless already changed)
            if ($tram->getAxle() === $this) {
                $tram->setAxle(null);
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
            $locomotive->setAxle($this);
        }

        return $this;
    }

    public function removeLocomotive(Locomotive $locomotive): static
    {
        if ($this->locomotives->removeElement($locomotive)) {
            // set the owning side to null (unless already changed)
            if ($locomotive->getAxle() === $this) {
                $locomotive->setAxle(null);
            }
        }

        return $this;
    }
}
