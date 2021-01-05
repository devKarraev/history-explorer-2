<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Form\PersonFormType;
use App\Repository\PersonRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @IsGranted("ROLE_ACCEPT_CHANGES")
 */
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
            $paginationData = $this->preparePaginator($queryBuilder->getQuery()->getResult());

            $pagination = $paginator->paginate(
                $paginationData,
                $request->query->getInt('page', 1)/*page number*/,
                self::PAGE_LIMIT/*limit per page*/
            );
        }

        return $this->render('admin_changes/list.html.twig', [
            'user' => $user,
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route ("/admin/changes/{id}/edit", name="admin_changes_edit")
     */
    public function edit(Request $request, Person $person): Response
    {
        $form = $this->createForm(PersonFormType::class, $person,
            [ /* 'include_x' => true*/
                'user' => $this->getUser(),
            ]
        );

        $addChildren = [];
        $q = $request->query->get('q');
        if(strlen($q) > 0) {
            $addChildren = $this->personRepository->findAllPossibleChildren($this->getUser(), $person, $q);
        }

        return $this->render(
            'admin_changes/edit.html.twig', [
            'personForm' => $form->createView(),
            'person' => $person,
            'addchildren'=> $addChildren,
        ]);
    }

    /**
     * Prepare paginator data for rendering.
     *
     * @param array $pagination
     *
     * @return array
     */
    private function preparePaginator(array $pagination): array
    {
        $resultArray = [];
        foreach ($pagination as $entity) {
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

        return $resultArray;
    }
}
