<?php

namespace App\Entity;

use App\Repository\WaggonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WaggonRepository::class)]
#[ORM\Table(name: 'mbs_waggon')]
class Waggon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $class = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registration = null;

    #[ORM\ManyToOne(inversedBy: 'waggons')]
    private ?Power $power = null;

    #[ORM\ManyToOne(inversedBy: 'waggons')]
    private ?Coupler $coupler = null;

    #[ORM\Column(nullable: true)]
    private ?float $length = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): static
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

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): static
    {
        $this->length = $length;

        return $this;
    }
}
