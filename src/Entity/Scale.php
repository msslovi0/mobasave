<?php

namespace App\Entity;

use App\Repository\ScaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScaleRepository::class)]
#[ORM\Table(name: 'mbs_scale')]
class Scale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $ratio = null;

    /**
     * @var Collection<int, ScaleTrack>
     */
    #[ORM\OneToMany(targetEntity: ScaleTrack::class, mappedBy: 'scale')]
    private Collection $scaleTracks;

    /**
     * @var Collection<int, Model>
     */
    #[ORM\OneToMany(targetEntity: Model::class, mappedBy: 'scale')]
    private Collection $models;

    #[ORM\ManyToOne(inversedBy: 'scales')]
    private ?User $user = null;

    public function __construct()
    {
        $this->scaleTracks = new ArrayCollection();
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

    public function getRatio(): ?string
    {
        return $this->ratio;
    }

    public function setRatio(?string $ratio): static
    {
        $this->ratio = $ratio;

        return $this;
    }

    /**
     * @return Collection<int, ScaleTrack>
     */
    public function getScaleTracks(): Collection
    {
        return $this->scaleTracks;
    }

    public function addScaleTrack(ScaleTrack $scaleTrack): static
    {
        if (!$this->scaleTracks->contains($scaleTrack)) {
            $this->scaleTracks->add($scaleTrack);
            $scaleTrack->setScale($this);
        }

        return $this;
    }

    public function removeScaleTrack(ScaleTrack $scaleTrack): static
    {
        if ($this->scaleTracks->removeElement($scaleTrack)) {
            // set the owning side to null (unless already changed)
            if ($scaleTrack->getScale() === $this) {
                $scaleTrack->setScale(null);
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
            $model->setScale($this);
        }

        return $this;
    }

    public function removeModel(Model $model): static
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getScale() === $this) {
                $model->setScale(null);
            }
        }

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
}
