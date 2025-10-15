<?php

namespace App\Entity;

use App\Repository\MakerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MakerRepository::class)]
#[ORM\Table(name: 'mbs_maker')]
class Maker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'makers')]
    #[ORM\JoinTable(name: 'mbs_maker_category')]
    private Collection $category;

    /**
     * @var Collection<int, Vehicle>
     */
    #[ORM\OneToMany(targetEntity: Vehicle::class, mappedBy: 'maker')]
    private Collection $vehicles;

    /**
     * @var Collection<int, Tram>
     */
    #[ORM\OneToMany(targetEntity: Tram::class, mappedBy: 'maker')]
    private Collection $trams;

    /**
     * @var Collection<int, Locomotive>
     */
    #[ORM\OneToMany(targetEntity: Locomotive::class, mappedBy: 'maker')]
    private Collection $locomotives;

    #[ORM\ManyToOne(inversedBy: 'makers')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    public function __construct()
    {
        $this->category = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        $this->trams = new ArrayCollection();
        $this->locomotives = new ArrayCollection();
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

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setMaker($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getMaker() === $this) {
                $vehicle->setMaker(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tram>
     */
    public function getTrams(): Collection
    {
        return $this->trams;
    }

    public function addTram(Tram $tram): static
    {
        if (!$this->trams->contains($tram)) {
            $this->trams->add($tram);
            $tram->setMaker($this);
        }

        return $this;
    }

    public function removeTram(Tram $tram): static
    {
        if ($this->trams->removeElement($tram)) {
            // set the owning side to null (unless already changed)
            if ($tram->getMaker() === $this) {
                $tram->setMaker(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Locomotive>
     */
    public function getLocomotives(): Collection
    {
        return $this->locomotives;
    }

    public function addLocomotive(Locomotive $locomotive): static
    {
        if (!$this->locomotives->contains($locomotive)) {
            $this->locomotives->add($locomotive);
            $locomotive->setMaker($this);
        }

        return $this;
    }

    public function removeLocomotive(Locomotive $locomotive): static
    {
        if ($this->locomotives->removeElement($locomotive)) {
            // set the owning side to null (unless already changed)
            if ($locomotive->getMaker() === $this) {
                $locomotive->setMaker(null);
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

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
