<?php

namespace App\Entity;

use App\Repository\FolkReferenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FolkReferenceRepository::class)
 */
class FolkReference
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=folk::class, inversedBy="folkReferences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $folk;

    /**
     * @ORM\ManyToOne(targetEntity=reference::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFolk(): ?folk
    {
        return $this->folk;
    }

    public function setFolk(?folk $folk): self
    {
        $this->folk = $folk;

        return $this;
    }

    public function getReference(): ?reference
    {
        return $this->reference;
    }

    public function setReference(?reference $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
