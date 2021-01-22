<?php

namespace App\Controller;

use App\Entity\EntityChange;
use App\Entity\Event;
use App\Entity\EventReference;
use App\Entity\Person;
use App\Entity\Reference;
use App\Entity\User;
use App\Form\EventFormType;
use App\Repository\BibleBooksRepository;
use App\Repository\EventRepository;
use App\Repository\PersonRepository;
use App\Service\UploaderHelper;
use App\Validator\UncertainNumberValidator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventController extends BaseController
{

    /**
     * @var EventRepository
     */
    private $eventRepository;
    /**
     * @var PersonRepository
     */
    private $personRepository;
    /**
     * @var BibleBooksRepository
     */
    private $bibleBooksRepository;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(EventRepository $eventRepository,
        PersonRepository $personRepository,
        BibleBooksRepository $bibleBooksRepository,
        RouterInterface $router)
    {
        $this->eventRepository = $eventRepository;
        $this->personRepository = $personRepository;
        $this->bibleBooksRepository = $bibleBooksRepository;
        $this->router = $router;
    }

    /**
     * @Route("/events", name="event_list")
     */
    public function list(Request $request, PaginatorInterface $paginator)
    {
        $sort = ['field' => 'e.orderedIndex', 'direction' => 'asc'];

        $q = $request->query->get('q');
        $queryBuilder = $this->eventRepository->getWithSearchQueryBuilder($q);

        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/, [
                'defaultSortFieldName' => $sort['field'],
                'defaultSortDirection' => $sort['direction'],
            ]
        );

        return $this->render(
            'event/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route ("/event/new", name ="add_event")
     * @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function new(EntityManagerInterface $em, Request $request, UploaderHelper $uploaderHelper, UncertainNumberValidator $uncertainNumberValidator, ValidatorInterface $validator)
    {
        $eventBefore = $request->query->get('before');
        $eventAfter = $request->query->get('after');
        $eventTime = $request->query->get('time');

        $after = $before = null;
        if($eventBefore) {
            $before = $this->eventRepository->findOneBy(['id' => $eventBefore]);
            $after = $before->getHappenedAfter();
        } else if($eventAfter) {
            $after = $this->eventRepository->findOneBy(['id' => $eventAfter]);
            $before = $after->getHappenedBefore();
        }

        $form = $this->createForm(EventFormType::class, null, [
            'before' => $before, 'after' => $after
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            /** @var Event $event */
            $event = $form->getData();

            $timeUncertain = $form['uncertainTime']->getData();
            $this->setTime($event, $timeUncertain, $uncertainNumberValidator);

            /** @var UploadedFile $uploadedFile */
            $uploadedFile =  $form['imageFile']->getData();
            if ($uploadedFile) {
                $violations = $validator->validate(
                    $uploadedFile, [
                    /* new NotBlank(
                            ['Please select a File']
                        ) ,*/
                        new File([
                            'maxSize' => '2M',
                            'mimeTypes' => [
                                'image/*',
                            ],
                        ]),
                    ]
                );
                if($violations->count()>0) {
                    /** @var ConstraintViolation $violation */
                    $violation = $violations[0];
                    $this->addFlash('error', $violation->getMessage());
                    return $this->redirectToRoute('add_event', [

                    ]);
                }
                $newFilename = $uploaderHelper->uploadEventImage($uploadedFile, null);
                $event->setImage($newFilename);
            }

            $event->setOwner($this->getUser());

            $this->getUser()->countChange(User::EVENT);

            $em->persist($this->getUser());
            $em->persist($event);

            if(!$this->setOrder($event, $em, false)) {
                $this->addFlash('error', 'Violation in event order.');
                return $this->redirectToRoute(
                    'add_event',
                    [
                    ]
                );
            }

            $em->flush();

            $this->reorderEvents($em);

            $this->addFlash('success', 'Event created');

            return $this->redirectToRoute('event_edit', [
                'id' => $event->getId(),
            ]);
        }
        return $this->render('event/new.html.twig', [
            'eventForm' => $form->createView(),

        ]);
    }



    /**
     * @Route("event/next-event-select", name="next_event_select")
     */
    public function getNextEvent(Request $request)
    {
        $e = $this->eventRepository->findOneBy(['id' => $request->query->get('event')]);
        $c = $this->eventRepository->findOneBy(['id' => $request->query->get('current')]);
        if(!$e) {
            return new Response(null, 204);
        }

        $next = $e->getHappenedBefore();
        if($next && $c && $next->getId() === $c->getId())
            $next = $next->getHappenedBefore();
        if(!$next) {
            $next = $e;
        }

        return $this->json([
            'id' => $next->getId(),
        ], 200, []
        );

       /* $event = new Event();
        $event->setHappenedAfter($prev);

        $form = $this->createForm(EventFormType::class, $event);
        // no field ? Return empty response
        if(!$form->has('happenedAfter')) {
            return new Response(null, 204);
        }
        return $this->render('event/prev_event.html.twig', [
                'eventForm' => $form->createView()]
        );*/
    }
    /**
     * @Route("event/prev-event-select", name="prev_event_select")
     */
    public function getPrevEvent(Request $request)
    {
        $e = $this->eventRepository->findOneBy(['id' => $request->query->get('event')]);
        $c = $this->eventRepository->findOneBy(['id' => $request->query->get('current')]);

        if(!$e) {
            return new Response(null, 204);
        }

        $prev = $e->getHappenedAfter();
        if($prev && $c && $prev->getId() === $c->getId())
            $prev = $prev->getHappenedAfter();
        if(!$prev) {
           $prev = $e;
        }

        return $this->json([
            'id' => $prev->getId(),
        ], 200, []
        );

        /*$event = new Event();
        $event->setHappenedBefore($prev);

        $form = $this->createForm(EventFormType::class, $event);
        // no field ? Return empty response
        if(!$form->has('happenedBefore')) {
            return new Response(null, 204);
        }
        return $this->render('event/prev_event.html.twig', [
                'eventForm' => $form->createView()]
        );*/
    }

    /**
     * @Route ("/event/reorder", name="event_reorder")
     * @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function reorder(EntityManagerInterface $em) {
        $this->reorderEvents($em);
        dd();
    }
    private function reorderEvents(EntityManager $em) {
        /** @var Event $event*/

        $event = $this->eventRepository->findOneBy(['happenedAfter' => null]);
        $order = 0;
        $prevEventTime = null;
        while($event)
        {
            echo "<b>".$event->getName()."</b><br>";
            $event->setOrderedIndex($order++);
            $prevEventTime = $event->guessTime($prevEventTime);
            $em->persist($event);
            $em->flush();
           // dump($event);
            $event = $event->getHappenedBefore();
        }
       // $em->flush();
    }

    /**
     * @Route ("/event/{id}/edit", name="event_edit")
     * @IsGranted("ROLE_EDIT_ENTITY", subject="event")
     */

    public function edit(Event $event, Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper,
        UncertainNumberValidator $uncertainNumberValidator, ValidatorInterface $validator)
    {
        // It is necessary so that the original record doesn't change.
        if ($request->getMethod() === 'POST') {
            $eventClone = clone $this->eventRepository->find($request->get('id'));
            $form = $this->createForm(EventFormType::class, $eventClone, [

            ]);
        } else {
            $form = $this->createForm(EventFormType::class, $event, [

            ]);
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if($uploadedFile) {

                $violations = $validator->validate(
                    $uploadedFile, [
                        /* new NotBlank(
                                ['Please select a File']
                            ) ,*/
                        new File([
                            'maxSize' => '2M',
                            'mimeTypes' => [
                                'image/*',
                            ],
                        ]),
                    ]
                );
                if($violations->count()>0) {
                    /** @var ConstraintViolation $violation */
                    $violation = $violations[0];
                    $this->addFlash('error', $violation->getMessage());
                    return $this->redirectToRoute('add_event', [

                    ]);
                }
                $newFilename = $uploaderHelper->uploadEventImage($uploadedFile, $event->getImage());
                $event->setImage($newFilename);
            }

            $timeUncertain = $form['uncertainTime']->getData();
            $this->setTime($event, $timeUncertain, $uncertainNumberValidator);

            if(!$this->setOrder($event, $em, true)) {
                $this->addFlash('error', 'Violation in event order.');
            }
            else {

                $user = $this->getUser();
                $change = new EntityChange();
                $change->setEvent($eventClone)->setModificationType("edit")->setChangedBy($user);
                $em->persist($change);
                $event->addChange($change);
                $em->persist($change);

                // create a clone
                $eventClone->setUpdateOf($change);
                $eventClone->setApproved(false);
                $eventClone->setOwner($user);
                if ($event->getHappenedAfter() === null && $event->getId() === $eventClone->getHappenedAfter()->getId()) {
                    $eventClone->setHappenedAfter(null);
                }
                $em->persist($eventClone);

                $em->flush();

                $em->persist($event);
                $em->flush();
                $this->reorderEvents($em);
                $this->addFlash('success', 'Event updated');
            }
            return $this->redirectToRoute('event_edit', [
                'id' => $event->getId(),
            ]);
        }

        $addPersons = [];
        $q = $request->query->get('q');
        //if(strlen($q) > 2) {
        $addPersons = $this->personRepository->findAllPossibleEventPeople($this->getUser(), $event, $q, 100);
        //$p =  $this->personRepository->findOneBy(['name' => $q]);
       // dd($p);

        return $this->render(
            'event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'addpersons'=> $addPersons,
        ]);

    }

    private function setTime(Event& $event, $timeUncertain, UncertainNumberValidator $uncertainNumberValidator ) {

        if($timeUncertain) {

            $time = $uncertainNumberValidator->getValue($timeUncertain);

            if($uncertainNumberValidator->isUncertain($timeUncertain)) {
                $event->setYearEstimated($time);
                $event->setYear(null);
                $isTimeUncertain = true;
            } else {
                $event->setYear($time);
                $event->setYearEstimated($time);
            }
        } else {
            $event->setYear(null);
            $event->setUncertainTime(null);
        }
    }
    /**
     * @Route("/event/{id}", name="event_show")
     */
    public function show(Event $event)
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @Route("/event/remove_reference/{event}/{id}", name="remove_event_reference")
     *  @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function removeReference(Event $event, EventReference $eventReference)
    {

        $event->removePersonReference($eventReference);

        $em = $this->getDoctrine()->getManager();
        $em->remove($eventReference);
        $em->persist($event);
        $em->flush();
        return $this->redirectToRoute('event_edit', [
            'id' => $event->getId(),
        ]);
    }
    // TODO: move this and person add refernce to ReferenceController?
    /**
     * @Route("/event/add_reference/{event}", name="add_event_reference")
     * @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function addEventReferenceAction(BibleBooksUtilityController $bibleBooksUtilityController, Request $request, Event $event)
    {
        $type = $request->request->get('reftype');
        $term = $request->request->get('submit_param');

        $valid = $bibleBooksUtilityController->checkIsValid($term);
        if($valid['fullReference']!="") {

            $em = $this->getDoctrine()->getManager();

            $reference = $em->getRepository("App:Reference")->findOneBy([
                'url' => $valid['fullReference'],
            ]);
            if($reference == null) {
                $reference = new Reference();
                $reference->setUrl($valid['fullReference']);
                $reference->setIsBibleRef($valid['book']!="");
                $em->persist($reference);
            }

            /** @var EventReference $eventReference */
            $eventReference = new EventReference();
            $eventReference->setType($type);
            $eventReference->setEvent($event);
            $eventReference->setReference($reference);
            $em->persist($eventReference);

            $event->addEventReference($eventReference);

            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Reference added');
        }else {
            $this->addFlash('error', 'Data not valid');
        }

        return $this->redirectToRoute('event_edit', [
            'id' => $event->getId(),
        ]);
    }

    /**
     * @Route("/event/add_participant/{event}/{participant}", name="event_add_participant")
     */
    public function addParticipantAction(Event $event, Person $participant)
    {
        $participant->addEvent($event);
        $em = $this->getDoctrine()->getManager();
        $em->persist($participant);
        $em->flush();

        $this->addFlash('success', 'Participant added');
        return $this->redirectToRoute('event_edit', [
            'id' => $event->getId(),
        ]);
    }

    /**
     * @Route("/event/remove_participant/{event}/{participant}", name="event_remove_participant")
     */
    public function removeParticipantAction(Event $event, Person $participant)
    {
        $participant->removeEvent($event);
        $em = $this->getDoctrine()->getManager();
        $em->persist($participant);
        $em->flush();

        $this->addFlash('success', 'Participant removed');
        return $this->redirectToRoute('event_edit', [
            'id' => $event->getId(),
        ]);
    }

    /**
     * @Route("/timeline", name="timeline")
     */
    public function timeline()
    {

        $books = $this->bibleBooksRepository->getJSONNodes();
        $events = $this->eventRepository->getOrdered($this->getUser());
        $nodes = [];
        $links = [];
        $lastPersonEventId = [];
        $birthNodes = [];
        $deathNodes = [];

        $nodesImages = [];
        /** @var Event $event */
        foreach ($events as $event) {

            $nodesImages[$event->getId()] = $event->getImage();
            $nodes[] = (object) ['id'=> $event->getId(), 'type'=> 'event', 'name' => $event->getName(),
                't' => $event->getYearCalculated(),  'order' => $event->getOrderedIndex()];

            foreach ($event->getParticipants() as $person) {

                if (!isset($lastPersonEventId[$person->getId()])) {

                    $birthNode = (object)[
                        'id' => $person->getId() + 10000,
                        'type' => 'birth',
                        'name' => '* '.$person->getShortName(),
                        't' => $person->getBornCalculated(),
                        'order' => 10000
                    ];
                    $deathNode = (object)[
                        'id' => $person->getId() + 20000,
                        'type' => 'death',
                        'name' => '† '.$person->getShortName(),
                        't' => $person->getDiedCalculated() ?: $person->getBornCalculated() + 80,
                        'order' => 20000
                    ];

                    $nodesImages[$person->getId() + 10000] = $person->getImage();

                    $birthNodes[$person->getId()] = $birthNode;//
                    $deathNodes[$person->getId()] = $deathNode;//
                    //
                    $lastPersonEventId[$person->getId()]['id'] = $birthNode->id;
                    $lastPersonEventId[$person->getId()]['name'] = $person->getShortName();
                } else {

                    $birthNodes[$person->getId()]->t = min(
                        $birthNodes[$person->getId()]->t,
                        $event->getYearCalculated()
                    );
                    $deathNodes[$person->getId()]->t = max(
                        $deathNodes[$person->getId()]->t,
                        $event->getYearCalculated()
                    );
                }

                $links[] = (object)[
                    'source' => $lastPersonEventId[$person->getId()]['id'],
                    'target' => $event->getId(),
                    'name' => $person->getShortName(),
                    'personId' => $person->getId()
                ];
                $lastPersonEventId[$person->getId()]['id'] = $event->getId();
            }//person
        } // event

        foreach($lastPersonEventId as $key => $event) {
            $links[] = (object) ['source' => $event['id'], 'target' => $deathNodes[$key]->id, 'name'=> $event['name'], 'personId' => $key ];
        }


        $nodes = array_merge($nodes, $birthNodes, $deathNodes);

// sort nodes
        usort($nodes, function($a, $b){
            return ($a->t < $b->t) ? -1 : ($a->t > $b->t ? 1 : ($a->order < $b->order ? -1 : 1));
        });

        return $this->render('event/timeline.html.twig', [
            'nodes' => json_encode($nodes),
            'links'=>json_encode($links),
            'books'=>$books,
            'images' => $nodesImages,
            'event_info_url' => $this->router->generate('event_utility_info'),
            'person_info_url' => $this->router->generate('person_utility_info'),
        ]);
    }

    /**
     * @Route("/timemap", name="timemap")
     */
    public function timemap()
    {

        $events = $this->eventRepository->getOrdered($this->getUser());

        $personImages = [];
        $eventImages = [];
        $eventStruct = [];
        $personStruct = [];
        $persons =[];
        /** @var Event $event */
        foreach ($events as $event) {
            $nodesImages[$event->getId()] = $event->getImage();

            $pid = [];
           // if($event->getLocation()) {

            //}

            $entry = null;
            $l = $event->getLocation();
            if($l) {

                $entry = (object)[
                    't' => $event->getYearCalculated(),
                    'c' => [+$l->getLat(), +$l->getLon()]
                ];

             //   echo '****'.$event->getName()."<br>";
                foreach ($event->getParticipants() as $p) {
                    $pid[] = $p->getId();
                    if (!isset($personStruct[$p->getId()])) {

                        $personStruct[$p->getId()] = (object)[
                            'name' => $p->getShortName(),
                            'gender' => $p->getGender(),
                            'id' => $p->getId(),
                            'data' => []
                        ];
                        // insert birth
                        $personImages[$p->getId()] = $p->getImage();
                        $persons[$p->getId()] = $p;

                        $birthDate = $p->getBornCalculated();
                        if($birthDate && $birthDate < $event->getYearCalculated()) {
                            $birthentry = (object)[
                                't' => $birthDate,
                                'c' => [+$l->getLat(), +$l->getLon()]
                            ];
                            $personStruct[$p->getId()]->data[] = $birthentry;
                        }
                    }
            //        echo $p->getName()."<br>";
                    $personStruct[$p->getId()]->data[] = $entry;
                }

                $e['id'] = $event->getId();
                $e["name"] = $event->getName();
                $e["t"] = $event->getYearCalculated();
                $e["c"][0] = $event->getLocation()->getLat();
                $e["c"][1] = $event->getLocation()->getLon();
                $e['p'] = $pid;
                $eventStruct[] = $e;
            }

        }

        // append death date, if eists
        foreach($personStruct as $key => $p) {
            $deathDate = $persons[$key]->getDiedCalculated();

            if($deathDate > end($p->data)->t) {
                $entry = (object)[
                    't' => $deathDate,
                    'c' => end($p->data)->c
                ];
            }
            $p->data[] = $entry;
        }

//dd($personStruct);
        return $this->render('event/timemap.html.twig', [
           // 'nodes' => json_encode($events),
            'events' => $eventStruct,
            'eventimages' => $eventImages,
            'persons' => array_values($personStruct),
            'personimages' => $personImages,
            'event_info_url' => $this->router->generate('event_utility_info'),
            'person_info_url' => $this->router->generate('person_utility_info'),
        ]);
    }

    /**
     * @Route("/event/utility/info", methods="GET", name="event_utility_info")
     *
     */
    public function getInfo(EventRepository $eventRepository, Request $request)
    {
        $id = $request->query->get('query');
        $event = $eventRepository->findOneBy(['id' => $id]);
        //

        return $this->render('event/info.html.twig', ['event' => $event]);

    }

    private function setOrder(Event& $event, EntityManager $em, bool $edit) : bool
    {
        $before = $event->getHappenedAfter();
        $after = $event->getHappenedBefore();

        if($edit) {
            if( $before === null || $before->getHappenedBefore() ==  $after->getHappenedAfter() ) {

            } else {
                dd("not ok");
                return false;
            }
        }

        else {
            if (!($after === $before && ($after->getHappenedBefore() === null || $before->getHappenedAfter(
                    ) === null))) {
                if ($after->getHappenedAfter() !== $before || $before->getHappenedBefore() !== $after) {
                    dd($after->getHappenedAfter(), $before);

                    return false;
                }
            }
        }

        if($after === $before) {
            if($after->getHappenedBefore() === null) { //last element
                $event->setHappenedBefore(null);
                $before->setHappenedBefore($event);
                $em->persist($before);
            }
            if($before->getHappenedAfter() === null) { // first element
                $event->setHappenedAfter(null);
                $after->getHappenedAfter($event);
                $em->persist($after);
            }
            $em->persist($event);
        } else {

            if ($before) {
                $before->setHappenedBefore($event);
                $em->persist($before);

            }
            if ($after) {
                $after->setHappenedAfter($event);
                $em->persist($after);
            }
            //dd($before, $after,$event);
        }
        return true;
    }
}
