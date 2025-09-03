<?php

namespace App\Entity;

use App\Repository\PininterfaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PininterfaceRepository::class)]
#[ORM\Table(name: 'mbs_pininterface')]
class Pininterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'pininterfaces')]
    private ?User $user = null;

    /**
     * @var Collection<int, Digital>
     */
    #[ORM\OneToMany(targetEntity: Digital::class, mappedBy: 'pininterface')]
    private Collection $digitals;

    public function __construct()
    {
        $this->digitals = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Digital>
     */
    public function getDigitals(): Collection
    {
        return $this->digitals;
    }

    public function addDigital(Digital $digital): static
    {
        if (!$this->digitals->contains($digital)) {
            $this->digitals->add($digital);
            $digital->setPininterface($this);
        }

        return $this;
    }

    public function removeDigital(Digital $digital): static
    {
        if ($this->digitals->removeElement($digital)) {
            // set the owning side to null (unless already changed)
            if ($digital->getPininterface() === $this) {
                $digital->setPininterface(null);
            }
        }

        return $this;
    }
}
