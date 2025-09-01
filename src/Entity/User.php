<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: 'mbs_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    final public const ROLE_USER = 'ROLE_USER';
    final public const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Database>
     */
    #[ORM\OneToMany(targetEntity: Database::class, mappedBy: 'user')]
    private Collection $userdatabases;

    /**
     * @var Collection<int, Status>
     */
    #[ORM\OneToMany(targetEntity: Status::class, mappedBy: 'user')]
    private Collection $statuses;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'user')]
    private Collection $categories;

    /**
     * @var Collection<int, Subcategory>
     */
    #[ORM\OneToMany(targetEntity: Subcategory::class, mappedBy: 'user')]
    private Collection $subcategories;

    /**
     * @var Collection<int, Manufacturer>
     */
    #[ORM\OneToMany(targetEntity: Manufacturer::class, mappedBy: 'user')]
    private Collection $manufacturers;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'user')]
    private Collection $companies;

    /**
     * @var Collection<int, Scale>
     */
    #[ORM\OneToMany(targetEntity: Scale::class, mappedBy: 'user')]
    private Collection $scales;

    /**
     * @var Collection<int, ScaleTrack>
     */
    #[ORM\OneToMany(targetEntity: ScaleTrack::class, mappedBy: 'user')]
    private Collection $scaleTracks;

    /**
     * @var Collection<int, Epoch>
     */
    #[ORM\OneToMany(targetEntity: Epoch::class, mappedBy: 'user')]
    private Collection $epochs;

    /**
     * @var Collection<int, Subepoch>
     */
    #[ORM\OneToMany(targetEntity: Subepoch::class, mappedBy: 'user')]
    private Collection $subepoches;

    /**
     * @var Collection<int, Storage>
     */
    #[ORM\OneToMany(targetEntity: Storage::class, mappedBy: 'user')]
    private Collection $storages;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'user')]
    private Collection $projects;

    /**
     * @var Collection<int, Dealer>
     */
    #[ORM\OneToMany(targetEntity: Dealer::class, mappedBy: 'user')]
    private Collection $dealers;

    /**
     * @var Collection<int, Maker>
     */
    #[ORM\OneToMany(targetEntity: Maker::class, mappedBy: 'user')]
    private Collection $makers;

    /**
     * @var Collection<int, Box>
     */
    #[ORM\OneToMany(targetEntity: Box::class, mappedBy: 'user')]
    private Collection $boxes;

    /**
     * @var Collection<int, Condition>
     */
    #[ORM\OneToMany(targetEntity: Condition::class, mappedBy: 'user')]
    private Collection $conditions;

    public function __construct()
    {
        $this->userdatabases = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->subcategories = new ArrayCollection();
        $this->manufacturers = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->scales = new ArrayCollection();
        $this->scaleTracks = new ArrayCollection();
        $this->epochs = new ArrayCollection();
        $this->subepoches = new ArrayCollection();
        $this->storages = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->dealers = new ArrayCollection();
        $this->makers = new ArrayCollection();
        $this->boxes = new ArrayCollection();
        $this->conditions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * @return Collection<int, Database>
     */
    public function getUserdatabases(): Collection
    {
        return $this->userdatabases;
    }

    public function addUserdatabase(Database $userdatabase): static
    {
        if (!$this->userdatabases->contains($userdatabase)) {
            $this->userdatabases->add($userdatabase);
            $userdatabase->setUser($this);
        }

        return $this;
    }

    public function removeUserdatabase(Database $userdatabase): static
    {
        if ($this->userdatabases->removeElement($userdatabase)) {
            // set the owning side to null (unless already changed)
            if ($userdatabase->getUser() === $this) {
                $userdatabase->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Status>
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
            $status->setUser($this);
        }

        return $this;
    }

    public function removeStatus(Status $status): static
    {
        if ($this->statuses->removeElement($status)) {
            // set the owning side to null (unless already changed)
            if ($status->getUser() === $this) {
                $status->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getUser() === $this) {
                $category->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subcategory>
     */
    public function getSubcategories(): Collection
    {
        return $this->subcategories;
    }

    public function addSubcategory(Subcategory $subcategory): static
    {
        if (!$this->subcategories->contains($subcategory)) {
            $this->subcategories->add($subcategory);
            $subcategory->setUser($this);
        }

        return $this;
    }

    public function removeSubcategory(Subcategory $subcategory): static
    {
        if ($this->subcategories->removeElement($subcategory)) {
            // set the owning side to null (unless already changed)
            if ($subcategory->getUser() === $this) {
                $subcategory->setUser(null);
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
            $manufacturer->setUser($this);
        }

        return $this;
    }

    public function removeManufacturer(Manufacturer $manufacturer): static
    {
        if ($this->manufacturers->removeElement($manufacturer)) {
            // set the owning side to null (unless already changed)
            if ($manufacturer->getUser() === $this) {
                $manufacturer->setUser(null);
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
            $company->setUser($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getUser() === $this) {
                $company->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Scale>
     */
    public function getScales(): Collection
    {
        return $this->scales;
    }

    public function addScale(Scale $scale): static
    {
        if (!$this->scales->contains($scale)) {
            $this->scales->add($scale);
            $scale->setUser($this);
        }

        return $this;
    }

    public function removeScale(Scale $scale): static
    {
        if ($this->scales->removeElement($scale)) {
            // set the owning side to null (unless already changed)
            if ($scale->getUser() === $this) {
                $scale->setUser(null);
            }
        }

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
            $scaleTrack->setUser($this);
        }

        return $this;
    }

    public function removeScaleTrack(ScaleTrack $scaleTrack): static
    {
        if ($this->scaleTracks->removeElement($scaleTrack)) {
            // set the owning side to null (unless already changed)
            if ($scaleTrack->getUser() === $this) {
                $scaleTrack->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Epoch>
     */
    public function getEpochs(): Collection
    {
        return $this->epochs;
    }

    public function addEpoch(Epoch $epoch): static
    {
        if (!$this->epochs->contains($epoch)) {
            $this->epochs->add($epoch);
            $epoch->setUser($this);
        }

        return $this;
    }

    public function removeEpoch(Epoch $epoch): static
    {
        if ($this->epochs->removeElement($epoch)) {
            // set the owning side to null (unless already changed)
            if ($epoch->getUser() === $this) {
                $epoch->setUser(null);
            }
        }

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
            $subepoch->setUser($this);
        }

        return $this;
    }

    public function removeSubepoch(Subepoch $subepoch): static
    {
        if ($this->subepoches->removeElement($subepoch)) {
            // set the owning side to null (unless already changed)
            if ($subepoch->getUser() === $this) {
                $subepoch->setUser(null);
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
            $storage->setUser($this);
        }

        return $this;
    }

    public function removeStorage(Storage $storage): static
    {
        if ($this->storages->removeElement($storage)) {
            // set the owning side to null (unless already changed)
            if ($storage->getUser() === $this) {
                $storage->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getUser() === $this) {
                $project->setUser(null);
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
            $dealer->setUser($this);
        }

        return $this;
    }

    public function removeDealer(Dealer $dealer): static
    {
        if ($this->dealers->removeElement($dealer)) {
            // set the owning side to null (unless already changed)
            if ($dealer->getUser() === $this) {
                $dealer->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Maker>
     */
    public function getMakers(): Collection
    {
        return $this->makers;
    }

    public function addMaker(Maker $maker): static
    {
        if (!$this->makers->contains($maker)) {
            $this->makers->add($maker);
            $maker->setUser($this);
        }

        return $this;
    }

    public function removeMaker(Maker $maker): static
    {
        if ($this->makers->removeElement($maker)) {
            // set the owning side to null (unless already changed)
            if ($maker->getUser() === $this) {
                $maker->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Box>
     */
    public function getBoxes(): Collection
    {
        return $this->boxes;
    }

    public function addBox(Box $box): static
    {
        if (!$this->boxes->contains($box)) {
            $this->boxes->add($box);
            $box->setUser($this);
        }

        return $this;
    }

    public function removeBox(Box $box): static
    {
        if ($this->boxes->removeElement($box)) {
            // set the owning side to null (unless already changed)
            if ($box->getUser() === $this) {
                $box->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Condition>
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(Condition $condition): static
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
            $condition->setUser($this);
        }

        return $this;
    }

    public function removeCondition(Condition $condition): static
    {
        if ($this->conditions->removeElement($condition)) {
            // set the owning side to null (unless already changed)
            if ($condition->getUser() === $this) {
                $condition->setUser(null);
            }
        }

        return $this;
    }
}
