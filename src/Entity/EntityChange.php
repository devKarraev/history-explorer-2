<?php

namespace App\Entity;

use App\Repository\EntityChangeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntityChangeRepository::class)
 */
class EntityChange
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entityChanges")
     * @ORM\JoinColumn(nullable=false, onDelete="SET NULL")
     */
    private $changedBy;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $modificationType;

    /**
     * @ORM\ManyToOne(targetEntity=Person::class, inversedBy="changes")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity=Location::class, inversedBy="changes")
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="changes")
     */
    private $event;

    /**
     * @ORM\OneToOne(targetEntity=Person::class, mappedBy="updateOf")
     */
    private $updatedPerson;

    /**
     * @ORM\OneToOne(targetEntity=Event::class, mappedBy="updateOf")
     */
    private $updatedEvent;

     public function getId(): ?int
    {
        return $this->id;
    }

    public function getChangedBy(): ?User
    {
        return $this->changedBy;
    }

    public function setChangedBy(?User $changedBy): self
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    public function getModificationType(): ?string
    {
        return $this->modificationType;
    }

    public function setModificationType(string $modificationType): self
    {
        $this->modificationType = $modificationType;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getUpdatedPerson(): ?Person
    {
        return $this->updatedPerson;
    }

    public function setUpdatedPerson(?Person $updatedPerson): self
    {
        $this->updatedPerson = $updatedPerson;

        // set (or unset) the owning side of the relation if necessary
        $newChangeEntityOf = null === $updatedPerson ? null : $this;
        if ($updatedPerson->getUpdateOf() !== $newChangeEntityOf) {
            $updatedPerson->setUpdateOf($newChangeEntityOf);
        }

        return $this;
    }

    public function getUpdatedEvent(): ?Event
    {
        return $this->updatedEvent;
    }

    public function setUpdatedEvent(?Event $updatedEvent): self
    {
        $this->updatedEvent = $updatedEvent;

        // set (or unset) the owning side of the relation if necessary
        $newChangeEntityOf = null === $updatedEvent ? null : $this;
        if ($updatedEvent->getUpdateOf() !== $newChangeEntityOf) {
            $updatedEvent->setUpdateOf($newChangeEntityOf);
        }

        return $this;
    }

}
