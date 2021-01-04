<?php

namespace App\Controller\Admin;

use App\Repository\PersonRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\Routing\Annotation\Route;

class ChangesController extends AbstractController
{
    /**
     * @var int
     */
    const PAGE_LIMIT = 20;

    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * ChangesController constructor.
     *
     * @param PersonRepository $personRepository
     */
    public function __construct(
        PersonRepository $personRepository
    ) {
        $this->personRepository = $personRepository;
    }

    /**
     * @Route("/admin/changes/list", name="admin_changes_list", methods={"GET"})
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $pagination = null;

        if (count($this->getDoctrine()->getRepository(\App\Entity\EntityChange::class)->findAll()) !== 0) {
            $q = $request->query->get('q');
            $queryBuilder = $this->personRepository->getForAdminChangesList($q);

            $pagination = $paginator->paginate(
                $queryBuilder,
                $request->query->getInt('page', 1)/*page number*/,
                self::PAGE_LIMIT/*limit per page*/
            );
            $pagination = $this->preparePaginator($pagination);
        }

        return $this->render('admin_changes/index.html.twig', [
            'user' => $user,
            'pagination' => $pagination
        ]);
    }

    /**
     * Prepare paginator data for rendering.
     *
     * @param SlidingPagination $pagination
     *
     * @return SlidingPagination
     */
    private function preparePaginator(SlidingPagination $pagination): SlidingPagination
    {
        $paginationEntities = $pagination->getItems();
        $resultArray = [];
        foreach ($paginationEntities as $entity) {
            if ($entity->getUpdateOf() != null) {
                $rootId = $entity->getUpdateOf()->getPerson()->getId();
            } else {
                $rootId = $entity->getId();
            }

            if (key_exists($rootId, $resultArray) === false) {
                $resultArray[$rootId] = [$entity];
            } else {
                array_push($resultArray[$rootId] , $entity);
            }
        }
        $pagination->setItems($resultArray);

        return $pagination;
    }
}
