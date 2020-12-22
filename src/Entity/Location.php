<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=LocationRepository::class)
 */
class Location
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("main")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("main")
     */
    private $todayKnownAs;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     * @Groups("main")
     */
    private $lat;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     * @Groups("main")
     */
    private $lon;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="location")
     */
    private $events;

    /**
     * @ORM\Column(type="boolean")
     */
    private $approved;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=LocationReference::class, mappedBy="location", orphanRemoval=true)
     */
    private $locationReferences;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity=EntityChange::class, mappedBy="locationChanges")
     */
    private $changes;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="locations")
     */
    private $owner;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->locationReferences = new ArrayCollection();
        $this->changes = new ArrayCollection();
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

    public function getTodayKnownAs(): ?string
    {
        return $this->todayKnownAs;
    }

    public function setTodayKnownAs(?string $todayKnownAs): self
    {
        $this->todayKnownAs = $todayKnownAs;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?string
    {
        return $this->lon;
    }

    public function setLon(?string $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setLocation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getLocation() === $this) {
                $event->setLocation(null);
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

    public function __toString()
    {
        return $this->name;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|LocationReference[]
     */
    public function getLocationReferences(): Collection
    {
        return $this->locationReferences;
    }

    public function addLocationReference(LocationReference $locationReference): self
    {
        if (!$this->locationReferences->contains($locationReference)) {
            $this->locationReferences[] = $locationReference;
            $locationReference->setLocation($this);
        }

        return $this;
    }

    public function removeLocationReference(LocationReference $locationReference): self
    {
        if ($this->locationReferences->contains($locationReference)) {
            $this->locationReferences->removeElement($locationReference);
            // set the owning side to null (unless already changed)
            if ($locationReference->getLocation() === $this) {
                $locationReference->setLocation(null);
            }
        }

        return $this;
    }

    public function getReferenceList($type = null): array
    {
        $list = [];
        ;
        foreach($this->getLocationReferences() as $locationReference) {
            //$ref = $locationReference->getReference();

            if(!$type || $locationReference->getType() == $type){
                $list[] = $locationReference;
            }
        }
        /** @var Reference $ref*/
        return $list;

    }



    public function getImage(): ?string
    {
        if($this->image !== null) {
            return UploaderHelper::LOCATION_IMAGE.'/'. $this->image;
        } else {
            $defaultFileName = UploaderHelper::LOCATION_IMAGE.'/'. $this->type .'.png';
           /* if(!file_exists($defaultFileName)) {
                dd($defaultFileName);
                $defaultFileName = UploaderHelper::LOCATION_IMAGE.'/'. 'default.png';
            }*/
            return $defaultFileName;
        }
        return null;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|EntityChange[]
     */
    public function getChanges(): Collection
    {
        return $this->changes;
    }

    public function addChange(EntityChange $change): self
    {
        if (!$this->changes->contains($change)) {
            $this->changes[] = $change;
            $change->setLocation($this);
        }

        return $this;
    }

    public function removeChange(EntityChange $change): self
    {
        if ($this->changes->contains($change)) {
            $this->changes->removeElement($change);
            // set the owning side to null (unless already changed)
            if ($change->getLocation() === $this) {
                $change->setLocation(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
