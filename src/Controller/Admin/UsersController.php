<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class UsersController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var int
     */
    const PAGE_LIMIT = 20;

    /**
     * @var string
     */
    const ADMIN_ROLE = 'ROLE_ADMIN';

    /**
     * @var string
     */
    const MANAGER_ROLE = 'ROLE_ACCEPT_CHANGES';

    /**
     * UsersController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/admin/users/list", name="admin_users_list", methods={"GET"})
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $q = $request->query->get('q');
        $queryBuilder = $this->userRepository->getWithSearchQueryBuilder($this->getUser(), $q);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1)/*page number*/,
            self::PAGE_LIMIT/*limit per page*/
        );

        return $this->render('admin_users/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/admin/users/{id}/edit", name="admin_users_edit", methods={"GET"})
     */
    public function edit(Request $request, $id)
    {
        $user = $this->userRepository->find($id);
        return $this->render('admin_users/edit.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/admin/users/{id}", name="admin_user_update", methods={"PATCH"})
     */
    public function update(Request $request, $id)
    {
        $newRoles = [
            'admin'   => $request->get('admin'),
            'manager' => $request->get('manager')
        ];

        $existsRoles = [
            'admin'   => self::ADMIN_ROLE,
            'manager' => self::MANAGER_ROLE
        ];

        $rolesToFlush = [];

        foreach ($newRoles as $role => $roleValue) {
            if ($roleValue == 1) {
                array_push($rolesToFlush, $existsRoles[$role]);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->userRepository->find($id);
        $user->setRoles($rolesToFlush);
        $entityManager->flush();

        return $this->redirectToRoute('admin_users_edit', [
            'id' => $id,
        ]);
    }
}
