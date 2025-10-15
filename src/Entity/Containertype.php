<?php

namespace App\Entity;

use App\Repository\ContainertypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ContainertypeRepository::class)]
#[ORM\Table(name: 'mbs_containertype')]
class Containertype
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?float $length = null;

    /**
     * @var Collection<int, Container>
     */
    #[ORM\OneToMany(targetEntity: Container::class, mappedBy: 'containertype')]
    private Collection $containers;

    #[ORM\ManyToOne(inversedBy: 'containertypes')]
    private ?User $user = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    public function __construct()
    {
        $this->containers = new ArrayCollection();
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

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): static
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return Collection<int, Container>
     */
    public function getContainers(): Collection
    {
        return $this->containers;
    }

    public function addContainer(Container $container): static
    {
        if (!$this->containers->contains($container)) {
            $this->containers->add($container);
            $container->setContainertype($this);
        }

        return $this;
    }

    public function removeContainer(Container $container): static
    {
        if ($this->containers->removeElement($container)) {
            // set the owning side to null (unless already changed)
            if ($container->getContainertype() === $this) {
                $container->setContainertype(null);
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
