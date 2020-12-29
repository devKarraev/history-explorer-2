<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use App\Repository\ReferenceRepository;
use App\Service\UploaderHelper;
use App\Validator\UncertainNumber;
use App\Validator\PersonFormAge;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Psr\Log\LoggerInterface;
//use Symfony\Component\Validator\Constraints\NotBlank as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 * @ORM\Table(name="person", uniqueConstraints={@ORM\UniqueConstraint(name="id_father_id", columns={"id", "father_id"})}, indexes={@ORM\Index(name="IDX_34DCD176B78A354D", columns={"mother_id"}), @ORM\Index(name="IDX_34DCD176A54DED42", columns={"folk_id"}), @ORM\Index(name="IDX_34DCD1762055B9A2", columns={"father_id"})})
 */
class Person
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="das muss schon sein....")
     * @Groups({"main", "info"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("info")
     */
    private $born;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("info")
     */
    private $died;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("info")
     */
    private $bornEstimated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("info")
     */
    private $diedEstimated;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Groups("info")
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("info")
     */
    private $alternateNames;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("info")
     */
    private $image;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAbstract = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leafStart;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leafOut;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leafLevel;

    /**
     * @ORM\ManyToOne(targetEntity=Person::class, inversedBy="children_father")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups("info")
     */
    private $father;

    /**
     * @ORM\OneToMany(targetEntity=Person::class, mappedBy="father")
     */
    private $children_father;

    /**
     * @ORM\OneToMany(targetEntity=Person::class, mappedBy="mother")
     */
    private $children_mother;

    /**
     * @ORM\ManyToOne(targetEntity=Person::class, inversedBy="children_mother")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups("info")
     */
    private $mother;

    /**
     * @ORM\ManyToOne(targetEntity=Folk::class)
     * @Groups("info")
     */
    private $folk;

    /**
     * @ORM\OneToMany(targetEntity=EntityChange::class, mappedBy="person", cascade={"persist", "remove"})
     */
    private $changes;


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="getChangedPeoples")
     */
    private $owner = null;

    /**
     * @ORM\ManyToOne(targetEntity=Person::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $livedAtTimeOfPerson;

    /**
     * @ORM\ManyToMany(targetEntity=Event::class, mappedBy="participants")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity=PersonReference::class, mappedBy="person", orphanRemoval=true)
     */
    private $personReferences;

    /**
     * @ORM\Column(type="boolean")
     */
    private $approved = false;


    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bornCalculated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $diedCalculated;


    /**
     * @PersonFormAge
     * @UncertainNumber()
     */
    protected $uncertainBorn;
    /**
     * @UncertainNumber()
     */
    protected $uncertainDied;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Job::class, inversedBy="people")
     */
    private $job;


    /**
     * @ORM\ManyToMany(targetEntity=folk::class, inversedBy="people")
     */
    private $progenitor;

    /**
     * @ORM\OneToOne(targetEntity=EntityChange::class, inversedBy="updatedPerson", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="update_of_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    private $updateOf;

    public function __construct()
    {
        $this->children_father = new ArrayCollection();
        $this->children_mother = new ArrayCollection();
        $this->changes = new ArrayCollection();
        $this->reference = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->personReferences = new ArrayCollection();
        $this->job = new ArrayCollection();
        $this->progenitor = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUniqueName(bool $unique = true): ?string
    {
        $parentName = '';
        if($unique) {
            if($this->getFather()) {
                $parentName = ' (' . $this->getFather()->getName(false) .')';
            } else if ($this->getMother()) {
                $parentName = ' ('.$this->getMother()->getName(false).')';
            }
        }
        return $this->name . $parentName;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getShortName(): ?string
    {
        if(strpos($this->name, " ") === false)
            return $this->name;
        return strstr ($this->name, " ", true);
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBorn(bool $estimateIfNull = false, bool $calculatedIfNull = false, $formatted = false)
    {

        $ret = $this->born;
        $estimated = '';
        if(!$this->born && $estimateIfNull === true) {
            $ret =  $this->getBornEstimated(1);
            if($ret === null && $calculatedIfNull === true) {
                $ret = $this->getBornCalculated();
               // if($ret!=null)dd($ret);
            } else {
                $estimated = '?';
            }
        }

        if($ret && $formatted) {
            $ret = $ret < 0 ? -$ret . ' BC' : $ret . 'AD';
            $ret.= $estimated;
        }
        return $ret;
    }

    public function getDied(bool $estimateIfNull = false, bool $calculatedIfNull = false, $formatted = false)
    {
        $ret = $this->died;
        $estimated = '';
        if(!$this->died && $estimateIfNull === true)
        {
            $ret = $this->getDiedEstimated(1);
            if($ret === null && $calculatedIfNull) {
                $ret = $this->getDiedCalculated();
            } else {
                $estimated = '?';
            }
        }
        if($ret && $formatted) {
            $ret = $ret < 0 ? -$ret . ' BC' : $ret . ' AD';
            $ret.= $estimated;
        }
        return $ret;
    }

    public function setBorn(?int $born): self
    {
        $this->born = $born;

        return $this;
    }

    public function setAge(?int $age) : self
    {
       //just a dummy f()
        return $this;
    }

    public function getAge(bool $estimateIfNull = false, bool $calculatedIfNull = false): ?int
    {
        $b = $this->getBorn($estimateIfNull, $calculatedIfNull);
        $d = $this->getDied($estimateIfNull, $calculatedIfNull);

        if($b && $d && $b <= $d){
            return ($d - $b);
        }
        return null;
    }

    public function setDied(?int $died): self
    {
        $this->died = $died;

        return $this;
    }

    public function getBornEstimated($recursion = 10, bool $calculationIfNull = false, $formatted = false): ?int
    {
        if(!$this->bornEstimated && $recursion > 0)
        {
            if($calculationIfNull && $this->getBornCalculated()) {
                return $this->getBornCalculated();
            }
            if( $this->livedAtTimeOfPerson) {
                if ($this->livedAtTimeOfPerson->getBorn() !== null) {
                    return $this->livedAtTimeOfPerson->getBorn();
                }
                // beware, don't use recursion  (getBorn(true)  here!
                return $this->livedAtTimeOfPerson->getBornEstimated($recursion - 1, $calculationIfNull, $formatted);
            }
        }
        if ($formatted) {
            return $this->bornEstimated . ' ?';
        }
        return $this->bornEstimated;
    }

    public function setBornEstimated(?int $bornEstimated): self
    {
        $this->bornEstimated = $bornEstimated;

        return $this;
    }

    public function getDiedEstimated($recursion = 10, bool $calculationIfNull = false, $formatted = false): ?int
    {
        if(!$this->diedEstimated && $recursion > 0)
        {
            if($calculationIfNull && $this->getDiedCalculated()) {
                return $this->getDiedCalculated();
            }

            if($this->livedAtTimeOfPerson) {
                if( $this->livedAtTimeOfPerson->getDied() !== null)
                    return $this->livedAtTimeOfPerson->getDied();
                // beware, don't use recursion  (getDied(true)  here!
                return $this->livedAtTimeOfPerson->getDiedEstimated($recursion - 1);
            }
        }
        if ($formatted) {
            return $this->diedEstimated . ' ?';
        }
        return $this->diedEstimated;
    }

    public function setDiedEstimated(?int $diedEstimated): self
    {
        $this->diedEstimated = $diedEstimated;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getAlternateNames(): ?string
    {
        return $this->alternateNames;
    }

    public function setAlternateNames(string $alternateNames): self
    {
        $this->alternateNames = $alternateNames;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getImage(): ?string
    {
        if($this->image !== null) {
            return UploaderHelper::PERSON_IMAGE.'/'. $this->image;
        } else {
            return UploaderHelper::PERSON_IMAGE.'/'. 'default_'. $this->getGender() .'.png';
        }
        return null;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getIsAbstract(): ?bool
    {
        return $this->isAbstract;
    }

    public function setIsAbstract(bool $isAbstract): self
    {
        $this->isAbstract = $isAbstract;

        return $this;
    }

    public function getLeafStart(): ?int
    {
        return $this->leafStart;
    }

    public function setLeafStart(?int $leafStart): self
    {
        $this->leafStart = $leafStart;

        return $this;
    }

    public function getLeafOut(): ?int
    {
        return $this->leafOut;
    }

    public function setLeafOut(?int $leafOut): self
    {
        $this->leafOut = $leafOut;

        return $this;
    }

    public function getLeafLevel(): ?int
    {
        return $this->leafLevel;
    }

    public function setLeafLevel(?int $leafLevel): self
    {
        $this->leafLevel = $leafLevel;

        return $this;
    }

    public function getFather(): ?self
    {
        return $this->father;
    }

    public function setFather(?self $father): self
    {
        $this->father = $father;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return new ArrayCollection(
            array_merge(
                $this->children_father->toArray(),
                $this->children_mother->toArray()
            )
        );
    }

    /*public function setChildren($children)
    {
        if($children !== null)
        {
            dd($this->getChildren());
            foreach ($children as $child) {
                $this->addChild($child);
            }
        }
    }*/

    public function addChild(self $child): self
    {
        if(!$this->getChildren()->contains($child)) {
            if ($this->getGender() == 'm') {
                $this->children_father[] = $child;
                $child->setFather($this);
            } else {
                $this->children_mother[] = $child;
                $child->setMother($this);
            }
        }
    }

    public function removeChild(self $child): self
    {
        if ($this->getChildren()->contains($child)) {
            if ($this->getGender() == 'm') {
                $this->children_father->removeElement($child);
                // set the owning side to null (unless already changed)
                if ($child->getFather() === $this) {
                    $child->setFather(null);
                }
            } else {
                $this->children_mother->removeElement($child);
                // set the owning side to null (unless already changed)
                if ($child->getMother() === $this) {
                    $child->setMother(null);
                }
            }
        }
        return $this;
    }

    public function getMother(): ?self
    {
        return $this->mother;
    }

    public function setMother(?self $mother): self
    {
        $this->mother = $mother;

        return $this;
    }

    public function getFolk()
    {
        return $this->folk;
    }

    public function setFolk(?self $folk): self
    {
        $this->folk = $folk;

        return $this;
    }

    /*public function getImagePath()
    {
        $image = $this->getImage();

        if($image === null)
        {
            return 'default_'. $this->getGender() .'.png';
        }
        return $image;
    }*/

    /**
     * @return Collection|EntityChange[]
     */
    public function getChanges(): Collection
    {
        return $this->changes;
    }

    /**
     * @return Collection|EntityChange[]
     */
    public function getUserChanges(User $user) : Collection {

        $ret = new ArrayCollection();
        if( in_array('ROLE_ACCEPT_CHANGES', $user->getRoles())) {
            $ret = $this->changes;
        } else {
            /** @var EntityChange $change */
            foreach ( $this->changes as $change) {
                if($change->getChangedBy() === $user) {
                    $ret->add($change);
                }
            }
        }
        return $ret;
    }

    public function addChange(EntityChange $change): self
    {
        if (!$this->changes->contains($change)) {
            $this->changes[] = $change;
            $change->setPerson($this);
        }

        return $this;
    }

    public function removeChange(EntityChange $change): self
    {
        if ($this->changes->contains($change)) {
            $this->changes->removeElement($change);
            // set the owning side to null (unless already changed)
            if ($change->getPerson() === $this) {
                $change->setPerson(null);
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

    public function isPublished() : bool
    {
        return $this->owner === null;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param ExecutionContextInterface $context
     * @param $payload
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (stripos($this->getName(), 'Hans') !== false) {
            $context->buildViolation('No Hans allowed!')
                ->atPath('name')
                ->addViolation();
        };
    }

    public function getLivedAtTimeOfPerson(): ?self
    {
        return $this->livedAtTimeOfPerson;
    }

    public function setLivedAtTimeOfPerson(?self $livedAtTimeOfPerson): self
    {
        $this->livedAtTimeOfPerson = $livedAtTimeOfPerson;

        return $this;
    }

    public function getReferenceList($type = null): array
    {
        $list = [];
        foreach($this->getPersonReferences() as $personReference) {
            //$ref = $personReference->getReference();
            if(!$type || $personReference->getType() == $type){
                $list[] = $personReference;
            }
        }
        /** @var Reference $ref*/
       // dd($this->personReferences);
       return $list;

    }

   /* public function setReferenceList($references): self
    {
        // TODO
    //dd($references);
    return $this;
    }*/

    /*public function addReference(Reference $reference): self
    {
        if (!$this->reference->contains($reference)) {
            $this->reference[] = $reference;
        }

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->reference->contains($reference)) {
            $this->reference->removeElement($reference);
        }

        return $this;
    }*/

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
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            $event->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection|PersonReference[]
     */
    public function getPersonReferences(): Collection
    {
        return $this->personReferences;
    }

    public function addPersonReference(PersonReference $personReference): self
    {
        if (!$this->personReferences->contains($personReference)) {
            $this->personReferences[] = $personReference;
            $personReference->setPerson($this);
        }

        return $this;
    }

    public function removePersonReference(PersonReference $personReference): self
    {
        if ($this->personReferences->contains($personReference)) {
            $this->personReferences->removeElement($personReference);
            // set the owning side to null (unless already changed)
            if ($personReference->getPerson() === $this) {
                $personReference->setPerson(null);
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

    /**
     * @return mixed
     */
    public function getBornUncertain()
    {
        return $this->bornUncertain;
    }

    /**
     * @param mixed $bornUncertain
     */
    public function setBornUncertain($bornUncertain): void
    {
        $this->bornUncertain = $bornUncertain;
    }

    /**
     * @return mixed
     */
    public function getUncertainBorn()
    {
        return $this->uncertainBorn;
    }

    /**
     * @param mixed $uncertainBorn
     */
    public function setUncertainBorn($uncertainBorn): void
    {
        $this->uncertainBorn = $uncertainBorn;
    }

    public function getBornCalculated(): ?int
    {
        return $this->bornCalculated;
    }

    public function setBornCalculated(?int $bornCalculated): self
    {
        $this->bornCalculated = $bornCalculated;

        return $this;
    }

    public function getDiedCalculated(): ?int
    {
        return $this->diedCalculated;
    }

    public function setDiedCalculated(?int $diedCalculated): self
    {
        $this->diedCalculated = $diedCalculated;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUncertainDied()
    {
        return $this->uncertainDied;
    }

    /**
     * @param mixed $uncertainDied
     */
    public function setUncertainDied($uncertainDied): void
    {
        $this->uncertainDied = $uncertainDied;
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
     * @return Collection|Job[]
     */
    public function getJob(): Collection
    {
        return $this->job;
    }

    public function addJob(Job $job): self
    {
        if (!$this->job->contains($job)) {
            $this->job[] = $job;
        }

        return $this;
    }

    public function removeJob(Job $job): self
    {
        if ($this->job->contains($job)) {
            $this->job->removeElement($job);
        }
        return $this;
    }

    /**
     * @return Collection|folk[]
     */
    public function getProgenitor(): Collection
    {
        return $this->progenitor;
    }

    public function addProgenitor(folk $progenitor): self
    {
        if (!$this->progenitor->contains($progenitor)) {
            $this->progenitor[] = $progenitor;
        }

        return $this;
    }

    public function removeProgenitor(folk $progenitor): self
    {
        if ($this->progenitor->contains($progenitor)) {
            $this->progenitor->removeElement($progenitor);
        }

        return $this;
    }

    public function getUpdateOf(): ?EntityChange
    {
        return $this->updateOf;
    }

    public function setUpdateOf(?EntityChange $updateOf): self
    {
        $this->updateOf = $updateOf;

        return $this;
    }

    public function showInList() :bool{
        $ret = true;

        if($this->getUpdateOf()) {
              $type = $this->getUpdateOf()->getModificationType();
              if($type == 'edit') {
                  $ret = false;
                }
        }
        return $ret;
    }

    public function hasUpdate(): bool {
        $ret = false;
        foreach ($this->getChanges() as $change) {
            if($change->getModificationType() != 'edit_init') {
                $ret = true;
                break;
            }
        }
        return $ret;

    }

    public function getUpdatedName() :?string {
        $ret = null;

        foreach ($this->getChanges() as $change) {
            if($change->getModificationType() == 'edit') {

               // dd($change->getPerson());
                if($change->getUpdatedPerson())
                {
                    $newName = $change->getUpdatedPerson()->getName();
                    $ret = $newName == $this->getName() ? null : $newName;
                    }
            }
        }
        return $ret;

    }


    public function updateFromChange(EntityManagerInterface $em, Person $p){
        /*if($this->name !== $p->getName()) $this->setName($p->getName());

        if($this->name !== $p->getName()) $this->setName($p->getName());*/

        $em->refresh($p);
       // dd($p);

        $this->setName($p->getName());
        $this->setDescription($p->getDescription());
        $this->setAlternateNames($p->getAlternateNames()?? "");

        $this->setBorn($p->getBorn());
        $this->setBornEstimated($p->getBornEstimated());
        $this->setBornCalculated($p->getBornCalculated());
        $this->setDied($p->getDied());
        $this->setDiedEstimated($p->getDiedEstimated());
        $this->setDiedCalculated($p->getDiedCalculated());

        $this->setFather($p->getFather());
        $this->setMother($p->getMother());


    }

}
