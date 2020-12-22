<?php

namespace App\Controller;

use App\Entity\Reference;
use App\Form\Model\ReferenceCollection;
use App\Repository\BibleBooksRepository;
use App\Repository\ReferenceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReferenceController extends BaseController
{
    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;
    /**
     * @var BibleBooksRepository
     */
    private $bibleBooksRepository;

    public function __construct(BibleBooksRepository $bibleBooksRepository, ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
        $this->bibleBooksRepository = $bibleBooksRepository;
    }

    /**
     * @Route("/references", name="reference_list")
     */
    public function list(Request $request, PaginatorInterface $paginator)
    {
        $sort = ['field' => 'r.id', 'direction' => 'asc'];

        $q = $request->query->get('q');

        $groupedReferences = [];

        $references = $this->referenceRepository->getAllReferenced();

        /** @var  Reference $reference */
        foreach ($references as $reference)
        {
            $b = $reference->getBook();
            $c = $reference->getChapter();
            if($b != null && $reference->getIsBibleRef()){

                $p = $this->referenceRepository->getReferencedPersons($reference);
                if($p)
                    $reference->setPersons($p);
                if(!array_key_exists($b->getName(), $groupedReferences)) {
                    $groupedReferences[$b->getName()] = [];
                }
                $groupedReferences[$b->getName()][$c][] = $reference;
            }
        }

        return $this->render(
            'reference/list.html.twig', [
            'groupedReferences' => $groupedReferences,
        ]);
    }
}
