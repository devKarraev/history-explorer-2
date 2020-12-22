<?php

namespace App\Entity;

use App\Repository\ReferenceRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;

/**
 * @ORM\Entity(repositoryClass=ReferenceRepository::class)
  */
class Reference
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
    private $url ="";

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $chapter;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $verse;

    /**
     * @ORM\ManyToOne(targetEntity=BibleBooks::class, inversedBy="entityReferences")
     */
    private $book;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isBibleRef;


    private $persons = [];
    private $locations = [];
    private $folks = [];
    private $events = [];
    private $checked = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getChapter(): ?int
    {
        return $this->chapter;
    }

    public function setChapter(?int $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getVerse(): ?int
    {
        return $this->verse;
    }

    public function setVerse(?int $verse): self
    {
        $this->verse = $verse;

        return $this;
    }

    public function __toString()
    {
        //return $this->getBook() . $this->getChapter() .','.$this->getVerse();
        return $this->url;
    }

    public function generatUrl() {
        if($this->getIsBibleRef()) {
            $this->url = $this->getBook().' '.$this->getChapter().','.$this->getVerse();
        }
    }

    public function generateBibleServerUrl() : ? string
    {

        if($this->getIsBibleRef()) {
            return 'https://www.bibleserver.com/LUT/'. $this->url;
        }
        //$this->url = 'https://www.bibleserver.com/LUT/' . strval($this);
        return $this->url;
    }

    public function getBook(): ?BibleBooks
    {
        return $this->book;
    }

    public function setBook(?BibleBooks $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getIsBibleRef(): ?bool
    {
        return $this->isBibleRef;
    }

    public function setIsBibleRef(bool $isBibleRef): self
    {
        $this->isBibleRef = $isBibleRef;

        return $this;
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
