<?php

namespace App\Entity;

use App\Repository\DigitalFunctionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DigitalFunctionRepository::class)]
#[ORM\Table(name: 'mbs_digital_function')]
class DigitalFunction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'digitalFunctions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Digital $digital = null;

    #[ORM\ManyToOne(inversedBy: 'digitalFunctions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Functionkey $functionkey = null;

    #[ORM\ManyToOne(inversedBy: 'digitalFunctions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Decoderfunction $decoderfunction = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $hint = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDigital(): ?Digital
    {
        return $this->digital;
    }

    public function setDigital(?Digital $digital): static
    {
        $this->digital = $digital;

        return $this;
    }

    public function getFunctionkey(): ?Functionkey
    {
        return $this->functionkey;
    }

    public function setFunctionkey(?Functionkey $functionkey): static
    {
        $this->functionkey = $functionkey;

        return $this;
    }

    public function getDecoderfunction(): ?Decoderfunction
    {
        return $this->decoderfunction;
    }

    public function setDecoderfunction(?Decoderfunction $decoderfunction): static
    {
        $this->decoderfunction = $decoderfunction;

        return $this;
    }

    public function getHint(): ?string
    {
        return $this->hint;
    }

    public function setHint(?string $hint): static
    {
        $this->hint = $hint;

        return $this;
    }
}
