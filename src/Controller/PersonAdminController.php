<?php

namespace App\Controller;

use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PersonAdminController extends AbstractController
{
    /**
     * @Route("/admin/person/list", name="admin_person_list")
     */
    public function list(PersonRepository $personRepository)
    {
        $persons = $personRepository->findAll();
        return $this->render('person_admin/list.html.twig', [
            'persons' => $persons
        ]);
    }

}
