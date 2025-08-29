<?php

namespace App\Entity;

use App\Repository\DecoderfuntionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DecoderfuntionRepository::class)]
#[ORM\Table(name: 'mbs_decoderfuntion')]
class Decoderfuntion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::BINARY)]
    private $sound;

    #[ORM\Column(type: Types::BINARY)]
    private $light;

    /**
     * @var Collection<int, DigitalFunction>
     */
    #[ORM\OneToMany(targetEntity: DigitalFunction::class, mappedBy: 'decoderfunction')]
    private Collection $digitalFunctions;

    public function __construct()
    {
        $this->digitalFunctions = new ArrayCollection();
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

    public function getSound()
    {
        return $this->sound;
    }

    public function setSound($sound): static
    {
        $this->sound = $sound;

        return $this;
    }

    public function getLight()
    {
        return $this->light;
    }

    public function setLight($light): static
    {
        $this->light = $light;

        return $this;
    }

    /**
     * @return Collection<int, DigitalFunction>
     */
    public function getDigitalFunctions(): Collection
    {
        return $this->digitalFunctions;
    }

    public function addDigitalFunction(DigitalFunction $digitalFunction): static
    {
        if (!$this->digitalFunctions->contains($digitalFunction)) {
            $this->digitalFunctions->add($digitalFunction);
            $digitalFunction->setDecoderfunction($this);
        }

        return $this;
    }

    public function removeDigitalFunction(DigitalFunction $digitalFunction): static
    {
        if ($this->digitalFunctions->removeElement($digitalFunction)) {
            // set the owning side to null (unless already changed)
            if ($digitalFunction->getDecoderfunction() === $this) {
                $digitalFunction->setDecoderfunction(null);
            }
        }

        return $this;
    }
}
