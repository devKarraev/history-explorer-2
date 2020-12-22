<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminUsersController extends AbstractController
{
    /**
     * @Route("/admin/users", name="admin_users", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_homepage');
        }

        $usersList = $userRepository->findByNot('id', $this->getUser()->getId());
        return $this->render('admin_users/index.html.twig', [
            'controller_name' => 'AdminUsersController',
            'users_list' => $usersList
        ]);
    }

    /**
     * @Route("/admin/users/edit/{id}", name="admin_users_edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        dd($request->get('id'));
    }

    /**
     * @Route("/admin/users/update-roles", name="users_update_roles", methods={"POST"})
     */
    public function updateRoles(Request $request)
    {
        dd($request);
    }
}
