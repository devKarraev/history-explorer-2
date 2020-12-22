<?php

namespace App\Entity;

use App\Repository\FolkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FolkRepository::class)
 */
class Folk
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity=FolkReference::class, mappedBy="folk")
     */
    private $folkReferences;

    /**
     * @ORM\Column(type="boolean")
     */
    private $approved;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;


    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $died;


    /**
     * @ORM\ManyToOne(targetEntity=Folk::class, inversedBy="tribes")
     */
    private $fatherFolk;

    /**
     * @ORM\OneToMany(targetEntity=Folk::class, mappedBy="fatherFolk")
     */
    private $tribes;

    /**
     * @ORM\ManyToMany(targetEntity=Person::class, mappedBy="progenitor")
     */
    private $people;


    public function __construct()
    {
        $this->folkReferences = new ArrayCollection();
        $this->progenitors = new ArrayCollection();
        $this->tribes = new ArrayCollection();
        $this->people = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|FolkReference[]
     */
    public function getFolkReferences(): Collection
    {
        return $this->folkReferences;
    }

    public function addFolkReference(FolkReference $folkReference): self
    {
        if (!$this->folkReferences->contains($folkReference)) {
            $this->folkReferences[] = $folkReference;
            $folkReference->setFolk($this);
        }

        return $this;
    }

    public function removeFolkReference(FolkReference $folkReference): self
    {
        if ($this->folkReferences->contains($folkReference)) {
            $this->folkReferences->removeElement($folkReference);
            // set the owning side to null (unless already changed)
            if ($folkReference->getFolk() === $this) {
                $folkReference->setFolk(null);
            }
        }

        return $this;
    }

    public function getApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

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
    public function __toString()
    {
        return $this->name;
    }

    public function getDied(): ?int
    {
        return $this->died;
    }

    public function setDied(?int $died): self
    {
        $this->died = $died;

        return $this;
    }


    public function getFatherFolk(): ?self
    {
        return $this->fatherFolk;
    }

    public function setFatherFolk(?self $fatherFolk): self
    {
        $this->fatherFolk = $fatherFolk;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getTribes(): Collection
    {
        return $this->tribes;
    }

    public function addTribe(self $tribe): self
    {
        if (!$this->tribes->contains($tribe)) {
            $this->tribes[] = $tribe;
            $tribe->setFatherFolk($this);
        }

        return $this;
    }

    public function removeTribe(self $tribe): self
    {
        if ($this->tribes->contains($tribe)) {
            $this->tribes->removeElement($tribe);
            // set the owning side to null (unless already changed)
            if ($tribe->getFatherFolk() === $this) {
                $tribe->setFatherFolk(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Person[]
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people[] = $person;
            $person->addProgenitor($this);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->people->contains($person)) {
            $this->people->removeElement($person);
            $person->removeProgenitor($this);
        }

        return $this;
    }


}
