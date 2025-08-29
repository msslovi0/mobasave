<?php

namespace App\Entity;

use App\Repository\ModelLoaditemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModelLoaditemRepository::class)]
#[ORM\Table(name: 'mbs_model_loaditem')]
class ModelLoaditem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'modelLoaditems')]
    private ?Model $model = null;

    #[ORM\ManyToOne(inversedBy: 'modelLoaditems')]
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
