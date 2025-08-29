<?php

namespace App\Entity;

use App\Repository\DecoderfuntionRepository;
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
}
