<?php

namespace App\Form\Model;

use App\Entity\Person;
use App\Validator\UncertainNumber;
use App\Validator\UncertainNumberValidator;
use App\Validator\UniqueUser;
use Symfony\Component\Validator\Constraints as Assert;

class PersonFormModel
{

    //private $person;

    /**
     * @UncertainNumber()
     */
    protected $uncertainBorn;
    /**
     * @UncertainNumber()
     */
    protected $uncertainDied;

    public function __construct(Person $person)
    {
        $this->uncertainBorn = $person->getBorn(true, true, true);
        $this->uncertainDied = $person->getDied(true, true, true);
        //$this->person = $person;
       // $this->id = $person->getId();
       // $this->name = $person->getName();
       // $this->gender = $person->getGender();
       // $this->image = $person->getImage();
       // $this->isAbstract = $person->getIsAbstract();
       // $this->father = $person->getFather();
      //  $this->mother = $person->getMother();
       // $this->folk = $person->getFolk();
        //$this->livedAtTimeOfPerson = $person->getLivedAtTimeOfPerson();
        //$this->personReferences = $person->getPersonReferences();

    }

    public function isEqual(UncertainNumberValidator $uncertainNumberValidator, Person $p, bool $born = true) : bool
    {

        if($born) {
             return $uncertainNumberValidator->getValue($p->getUncertainBorn()) === $uncertainNumberValidator->getValue($this->uncertainBorn)
                 && $uncertainNumberValidator->isUncertain($p->getUncertainBorn()) === $uncertainNumberValidator->isUncertain($this->uncertainBorn);
        }

        return $uncertainNumberValidator->getValue($p->getUncertainDied()) === $uncertainNumberValidator->getValue($this->uncertainDied)
                && $uncertainNumberValidator->isUncertain($p->getUncertainDied()) === $uncertainNumberValidator->isUncertain($this->uncertainDied);

    }
//    /**
//     * @return int|null
//     */
//    public function getId(): ?int
//    {
//        return $this->person->getId();
//    }

//    /**
//     * @return Person
//     */
//    public function getPerson(): Person
//    {
//        return $this->person;
//    }
//
//    /**
//     * @param Person $person
//     */
//    public function setPerson(Person $person): void
//    {
//        $this->person = $person;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getName()
//    {
//        return $this->person->getName();
//    }
//
//    /**
//     * @param mixed $name
//     */
//    public function setName($name): void
//    {
//        $this->person->setName($name);
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getGender()
//    {
//        return $this->person->getGender();
//    }
//
//    /**
//     * @param mixed $gender
//     */
//    public function setGender($gender): void
//    {
//        $this->person->setGender($gender);
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getImage()
//    {
//        return $this->person->getImage();
//    }
//
//    /**
//     * @param mixed $image
//     */
//    public function setImage($image): void
//    {
//        $this->person->setImage($image);
//    }
//
//    /**
//     * @return bool
//     */
//    public function isAbstract(): bool
//    {
//        return $this->person->getIsAbstract();
//    }
//
//    /**
//     * @param bool $isAbstract
//     */
//    public function setIsAbstract(bool $isAbstract): void
//    {
//        $this->isAbstract = $isAbstract;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFather()
//    {
//        return $this->person->getFather();
//    }
//
//    /**
//     * @param mixed $father
//     */
//    public function setFather($father): void
//    {
//        $this->father = $father;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMother()
//    {
//        return $this->person->getMother();
//    }
//
//    /**
//     * @param mixed $mother
//     */
//    public function setMother($mother): void
//    {
//        $this->person->setMother($mother);
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFolk()
//    {
//        return $this->person->getFolk();
//    }
//
//    /**
//     * @param mixed $folk
//     */
//    public function setFolk($folk): void
//    {
//        $this->person->setFolk($folk);
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getLivedAtTimeOfPerson()
//    {
//        return $this->person->getLivedAtTimeOfPerson();
//    }
//
//    /**
//     * @param mixed $livedAtTimeOfPerson
//     */
//    public function setLivedAtTimeOfPerson($livedAtTimeOfPerson): void
//    {
//        $this->person->setLivedAtTimeOfPerson($livedAtTimeOfPerson);
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPersonReferences()
//    {
//        return $this->person->getPersonReferences();
//    }
//
//    /**
//     * @param mixed $personReferences
//     */
//    public function setPersonReferences($personReferences): void
//    {
//        $this->person->setPersonReferences($personReferences);
//    }
//
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
//
//    /**
//     * @return mixed
//     */
//    public function getBorn()
//    {
//        return $this->person->getBorn();
//    }
//
//    /**
//     * @param mixed $born
//     */
//    public function setBorn($born): void
//    {
//        $this->person->setBorn($born);
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDied()
//    {
//        return $this->person->getDied();
//    }
//
//    /**
//     * @param mixed $died
//     */
//    public function setDied($died): void
//    {
//        $this->person->setDied($died);
//    }


    /**
     * @return mixed
     *
    public function getBornEstimated()
    {
        return $this->bornEstimated;
    }

    /**
     * @param mixed $bornEstimated
     *
    public function setBornEstimated($bornEstimated): void
    {
        $this->bornEstimated = $bornEstimated;
    }

    /**
     * @return mixed
     *
    public function getDiedEstimated()
    {
        return $this->diedEstimated;
    }

    /**
     * @param mixed $diedEstimated
     *
    public function setDiedEstimated($diedEstimated): void
    {
        $this->diedEstimated = $diedEstimated;
    }*/

//    /**
//     * @return mixed
//     */
//    public function getAge()
//    {
//        return null;//$this->person->getDied();
//    }

}
