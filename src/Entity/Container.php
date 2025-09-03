<?php

namespace App\Entity;

use App\Repository\ContainerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContainerRepository::class)]
#[ORM\Table(name: 'mbs_container')]
class Container
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registration = null;

    #[ORM\Column(nullable: true)]
    private ?float $length = null;

    #[ORM\ManyToOne(inversedBy: 'containers')]
    private ?Containertype $containertype = null;

    /**
     * @var Collection<int, Model>
     */
    #[ORM\OneToMany(targetEntity: Model::class, mappedBy: 'container')]
    private Collection $models;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $import = null;

    public function __construct()
    {
        $this->models = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    public function setRegistration(string $registration): static
    {
        $this->registration = $registration;

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

    public function getContainertype(): ?Containertype
    {
        return $this->containertype;
    }

    public function setContainertype(?Containertype $containertype): static
    {
        $this->containertype = $containertype;

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
            $model->setContainer($this);
        }

        return $this;
    }

    public function removeModel(Model $model): static
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getContainer() === $this) {
                $model->setContainer(null);
            }
        }

        return $this;
    }

    public function getImport(): ?string
    {
        return $this->import;
    }

    public function setImport(string $import): static
    {
        $this->import = $import;

        return $this;
    }
}
