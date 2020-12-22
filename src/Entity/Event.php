<?php

namespace App\Entity;

use App\Repository\EventRepository;
use App\Service\UploaderHelper;
use App\Validator\UncertainNumber;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Person::class, inversedBy="events")
     */
    private $participants;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $year;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class)

     */
    private $happenedBefore;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class)

     */
    private $happenedAfter;

    /**
     * @ORM\ManyToOne(targetEntity=Location::class, inversedBy="events")
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    private $image = null;

    /**
     * @ORM\OneToMany(targetEntity=EventReference::class, mappedBy="event", orphanRemoval=true)
     */
    private $eventReferences;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="events")
     */
    private $owner;

    /**
     * @ORM\Column(type="boolean")
     */
    private $approved = false;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2, nullable=true)
     */
    private $yearCalculated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $yearEstimated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orderedIndex;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->eventReferences = new ArrayCollection();
        $this->changes = new ArrayCollection();
    }

    /**
     * @UncertainNumber()
     */
    protected $uncertainTime;

    /**
     * @ORM\OneToMany(targetEntity=EntityChange::class, mappedBy="eventChanges")
     */
    private $changes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hide;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $relativeTime;


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Person[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Person $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(Person $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
        }

        return $this;
    }

    public function getYear(bool $estimateIfNull = false, $calculateIfNull = false, $formatted = false)
    {
        $ret = $this->year;
        $estimated = '';

        if(!$this->year && $estimateIfNull === true) {

            $ret =  $this->getYearEstimated($calculateIfNull);

            if($ret !== null) {
                $estimated = '?';
            }
        }

        if($ret && $formatted) {
            $ret = $ret < 0 ? -$ret . ' BC' : $ret . 'AD';
            $ret.= $estimated;
        }

        return $ret;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getHappenedBefore(): ?self
    {
        return $this->happenedBefore;
    }

    public function setHappenedBefore(?self $happenedBefore): self
    {
        $this->happenedBefore = $happenedBefore;

        return $this;
    }

    public function getHappenedAfter(): ?self
    {
        return $this->happenedAfter;
    }

    public function iterateYear(int &$counter, bool $up, $calculated = false): int
    {
        $result = $this->getYear(true);
        if(!$result && $calculated) {
            $result = $this->getYearCalculated();
        }
        if(!$result) {
            if ($up) {
                if ($this->getHappenedAfter()) {
                    $counter++;
                    $result = $this->getHappenedAfter()->iterateYear($counter, true, $calculated);
                }
            } else {
                if ($this->getHappenedBefore()) {
                    $counter++;
                    $result = $this->getHappenedBefore()->iterateYear($counter, false, $calculated);
                }
            }
        }
        return $result;
    }

    public function setHappenedAfter(?self $happenedAfter): self
    {
        $this->happenedAfter = $happenedAfter;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        if($this->image !== null) {
            return UploaderHelper::EVENT_IMAGE.'/'. $this->image;
        } else {
            return UploaderHelper::EVENT_IMAGE.'/'. 'default.png';
        }
        return null;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection|EventReference[]
     */
    public function getEventReferences(): Collection
    {
        return $this->eventReferences;
    }

    public function addEventReference(EventReference $eventReference): self
    {
        if (!$this->eventReferences->contains($eventReference)) {
            $this->eventReferences[] = $eventReference;
            $eventReference->setEvent($this);
        }

        return $this;
    }

    public function getReferenceList($type = null): array
    {
        $list = [];
        foreach($this->getEventReferences() as $eventReference) {
            //$ref = $eventReference->getReference();
            if(!$type || $eventReference->getType() == $type){
                $list[] = $eventReference;
            }
        }
        /** @var Reference $ref*/
        return $list;

    }

    public function removeEventReference(EventReference $eventReference): self
    {
        if ($this->eventReferences->contains($eventReference)) {
            $this->eventReferences->removeElement($eventReference);
            // set the owning side to null (unless already changed)
            if ($eventReference->getEvent() === $this) {
                $eventReference->setEvent(null);
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

    public function getApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    private function getTimespan(&$maxVal, &$minVal, bool $estimate = false, bool $calculate = false){

        foreach($this->getParticipants() as $person) {
            $b  = $person->getBorn($estimate, $calculate);
            $d  = $person->getDied($estimate, $calculate);


            /*if($minYearFromPrevEvent) {
                $b = $b ? max($minYearFromPrevEvent, $b) : $minYearFromPrevEvent;
                $d = $d ? max($minYearFromPrevEvent, $d) : $minYearFromPrevEvent;
            }*/

            $maxVal =  $b ? ($maxVal ? max($maxVal, $b) : $b) : $maxVal;
            $minVal = $d ? ($minVal ? min($minVal, $d) : $d) : $minVal;
        }

    }

    public function guessTime($prevEventYear = null) : ?int
    {
        $result = $this->getYear(true);

        if(!$result) {
            if($this->getRelativeTime() != null) {
                if($this->getRelativeTime() > 0) {
                    if($prevEventYear) {
                        $result = $prevEventYear + $this->getRelativeTime();
                        $this->setYearEstimated($result);
                        $this->setYearCalculated($result);
                        echo $result ."<br>";
                        return $result;
                    }

                } else {
                    dd("todo: negative relations");
                }
            }

            echo "prev: ".$prevEventYear."<br>";
            $counterDown = 0;
            $nextEventYear = $this->iterateYear($counterDown, false);

            $upper = null; $lower = null; $upperE = null; $lowerE = null; $upperC = null;
            $lowerC = null;

            $this->getTimespan( $lower, $upper );
            $this->getTimespan($lowerE, $upperE, true);
            $this->getTimespan($lowerC, $upperC, true, true);

            echo sizeof($this->getParticipants()) . " from $lower to $upper <br>";
            echo "Estimation from $lowerE to $upperE <br>";
            echo "Computation from $lowerC to $upperC <br>";

            $l = $lowerE;
            $u = $upperE;
           /* if($lowerE && $upper) {
                $l = min($lowerE, $upper);
                echo "<b>lower</b> =  min($lowerE, $upper) = $l<br>";
            }
            if($upperE && $lower) {
                $u = max($upperE, $lower);
                echo "<b>upper</b> =  max($upperE, $lower) = $u<br>";
            }
            if($l && $u && $l > $u) {
                $upperE = min($upperE, 0.5 * ($l + $u));
                $lowerE = max($lower, 0.5 * ($l + $u));
                echo "choosing mean of $l and  $u, upper = $upperE, lower = $lowerE <br>";
                dd("!");
            }*/
            $lower = $lower ? ($lowerE ? $lowerE : $lower) : $lowerC;
            $upper = $upper ? ($upperE ? $upperE : $upper) : $upperC;

            $upper = min($upper, $nextEventYear);
            if($prevEventYear)
                $lower = max($lower, $prevEventYear);

            echo "from <b>lower</b> $lower to <b>upper</b> $upper <br>";

            $result = ($upper && $lower ) ? 0.5 * ($upper + $lower) : ($upper ? $upper : $lower? $lower : null);

            //if(!$result)
            {
                $counterUp = 0;
                $counterDown = 0;
                $upGuess = $this->iterateYear($counterUp, true);
                $downGuess = $this->iterateYear($counterDown, false);

                echo "$upGuess , $counterUp, $downGuess, $counterDown  <br>";

                $result = ($upGuess && $downGuess ) ?
                    ($upGuess + $counterUp * ($downGuess - $upGuess) / ($counterUp + $counterDown )) :
                    ($upGuess ? $upGuess + $counterUp * 1 : $downGuess ? $downGuess -  $counterDown * 1 : null);

                if($upper)
                    $result = min($upper, $result);
                if($lower)
                    $result = max($result, $lower);


            }
          /*  if($prevEventYear) {
                $result = max($result, $prevEventYear);
            }*/
        }
        echo $result ."<br>";
        $this->setYearCalculated($result);
        return $result;
    }

    public function getYearEstimated(bool $calculationIfNull = false): ?int
    {

        $result = $this->yearEstimated;
        if(!$result && $calculationIfNull) {

            $result = $this->getYearCalculated();
        }
        return $result;
    }

    public function setYearEstimated(?int $year_estimated): self
    {
        $this->yearEstimated = $year_estimated;

        return $this;
    }

    public function getYearCalculated(): ?float
    {
        return $this->yearCalculated;
    }

    public function setYearCalculated(?float $year_calculated): self
    {
        $this->yearCalculated = $year_calculated;

        return $this;
    }

    public function getOrderedIndex(): ?int
    {
        return $this->orderedIndex;
    }

    public function setOrderedIndex(?int $orderedIndex): self
    {
        $this->orderedIndex = $orderedIndex;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUncertainTime()
    {
        return $this->uncertainTime;
    }

    /**
     * @param mixed $uncertainTime
     */
    public function setUncertainTime($uncertainTime): void
    {
        $this->uncertainTime = $uncertainTime;
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
            $change->setEventChanges($this);
        }

        return $this;
    }

    public function removeChange(EntityChange $change): self
    {
        if ($this->changes->contains($change)) {
            $this->changes->removeElement($change);
            // set the owning side to null (unless already changed)
            if ($change->getEvent() === $this) {
                $change->setEventChanges(null);
            }
        }

        return $this;
    }

    public function getHide(): ?bool
    {
        return $this->hide;
    }

    public function setHide(bool $hide): self
    {
        $this->hide = $hide;

        return $this;
    }

    public function getRelativeTime(): ?float
    {
        return $this->relativeTime;
    }

    public function setRelativeTime(?float $relativeTime): self
    {
        $this->relativeTime = $relativeTime;

        return $this;
    }
}
