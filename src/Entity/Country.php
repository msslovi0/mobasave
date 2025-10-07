<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'mbs_country')]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $iso2 = null;

    /**
     * @var Collection<int, State>
     */
    #[ORM\OneToMany(targetEntity: State::class, mappedBy: 'country')]
    private Collection $states;

    /**
     * @var Collection<int, Dealer>
     */
    #[ORM\OneToMany(targetEntity: Dealer::class, mappedBy: 'country')]
    private Collection $dealers;

    /**
     * @var Collection<int, Manufacturer>
     */
    #[ORM\OneToMany(targetEntity: Manufacturer::class, mappedBy: 'country')]
    private Collection $manufacturers;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'country')]
    private Collection $companies;

    /**
     * @var Collection<int, Model>
     */
    #[ORM\OneToMany(targetEntity: Model::class, mappedBy: 'country')]
    private Collection $models;

    /**
     * @var Collection<int, Storage>
     */
    #[ORM\OneToMany(targetEntity: Storage::class, mappedBy: 'country')]
    private Collection $storages;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $prefix = null;

    public function __construct()
    {
        $this->states = new ArrayCollection();
        $this->dealers = new ArrayCollection();
        $this->manufacturers = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->models = new ArrayCollection();
        $this->storages = new ArrayCollection();
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

    public function getIso2(): ?string
    {
        return $this->iso2;
    }

    public function setIso2(?string $iso2): static
    {
        $this->iso2 = $iso2;

        return $this;
    }

    /**
     * @return Collection<int, State>
     */
    public function getStates(): Collection
    {
        return $this->states;
    }

    public function addState(State $state): static
    {
        if (!$this->states->contains($state)) {
            $this->states->add($state);
            $state->setCountry($this);
        }

        return $this;
    }

    public function removeState(State $state): static
    {
        if ($this->states->removeElement($state)) {
            // set the owning side to null (unless already changed)
            if ($state->getCountry() === $this) {
                $state->setCountry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Dealer>
     */
    public function getDealers(): Collection
    {
        return $this->dealers;
    }

    public function addDealer(Dealer $dealer): static
    {
        if (!$this->dealers->contains($dealer)) {
            $this->dealers->add($dealer);
            $dealer->setCountry($this);
        }

        return $this;
    }

    public function removeDealer(Dealer $dealer): static
    {
        if ($this->dealers->removeElement($dealer)) {
            // set the owning side to null (unless already changed)
            if ($dealer->getCountry() === $this) {
                $dealer->setCountry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Manufacturer>
     */
    public function getManufacturers(): Collection
    {
        return $this->manufacturers;
    }

    public function addManufacturer(Manufacturer $manufacturer): static
    {
        if (!$this->manufacturers->contains($manufacturer)) {
            $this->manufacturers->add($manufacturer);
            $manufacturer->setCountry($this);
        }

        return $this;
    }

    public function removeManufacturer(Manufacturer $manufacturer): static
    {
        if ($this->manufacturers->removeElement($manufacturer)) {
            // set the owning side to null (unless already changed)
            if ($manufacturer->getCountry() === $this) {
                $manufacturer->setCountry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->setCountry($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getCountry() === $this) {
                $company->setCountry(null);
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
            $model->setCountry($this);
        }

        return $this;
    }

    public function removeModel(Model $model): static
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getCountry() === $this) {
                $model->setCountry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Storage>
     */
    public function getStorages(): Collection
    {
        return $this->storages;
    }

    public function addStorage(Storage $storage): static
    {
        if (!$this->storages->contains($storage)) {
            $this->storages->add($storage);
            $storage->setCountry($this);
        }

        return $this;
    }

    public function removeStorage(Storage $storage): static
    {
        if ($this->storages->removeElement($storage)) {
            // set the owning side to null (unless already changed)
            if ($storage->getCountry() === $this) {
                $storage->setCountry(null);
            }
        }

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }
}
