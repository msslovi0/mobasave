<?php

namespace App\Entity;

use App\Repository\DigitalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DigitalRepository::class)]
#[ORM\Table(name: 'mbs_digital')]
class Digital
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $interface = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $decoder = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?int
    {
        return $this->address;
    }

    public function setAddress(?int $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getInterface(): ?string
    {
        return $this->interface;
    }

    public function setInterface(?string $interface): static
    {
        $this->interface = $interface;

        return $this;
    }

    public function getDecoder(): ?string
    {
        return $this->decoder;
    }

    public function setDecoder(?string $decoder): static
    {
        $this->decoder = $decoder;

        return $this;
    }
}
