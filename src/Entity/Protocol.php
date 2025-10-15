<?php

namespace App\Entity;

use App\Repository\ProtocolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProtocolRepository::class)]
#[ORM\Table(name: 'mbs_protocol')]
class Protocol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'protocols')]
    private ?User $user = null;

    /**
     * @var Collection<int, Digital>
     */
    #[ORM\OneToMany(targetEntity: Digital::class, mappedBy: 'protocol')]
    private Collection $digitals;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

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
            $digital->setProtocol($this);
        }

        return $this;
    }

    public function removeDigital(Digital $digital): static
    {
        if ($this->digitals->removeElement($digital)) {
            // set the owning side to null (unless already changed)
            if ($digital->getProtocol() === $this) {
                $digital->setProtocol(null);
            }
        }

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }
}
