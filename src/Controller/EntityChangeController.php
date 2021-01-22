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
    public function denyChange(EntityChange $change, EntityManagerInterface $em, Request $request)
    {
        $user = $this->getUser();
        if(in_array('ROLE_ACCEPT_CHANGES', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles())
        || $user === $change->getChangedBy() ) {
            $l = $change->getLocation();
            if($l) $em->remove($l);
            $em->remove($change);
            $em->flush();
        }

        $redirectRoute = $request->get('redirectRoute') === null ? 'persons_list': $request->get('redirectRoute');

        return $this->redirectToRoute($redirectRoute, [

        ]);

    }

    /**
     * @Route ("/change/{id}/accept", name="change_accept")
     * @IsGranted("ROLE_ACCEPT_CHANGES", subject="change")
     */
    public function acceptChange(EntityChange $change, EntityManagerInterface $em, Request $request)
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
            $e = $change->getUpdatedEvent();
            $ue = $change->getEvent();

           // dd($up);
            if($p && $up) {
               $up->updateFromChange($em, $p);
               $up->setApproved(true);
               $em->persist($up);
               $em->remove($p);
            }

            if($e && $ue) {
                $ue->updateFromChange($em, $e);
                $ue->setApproved(true);
                $em->persist($ue);
                $em->remove($e);
            }
        }

        $em->remove($change);



                //  $p->removeChange($change);
                //    $person->removeChange($change);
                // $em->persist($p);


        $em->flush();

        $redirectRoute = $request->get('redirectRoute') === null ? 'persons_list': $request->get('redirectRoute');

        return $this->redirectToRoute($redirectRoute, [

        ]);

    }
}
