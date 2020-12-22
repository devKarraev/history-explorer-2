<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *     fields={"email"},
 *     message="You've already registered under this account!"
 * )
 */
class User implements UserInterface
{
    const PERSON = 'person';
    const EVENT = 'event';
    const LOCATION = 'location';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups("main")
     * @Assert\NotBlank(message = "Please enter your email")
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("main")
     */
    private $firstName;

    /**
     * @ORM\OneToMany(targetEntity=EntityChange::class, mappedBy="changedBy", orphanRemoval=true)
     */
    private $entityChanges;

    /**
     * @ORM\OneToMany(targetEntity=Person::class, mappedBy="owner")
     */
    private $getChangedPeoples;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     */
    private $agreedTermsAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $personCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $locationCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $eventCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $chapterCount = 0;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="owner")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity=Location::class, mappedBy="owner")
     */
    private $locations;


    public function __construct()
    {
        $this->entityChanges = new ArrayCollection();
        $this->getChangedPeoples = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using bcrypt or argon
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return Collection|EntityChange[]
     */
    public function getEntityChanges(): Collection
    {
        return $this->entityChanges;
    }

    public function addEntityChange(EntityChange $entityChange): self
    {
        if (!$this->entityChanges->contains($entityChange)) {
            $this->entityChanges[] = $entityChange;
            $entityChange->setChangedBy($this);
        }

        return $this;
    }

    public function removeEntityChange(EntityChange $entityChange): self
    {
        if ($this->entityChanges->contains($entityChange)) {
            $this->entityChanges->removeElement($entityChange);
            // set the owning side to null (unless already changed)
            if ($entityChange->getChangedBy() === $this) {
                $entityChange->setChangedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Person[]
     */
    public function getGetChangedPeoples(): Collection
    {
        return $this->getChangedPeoples;
    }

    public function addGetChangedPeople(Person $getChangedPeople): self
    {
        if (!$this->getChangedPeoples->contains($getChangedPeople)) {
            $this->getChangedPeoples[] = $getChangedPeople;
            $getChangedPeople->setOwner($this);
        }

        return $this;
    }

    public function removeGetChangedPeople(Person $getChangedPeople): self
    {
        if ($this->getChangedPeoples->contains($getChangedPeople)) {
            $this->getChangedPeoples->removeElement($getChangedPeople);
            // set the owning side to null (unless already changed)
            if ($getChangedPeople->getOwner() === $this) {
                $getChangedPeople->setOwner(null);
            }
        }

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }



    public function __toString()
    {
        return $this->getFirstName();
    }
    
    public function getAvatarUrl(int $size = null): string
    {
        $url = 'https://robohash.org/'.$this->getEmail().'?set=set5';

        if ($size) {
            $url .= sprintf('&size=%dx%d', $size, $size);
        }
        return $url;
    }


    public function getAgreedTermsAt(): ?\DateTimeInterface
    {
        return $this->agreedTermsAt;
    }
    public function agreeToTerms()
    {
        $this->agreedTermsAt = new \DateTime();
    }

    public function getPersonCount(): ?int
    {
        return $this->personCount;
    }

    public function setPersonCount(int $personCount): self
    {
        $this->personCount = $personCount;

        return $this;
    }

    public function getLocationCount(): ?int
    {
        return $this->locationCount;
    }

    public function setLocationCount(int $locationCount): self
    {
        $this->locationCount = $locationCount;

        return $this;
    }

    public function getEventCount(): ?int
    {
        return $this->eventCount;
    }

    public function setEventCount(int $eventCount): self
    {
        $this->eventCount = $eventCount;

        return $this;
    }

    public function getChapterCount(): ?int
    {
        return $this->chapterCount;
    }

    public function setChapterCount(int $chapterCount): self
    {
        $this->chapterCount = $chapterCount;

        return $this;
    }

    public function countChange(string $type)
    {
        switch ($type) {
            case self::PERSON:
                $this->personCount++;
                break;
            case self::EVENT:
                $this->eventCount++;
                break;
            case self::LOCATION:
                $this->locationCount++;
                break;
        }
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
            $event->setOwner($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getOwner() === $this) {
                $event->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Location[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            $location->setOwner($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            // set the owning side to null (unless already changed)
            if ($location->getOwner() === $this) {
                $location->setOwner(null);
            }
        }

        return $this;
    }


}
