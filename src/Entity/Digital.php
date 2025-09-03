<?php

namespace App\Entity;

use App\Repository\DigitalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DigitalRepository::class)]
#[ORM\Table(name: 'mbs_digital')]
class Digital
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $address = null;

    /**
     * @var Collection<int, DigitalFunction>
     */
    #[ORM\OneToMany(targetEntity: DigitalFunction::class, mappedBy: 'digital')]
    private Collection $digitalFunctions;

    /**
     * @var Collection<int, Model>
     */
    #[ORM\OneToMany(targetEntity: Model::class, mappedBy: 'digital')]
    private Collection $models;

    #[ORM\ManyToOne(inversedBy: 'digitals')]
    private ?Protocol $protocol = null;

    #[ORM\ManyToOne(inversedBy: 'digitals')]
    private ?Decoder $decoder = null;

    #[ORM\ManyToOne(inversedBy: 'digitals')]
    private ?Pininterface $pininterface = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $import = null;

        public function __construct()
        {
            $this->digitalFunctions = new ArrayCollection();
            $this->models = new ArrayCollection();
        }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?int
    {
        return $this->address;
    }

    public function setAddress(?int $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, DigitalFunction>
     */
    public function getDigitalFunctions(): Collection
    {
        return $this->digitalFunctions;
    }

    public function addDigitalFunction(DigitalFunction $digitalFunction): static
    {
        if (!$this->digitalFunctions->contains($digitalFunction)) {
            $this->digitalFunctions->add($digitalFunction);
            $digitalFunction->setDigital($this);
        }

        return $this;
    }

    public function removeDigitalFunction(DigitalFunction $digitalFunction): static
    {
        if ($this->digitalFunctions->removeElement($digitalFunction)) {
            // set the owning side to null (unless already changed)
            if ($digitalFunction->getDigital() === $this) {
                $digitalFunction->setDigital(null);
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
            $model->setDigital($this);
        }

        return $this;
    }

    public function removeModel(Model $model): static
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getDigital() === $this) {
                $model->setDigital(null);
            }
        }

        return $this;
    }

    public function getProtocol(): ?Protocol
    {
        return $this->protocol;
    }

    public function setProtocol(?Protocol $protocol): static
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getDecoder(): ?Decoder
    {
        return $this->decoder;
    }

    public function setDecoder(?Decoder $decoder): static
    {
        $this->decoder = $decoder;

        return $this;
    }

    public function getPininterface(): ?Pininterface
    {
        return $this->pininterface;
    }

    public function setPininterface(?Pininterface $pininterface): static
    {
        $this->pininterface = $pininterface;

        return $this;
    }

    public function getImport(): ?string
    {
        return $this->import;
    }

    public function setImport(?string $import): static
    {
        $this->import = $import;

        return $this;
    }

}
