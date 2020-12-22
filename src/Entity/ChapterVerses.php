<?php

namespace App\Entity;

use App\Repository\ChapterVersesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ChapterVersesRepository::class)
 */
class ChapterVerses
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=bibleBooks::class, inversedBy="chapterVerses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $book;

    /**
     * @ORM\Column(type="integer")
     */
    private $chapter;

    /**
     * @ORM\Column(type="integer")
     * @Groups("main")
     */
    private $verses;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?bibleBooks
    {
        return $this->book;
    }

    public function setBook(?bibleBooks $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getChapter(): ?int
    {
        return $this->chapter;
    }

    public function setChapter(int $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getVerses(): ?int
    {
        return $this->verses;
    }

    public function setVerses(int $verses): self
    {
        $this->verses = $verses;

        return $this;
    }
}
