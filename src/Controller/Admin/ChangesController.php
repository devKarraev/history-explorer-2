<?php

namespace App\Controller\Admin;

use App\Logic\AdminChanges;
use App\Repository\EventRepository;
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
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var AdminChanges
     */
    private $adminChanges;

    /**
     * ChangesController constructor.
     *
     * @param AdminChanges $adminChanges
     * @param PersonRepository $personRepository
     * @param EventRepository $eventRepository
     */
    public function __construct(
        AdminChanges $adminChanges,
        PersonRepository $personRepository,
        EventRepository $eventRepository
    )
    {
        $this->adminChanges = $adminChanges;
        $this->eventRepository = $eventRepository;
        $this->personRepository = $personRepository;
    }

    /**
     * @Route("/admin/changes/list", name="admin_changes_list", methods={"GET"})
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $personPagination = $this->personRepository->getPaginatedChanges($paginator, $request);
        $eventPagination = $this->eventRepository->getPaginatedChanges($paginator, $request);

        return $this->render('admin_changes/list.html.twig', [
            'user' => $user,
            'personPagination' => $personPagination,
            'eventPagination' => $eventPagination
        ]);
    }

    /**
     * @Route ("/admin/changes/{id}/edit/{type}", name="admin_changes_edit", requirements={"type"="event|person"})
     */
    public function edit(Request $request): Response
    {
        $entity = $this->adminChanges->getEntityOfType($request->get('id'), $request->get('type'));
        $form = $this->adminChanges->getFormType($request->get('type'), $entity, $this->getUser());
        $templateView = $this->adminChanges->getTemplateView($request->get('type'));

        $templateOptions = [
            'form' => $form->createView(),
        ];

        $templateOptions = $this->adminChanges->setTemplateOptions($request->get('type'), $templateOptions, $request->query->get('q'), $this->getUser(), $entity);
        return $this->render($templateView, $templateOptions);
    }
}
