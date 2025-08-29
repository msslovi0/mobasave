<?php

namespace App\Entity;

use App\Repository\EpochRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EpochRepository::class)]
#[ORM\Table(name: 'mbs_epoch')]
class Epoch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $start = null;

    #[ORM\Column(nullable: true)]
    private ?int $end = null;

    /**
     * @var Collection<int, Subepoch>
     */
    #[ORM\OneToMany(targetEntity: Subepoch::class, mappedBy: 'epoch')]
    private Collection $subepoches;

    /**
     * @var Collection<int, Model>
     */
    #[ORM\OneToMany(targetEntity: Model::class, mappedBy: 'epoch')]
    private Collection $models;

    public function __construct()
    {
        $this->subepoches = new ArrayCollection();
        $this->models = new ArrayCollection();
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

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function setStart(string $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(?string $end): static
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return Collection<int, Subepoch>
     */
    public function getSubepoches(): Collection
    {
        return $this->subepoches;
    }

    public function addSubepoch(Subepoch $subepoch): static
    {
        if (!$this->subepoches->contains($subepoch)) {
            $this->subepoches->add($subepoch);
            $subepoch->setEpoch($this);
        }

        return $this;
    }

    public function removeSubepoch(Subepoch $subepoch): static
    {
        if ($this->subepoches->removeElement($subepoch)) {
            // set the owning side to null (unless already changed)
            if ($subepoch->getEpoch() === $this) {
                $subepoch->setEpoch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Model>
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(Model $model): static
    {
        if (!$this->models->contains($model)) {
            $this->models->add($model);
            $model->setEpoch($this);
        }

        return $this;
    }

    public function removeModel(Model $model): static
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getEpoch() === $this) {
                $model->setEpoch(null);
            }
        }

        return $this;
    }
}
