<?php

namespace App\Entity;

use App\Repository\FunctionkeyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FunctionkeyRepository::class)]
#[ORM\Table(name: 'mbs_functionkey')]
class Functionkey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 5)]
    private ?string $name = null;

    /**
     * @var Collection<int, DigitalFunction>
     */
    #[ORM\OneToMany(targetEntity: DigitalFunction::class, mappedBy: 'functionkey')]
    private Collection $digitalFunctions;

    public function __construct()
    {
        $this->digitalFunctions = new ArrayCollection();
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
            $digitalFunction->setFunctionkey($this);
        }

        return $this;
    }

    public function removeDigitalFunction(DigitalFunction $digitalFunction): static
    {
        if ($this->digitalFunctions->removeElement($digitalFunction)) {
            // set the owning side to null (unless already changed)
            if ($digitalFunction->getFunctionkey() === $this) {
                $digitalFunction->setFunctionkey(null);
            }
        }

        return $this;
    }
}
