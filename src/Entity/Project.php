<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'mbs_project')]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $color = null;

    /**
     * @var Collection<int, Model>
     */
    #[ORM\OneToMany(targetEntity: Model::class, mappedBy: 'project')]
    private Collection $models;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?User $user = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    public function __construct()
    {
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

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
            $model->setProject($this);
        }

        return $this;
    }

    public function removeModel(Model $model): static
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getProject() === $this) {
                $model->setProject(null);
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

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }
}
