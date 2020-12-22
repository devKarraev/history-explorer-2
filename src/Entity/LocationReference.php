<?php

namespace App\Entity;

use App\Repository\LocationReferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Table(uniqueConstraints={
 *        @UniqueConstraint(name="location_id_reference_id",
 *            columns={"location_id", "reference_id"})
 * })
 *
 * @ORM\Entity(repositoryClass=LocationReferenceRepository::class)
 */
class LocationReference
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Location::class, inversedBy="locationReferences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity=Reference::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type ="etc";

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getReference(): ?Reference
    {
        return $this->reference;
    }

    public function setReference(?Reference $reference): self
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
