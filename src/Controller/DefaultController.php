<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Entity\Person;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     * 
     */
    public function indexAction()
    {
        return $this->render('index.html.twig');
    }
}
