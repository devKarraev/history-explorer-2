<?php

namespace App\Entity;

use App\Repository\BibleBooksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=BibleBooksRepository::class)
 */
class BibleBooks
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups("main")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $alternateName;

    /**
     * @ORM\Column(type="smallint")
     * @Groups("main")
     */
    private $chapters;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fullName;

    /**
     * @ORM\OneToMany(targetEntity=Reference::class, mappedBy="book")
     */
    private $entityReferences;

    /**
     * @ORM\OneToMany(targetEntity=ChapterVerses::class, mappedBy="book", orphanRemoval=true)
     * @Groups("main")
     */
    private $chapterVerses;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $fromYear;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $toYear;

    public function __construct()
    {
        $this->entityReferences = new ArrayCollection();
        $this->chapterVerses = new ArrayCollection();
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

    public function getAlternateName(): ?string
    {
        return $this->alternateName;
    }

    public function setAlternateName(?string $alternateName): self
    {
        $this->alternateName = $alternateName;

        return $this;
    }

    public function getChapters(): ?int
    {
        return $this->chapters;
    }

    public function setChapters(int $chapters): self
    {
        $this->chapters = $chapters;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return Collection|Reference[]
     */
    public function getEntityReferences(): Collection
    {
        return $this->entityReferences;
    }

    public function addEntityReference(Reference $entityReference): self
    {
        if (!$this->entityReferences->contains($entityReference)) {
            $this->entityReferences[] = $entityReference;
            $entityReference->setBook($this);
        }

        return $this;
    }

    public function removeEntityReference(Reference $entityReference): self
    {
        if ($this->entityReferences->contains($entityReference)) {
            $this->entityReferences->removeElement($entityReference);
            // set the owning side to null (unless already changed)
            if ($entityReference->getBook() === $this) {
                $entityReference->setBook(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return Collection|ChapterVerses[]
     */
    public function getChapterVerses(): Collection
    {
        return $this->chapterVerses;
    }

    /**
     * @return array
     */
    public function getChapterVersesArray(): array
    {
        $verses = [];
        /** @var ChapterVerses $chapterVers */
        foreach ($this->chapterVerses as $chapterVers) {
            $verses[$chapterVers->getChapter()] = $chapterVers->getVerses();
        }
        return $verses;
    }

    public function addChapterVerse(ChapterVerses $chapterVerse): self
    {
        if (!$this->chapterVerses->contains($chapterVerse)) {
            $this->chapterVerses[] = $chapterVerse;
            $chapterVerse->setBook($this);
        }

        return $this;
    }

    public function removeChapterVerse(ChapterVerses $chapterVerse): self
    {
        if ($this->chapterVerses->contains($chapterVerse)) {
            $this->chapterVerses->removeElement($chapterVerse);
            // set the owning side to null (unless already changed)
            if ($chapterVerse->getBook() === $this) {
                $chapterVerse->setBook(null);
            }
        }

        return $this;
    }

    public function getFromYear(): ?int
    {
        return $this->fromYear;
    }

    public function setFromYear(?int $fromYear): self
    {
        $this->fromYear = $fromYear;

        return $this;
    }

    public function getToYear(): ?int
    {
        return $this->toYear;
    }

    public function setToYear(?int $toYear): self
    {
        $this->toYear = $toYear;

        return $this;
    }
}
