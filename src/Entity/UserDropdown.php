<?php

namespace App\Entity;

use App\Repository\UserDropdownRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'mbs_user_dropdown')]
#[ORM\Entity(repositoryClass: UserDropdownRepository::class)]
class UserDropdown
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userDropdowns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\Column(nullable: true)]
    private ?int $defaultvalue = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getDefaultvalue(): ?int
    {
        return $this->defaultvalue;
    }

    public function setDefaultvalue(?int $defaultvalue): static
    {
        $this->defaultvalue = $defaultvalue;

        return $this;
    }
}
