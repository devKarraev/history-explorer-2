<?php

namespace App\Controller;

use App\Entity\EntityChange;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EntityChangeController extends AbstractController
{
    /**
     * @Route ("/change/{id}/deny", name="change_deny")
     * @IsGranted("ROLE_EDIT_ENTITY", subject="change")
     */
    public function denyChange(EntityChange $change, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if(in_array('ROLE_ACCEPT_CHANGES', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles())
        || $user === $change->getChangedBy() ) {
            $l = $change->getLocation();
            $e = $change->getEvent();
            if($l) $em->remove($l);
            if($e) $em->remove($e);
            $em->remove($change);
            $em->flush();
        }

        return $this->redirectToRoute('persons_list', [

        ]);

    }

    /**
     * @Route ("/change/{id}/accept", name="change_accept")
     * @IsGranted("ROLE_ACCEPT_CHANGES", subject="change")
     */
    public function acceptChange(EntityChange $change, EntityManagerInterface $em)
    {

        if($change->getModificationType() == 'new') {
            $p = $change->getPerson();
            $l = $change->getLocation();
            $e = $change->getEvent();
            if($p) {
                $p->setApproved(true);
                $em->persist($p);
            }
            if($l) {
                $l->setApproved(true);
                $em->persist($l);
            }
            if($e) {
                $e->setApproved(true);
                $em->persist($e);
            }
        }
        else {
            $up = $change->getPerson();
            $p= $change->getUpdatedPerson();
           // dd($up);
            if($p && $up) {
               $up->updateFromChange($em, $p);
               $up->setApproved(true);
               $em->persist($up);
               $em->remove($p);
            }
        }

        $em->remove($change);



                //  $p->removeChange($change);
                //    $person->removeChange($change);
                // $em->persist($p);


        $em->flush();


        return $this->redirectToRoute('persons_list', [

        ]);

    }
}
