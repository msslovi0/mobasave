<?php

namespace App\Entity;

use App\Repository\DecoderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DecoderRepository::class)]
#[ORM\Table(name: 'mbs_decoder')]
class Decoder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'decoders')]
    private ?User $user = null;

    /**
     * @var Collection<int, Digital>
     */
    #[ORM\OneToMany(targetEntity: Digital::class, mappedBy: 'decoder')]
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
            $digital->setDecoder($this);
        }

        return $this;
    }

    public function removeDigital(Digital $digital): static
    {
        if ($this->digitals->removeElement($digital)) {
            // set the owning side to null (unless already changed)
            if ($digital->getDecoder() === $this) {
                $digital->setDecoder(null);
            }
        }

        return $this;
    }
}
