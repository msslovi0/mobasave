<?php

namespace App\Entity;

use App\Repository\ModelloadRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModelloadRepository::class)]
#[ORM\Table(name: 'mbs_modelload')]
class Modelload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'modelloads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Model $model = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Model $loaditem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function setModel(?Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getLoaditem(): ?Model
    {
        return $this->loaditem;
    }

    public function setLoaditem(?Model $loaditem): static
    {
        $this->loaditem = $loaditem;

        return $this;
    }
}
