<?php


namespace App\Controller;


use App\Repository\BibleBooksRepository;
use App\Repository\ChapterVersesRepository;
use App\Repository\ReferenceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BibleBooksUtilityController extends AbstractController
{
    /**
     * @var BibleBooksRepository
     */
    private $bibleBooksRepository;

    public function __construct(BibleBooksRepository $bibleBooksRepository)
    {
        $this->bibleBooksRepository = $bibleBooksRepository;
    }

    /**
     * @Route("/references/utility/bible_books", methods="GET", name="books_utility")
     *
     */
    public function getReferencesApi(Request $request)
    {
        $term = $request->query->get('query');

        $result = $this->checkIsValid($term);


       // dd("not OK");

        return $this->json([
            'books' => $result['books'],
            'book' => $result['book'],
            'error' => $result['error'],
            'fullReference' => $result['fullReference'],

        ], 200, [], [
                'groups' => ['main']
            ]
        );
    }

    public function checkIsValid($term) {
        $result = [];
        $book = "";
        $fullReference = "";
        $error = "";
        $bibleBooks = null;
        if($this->is_url($term)) {
            $fullReference = $term;
        } else {

            if (preg_match('/(\d*\D+)(\d*)[,.\s]*(\d*)/', $term, $matches)) {
                $name = str_replace(" ", "", $matches[1]);
                $bibleBooks = $this->bibleBooksRepository->findByName($name);

                if (sizeof($bibleBooks) == 1 && $matches[2] !== "" && $matches[3] != "") {
                    $chapter = $matches[2];

                    $book = $bibleBooks[0]->getName();
                    $chapterVerses = ($bibleBooks[0]->getChapterVersesArray());
                    if ($chapter <= sizeof($chapterVerses)) {
                        $verse = $matches[3];
                        if ($verse <= $chapterVerses[$chapter]) {
                            $fullReference = $bibleBooks[0]->getName().' '.$chapter.','.$verse;
                        } else {
                            $error = 'verse not valid';
                        }
                    } else {
                        $error = 'chapter not valid';
                    }
                }

            } else {

            }
        }
        $result['books'] = $bibleBooks;
        $result['book'] = $book;
        $result['fullReference'] = $fullReference;
        $result['error'] = $error;
        return $result;
    }
    private function is_url($uri){
        if(preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$uri)){
            return $uri;
        }
        else{
            return false;
        }
    }

    /**
     * @Route("/references/utility/verses", methods="GET", name="books_utility_chapters")
     *
     */
    public function getChaptersApi(ChapterVersesRepository $chapterVersesRepository, Request $request)
    {
        $bookId = $request->query->get('query');

        $chapters = $chapterVersesRepository->findBy();

        return $this->json([
            'verses' => $chapters
        ], 200, []
        );
    }



}