<?php


namespace App\Form\Model;


use App\Entity\Reference;
use App\Repository\ReferenceRepository;

class ReferenceCollection extends Reference
{
    private $persons = [];
    private $locations = [];
    private $folks = [];
    private $events = [];
    private $checked = false;
    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;

    public function __construct(Reference $reference, ReferenceRepository $referenceRepository)
    {
        $objValues = get_object_vars($reference); // return array of object values
        foreach($objValues as $key=>$value)
        {
            $this->$key = $value;
        }
        $this->referenceRepository = $referenceRepository;
        $this->persons = $referenceRepository->getReferencedPersons($reference);

    }

    /**
     * @return array
     */
    public function getPersons(): array
    {
        return $this->persons;
    }

    /**
     * @param array $persons
     */
    public function setPersons(array $persons): void
    {
        $this->persons = $persons;
    }

    /**
     * @return array
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param array $locations
     */
    public function setLocations(array $locations): void
    {
        $this->locations = $locations;
    }

    /**
     * @return array
     */
    public function getFolks(): array
    {
        return $this->folks;
    }

    /**
     * @param array $folks
     */
    public function setFolks(array $folks): void
    {
        $this->folks = $folks;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param array $events
     */
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     */
    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
    }
}