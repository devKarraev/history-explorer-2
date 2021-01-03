<?php


namespace App\Controller;


use App\Entity\EntityChange;
use App\Entity\Person;
use App\Entity\PersonReference;
use App\Entity\Reference;
use App\Entity\User;
use App\Form\Model\PersonFormModel;
use App\Form\PersonFormType;
use App\Logic\Leecher;
use App\Logic\SyntaxLogic;
use App\Logic\TreeLogic;
use App\Repository\BibleBooksRepository;
use App\Repository\FolkRepository;
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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersonController extends BaseController
{
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
    /**
     * @var FolkRepository
     */
    private $folkRepository;


    public function __construct(BibleBooksRepository $bibleBooksRepository, PersonRepository $personRepository,
        FolkRepository $folkRepository, RouterInterface $router)
    {
        $this->personRepository = $personRepository;
        $this->bibleBooksRepository = $bibleBooksRepository;
        $this->router = $router;
        $this->folkRepository = $folkRepository;

    }

    /**
     * @Route("/persons", name="persons_list")
     */
    public function list(Request $request, PaginatorInterface $paginator)
    {
        $sort = ['field' => 'p.name', 'direction' => 'asc'];
        /*if (!$request->getSession()->get('sort') )
        {
            $request->getSession()->set('sort', $sort);
        }
        if(!$request->query->get('sort') && !$request->query->get('direction') )
        {
            $sort = $request->getSession()->get('sort');
        }
        else
        {
            $sort = ['field' => $request->query->get('sort'), 'direction' => $request->query->get('direction')];
            $request->getSession()->set('sort', $sort);
        }*/

        $q = $request->query->get('q');
        $queryBuilder = $this->personRepository->getWithSearchQueryBuilder($this->getUser(), $q);
//dd($queryBuilder->getQuery()->getResult());
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/, [
                'defaultSortFieldName' => $sort['field'],
                'defaultSortDirection' => $sort['direction'],
            ]
        );

        return $this->render(
            'person/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/person/test", name="person_test")
     */
    public function test() {
        dd("test");
    }


    /**
     * @Route ("/leech", name ="leech")
     * @IsGranted("ROLE_ADMIN")
     */
    public function leech(EntityManagerInterface $em, Leecher $leecher)
    {
        //$leecher->leechLocations($em);
        $leecher->leechFolks($em);
        //$leecher->leechPersons($em);
    }

    /**
     * @Route ("/person/recalc/ages", name ="recalc_ages")
     * @IsGranted("ROLE_ADMIN")
     */
    public function recalcAges(EntityManagerInterface $em, TreeLogic $treeLogic)
    {
        $treeLogic->estimateAgesAction($em, $this->getUser());
    }

    /**
     * @Route ("/person/recalc/tree", name ="recalc_tree")
     * @IsGranted("ROLE_ADMIN")
     */
    public function recalcTree(EntityManagerInterface $em, TreeLogic $treeLogic)
    {
        $treeLogic->calcLeafAction($em);
    }

    /**
     * @Route ("/person/recalc/gender", name ="recalc_gender")
     * @IsGranted("ROLE_ADMIN")
     */
    public function recalcGender(EntityManagerInterface $em, SyntaxLogic $syntaxLogic)
    {
        $syntaxLogic->genderHelper($em);
    }

    /**
     * @Route ("/person/recalc/treesyntax", name ="recalc_tree_syntax")
     * @IsGranted("ROLE_ADMIN")
     */
    public function recalcChildren(EntityManagerInterface $em, SyntaxLogic $syntaxLogic)
    {
        $syntaxLogic->childrenHelper($em);
    }

    /**
     * @Route ("/person/recalc/folks", name ="recalc_folks")
     * @IsGranted("ROLE_ADMIN")
     */
    public function recalcFolks(EntityManagerInterface $em, SyntaxLogic $syntaxLogic)
    {
        $syntaxLogic->folkHelper($em);
    }


    /**
     * @Route ("/person/new", name ="add_person")
     * @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function new(EntityManagerInterface $em, Request $request, UploaderHelper $uploaderHelper, TreeLogic $treeLogic, ValidatorInterface $validator)
    {
        $user = $this->getUser();
        $form = $this->createForm(PersonFormType::class , null, [
            'user' => $user,
        ]);


        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            /** @var Person $person */
            $person = $form->getData();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile =  $form['imageFile']->getData();
            if ($uploadedFile) {
                $violations = $validator->validate(
                    $uploadedFile, [
                        new NotBlank(
                            ['Please select a File']
                        ) ,
                        new File([
                            'maxSize' => '2M',
                            'mimeTyp es' => [
                                'image/*',
                            ],
                        ]),
                    ]
                );
                if($violations->count()>0) {
                    /** @var ConstraintViolation $violation */
                    $violation = $violations[0];
                    $this->addFlash('error', $violation->getMessage());
                    return $this->redirectToRoute('add_person', [

                    ]);
                }
                $newFilename = $uploaderHelper->uploadPersonImage($uploadedFile);
                $person->setImage($newFilename);
            }

            $person->setOwner($user);
            $user->countChange(User::PERSON);
            $em->persist($user);

            if (!in_array('ROLE_ACCEPT_CHANGES', $user->getRoles() )) {
                $change = new EntityChange();
                $change->setChangedBy($user)->setModificationType('new');//->setPerson($person);

                $person->addChange($change);
                $person->setUpdateOf($change);
                $person->setApproved(false);

                $em->persist($person);
                $em->persist($change);
            } else {
                $person->setApproved(true);
                $em->persist($person);
            }

            $em->flush();

            $treeLogic->calcLeafAction($em);
        //    $treeLogic->estimateAgesAction($em, $this->getUser());

            $this->addFlash('success', 'Person created');
            //return $this->redirectToRoute('admin_person_list');
            return $this->redirectToRoute('person_edit', [
                'id' => $person->getId(),
            ]);
        }
        return $this->render('person/new.html.twig', [
            'personForm' => $form->createView(),

        ]);
    }

    /**
     * @Route ("/person/{id}/deny", name="person_deny")
     * @IsGranted("ROLE_ACCEPT_CHANGES", subject="person")
     */
    public function deny(Person $person, EntityManagerInterface $em)
    {
        foreach ($person->getChanges() as $change) {
           if($change->getModificationType() == "new") {

               $em->remove($change);
               $person->removeChange($change);
               $em->remove($person);
               $em->flush();
               $this->addFlash('success', 'Person removed');
               return $this->redirectToRoute('persons_list', [

               ]);
           }
        }
        $this->addFlash('success', 'Person reset');
        return $this->redirectToRoute('person_edit', [
            'id' => $person->getId(),
        ]);
    }

    /**
     * @Route ("/person/{id}/accept", name="person_accept")
     * @IsGranted("ROLE_ACCEPT_CHANGES", subject="person")
     */
    public function accept(Person $person, EntityManagerInterface $em)
    {
        foreach ($person->getChanges() as $change) {
            if($change->getModificationType() == "new") {
                $em->remove($change);
                $person->removeChange($change);
                $person->setApproved(true);
                $em->persist($person);
                $em->flush();
                $this->addFlash('success', 'new Person accepted');
                break;
            }
        }

        return $this->redirectToRoute('person_edit', [
            'id' => $person->getId(),
        ]);
    }
    /**
     * @Route ("/person/{id}/edit", name="person_edit")
     * @IsGranted("ROLE_EDIT_ENTITY", subject="person")
     */
    public function edit(Person $person, Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper,
        UncertainNumberValidator $uncertainNumberValidator, TreeLogic $treeLogic)
    {
        // It is necessary so that the original record doesn't change.
        if ($request->getMethod() === 'POST' && $request->get('person_form')) {
            $personClone = clone $this->personRepository->find($request->get('id'));
            $form = $this->createForm(PersonFormType::class, $personClone,
                [ /* 'include_x' => true*/
                    'user' => $this->getUser(),
                ]
            );
        } else {
            $form = $this->createForm(PersonFormType::class, $person,
                [ /* 'include_x' => true*/
                    'user' => $this->getUser(),
                ]
            );
        }

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $change = new EntityChange();
            $change->setPerson($personClone)->setModificationType("edit")->setChangedBy($user);
            $em->persist($change);
            $person->addChange($change);
            $em->persist($change);

            // create a clone
            $personClone->setUpdateOf($change);
            $personClone->setApproved(false);
            $personClone->setOwner($user);
            $em->persist($personClone);

            $em->flush();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile =  $form['imageFile']->getData();
            if($uploadedFile) {

                $newFilename = $uploaderHelper->uploadPersonImage($uploadedFile, $personClone->getImage());
                $personClone->setImage($newFilename);
            }
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSet($em->getClassMetadata(get_class($personClone)), $form->getViewData());
            $changeSet = $uow->getEntityChangeSet($form->getData());
            $newModel = new PersonFormModel(clone($personClone));

            dump($newModel);
            dump($personClone);
            $em->persist($personClone);
            $em->flush();

            if(!$newModel->isEqual($uncertainNumberValidator, $personClone, true)) {

                $changeSet['uncertainBorn'] = 1;
            }
            if(!$newModel->isEqual($uncertainNumberValidator, $personClone, false)) {
                $changeSet['uncertainDied'] = 1;
            }

            if(isset($changeSet['gender'])) {
                foreach ($personClone->getChildren() as $child) {
                    $personClone->removeChild($child);
                }
            }
            $born = null;
            $died = null;
            $bornUncertain = $form['uncertainBorn']->getData();
            $diedUncertain = $form['uncertainDied']->getData();

            $isBornUncertain = false;
            $isDiedUncertain = false;
     //       dump($changeSet);
            if($bornUncertain) {

                $born = $uncertainNumberValidator->getValue($bornUncertain);

                if($uncertainNumberValidator->isUncertain($bornUncertain)) {
                    $personClone->setBornEstimated($born);
                    $personClone->setBorn(null);
                    $isBornUncertain = true;
                } else {
                    $personClone->setBorn($born);
                    $personClone->setBornEstimated($born);
                }
            } else {
                $personClone->setBorn(null);
                $personClone->setBornEstimated(null);
            }
          //  dd($changeSet);
            if($diedUncertain) {
                $died = $uncertainNumberValidator->getValue($diedUncertain);
                if($uncertainNumberValidator->isUncertain($diedUncertain)) {
                    $personClone->setDiedEstimated($died);
                    $personClone->setDied(null);
                    $isDiedUncertain = true;
                } else {
                    $personClone->setDied($died);
                    $personClone->setDiedEstimated($died);
                }
            } else {
                $personClone->setDied(null);
                $personClone->setDiedEstimated(null);
            }

            $em->persist($personClone);
            $em->flush();

            $age = $form['age']->getData();

            if($age!=null) {

                if($born == null) {
                    if($died != null) {
                        if ($isDiedUncertain) {
                            $personClone->setBornEstimated($born = $died - $age);

                        } else {
                            $personClone->setBorn($born = $died - $age);
                        }
                        $em->persist($personClone);
                        $em->flush();
                    }
                }
                if($died == null) {
                    if($born != null) {
                        if ($isBornUncertain) {
                            $personClone->setDiedEstimated($born + $age);
                        } else {
                            $personClone->setDied($born + $age);
                        }
                        $em->persist($personClone);
                        $em->flush();
                    }
                }
            }

            if (in_array('ROLE_ACCEPT_CHANGES', $this->getUser()->getRoles())) {
                if(isset($changeSet['uncertainBorn']) || isset($changeSet['uncertainDied']) || isset($changeSet['age']) || isset($changeSet['livedAtTimeOfPerson'])) {
//dd($changeSet);
                    $treeLogic->estimateAgesAction($em, $this->getUser());
                }

                if(isset($changeSet['father']) || isset($changeSet['mother']) || isset($changeSet['children'])) {
                    //dd("no");
                  //  $treeLogic->calcLeafAction($em);
                }
            }


            $this->addFlash('success', 'Person updated');
        }

        $addChildren = [];
        $q = $request->query->get('q');
        if(strlen($q) > 0) {
            $addChildren = $this->personRepository->findAllPossibleChildren($this->getUser(), $person, $q);
        }

        return $this->render(
            'person/edit.html.twig', [
            'personForm' => $form->createView(),
            'person' => $person,
            'addchildren'=> $addChildren,
        ]);
    }
    /**
     * @Route("/person/remove_link/{parent}/{child}", name="removeLink")
     *
     */
    public function removeLinkAction(Person $parent, Person $child)
    {
        if($parent->getGender()== 'f')
        {
            $child->setMother(null);
        }
        if($parent->getGender() == 'm')
        {
            $child->setFather(null);
        }
        /*if($parent->getGender('folk'))
        {
            $child->setFolk(null);
        }*/
        $em = $this->getDoctrine()->getManager();
        $em->persist($child);
        $em->flush();

        return $this->redirect($this->generateUrl('person_edit',
            ['id' => $parent->getId()]));
    }

    /**
     * @Route("/person/remove_reference/{person}/{id}", name="remove_reference")
     *  @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function removeReference(Person $person, PersonReference $personReference)
    {

        $person->removePersonReference($personReference);

        $em = $this->getDoctrine()->getManager();
        $em->remove($personReference);
        $em->persist($person);
        $em->flush();
        return $this->redirectToRoute('person_edit', [
            'id' => $person->getId(),
        ]);
    }
    /**
     * @Route("/person/add_reference/{person}", name="add_reference")
     * @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function addReferenceAction(BibleBooksUtilityController $bibleBooksUtilityController, Request $request, Person $person)
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

            $personReference = new PersonReference();
            $personReference->setType($type);
            $personReference->setPerson($person);
            $personReference->setReference($reference);
            $em->persist($personReference);

            $person->addPersonReference($personReference);

            $em->persist($person);
            $em->flush();
            $this->addFlash('success', 'Reference added');
        }else {
            $this->addFlash('error', 'Data not valid');
        }

        return $this->redirectToRoute('person_edit', [
            'id' => $person->getId(),
        ]);
    }

    /**
     * @Route("/person/add_link/{parent}/{child}", name="addLink")
     */
    public function addLinkAction(Person $parent, Person $child)
    {
        if($parent->getGender()== 'f')
        {
            $child->setMother($parent);
        }
        if($parent->getGender() == 'm')
        {
            $child->setFather($parent);
        }
        /*if($parent->getGender('folk'))
        {
            $child->setFolk(null);
        }*/
        $em = $this->getDoctrine()->getManager();
        $em->persist($child);
        $em->flush();

        $this->addFlash('success', 'Child link added');
        return $this->redirectToRoute('person_edit', [
            'id' => $parent->getId(),
        ]);
    }

    /**
     * @Route("/person/{id}", name="person_show")
     */
    public function show(PersonRepository $repository, Person $person/*, Request $request*/)
    {

        $user = $this->getUser();
        if ($user && in_array('ROLE_ADMIN', $user->getRoles() )) {

        };
        /* $addChildren = [];
         $q = $request->query->get('q');
         if(strlen($q) > 2) {
             $addChildren = $repository->findAllPossibleChildren($person, $q);
         }*/
        /* if(sizeof($person->getReference()) > 0)
        {
            /** @var Reference $reference* /
            $reference = $person->getReferenceL()->toArray()[0];
            $reference->generateBibleServerUrl();
        }*/
        return $this->render('person/show.html.twig', [
            'person' => $person,
            // 'addchildren' => $addChildren,
        ]);
    }

    /**
     * @Route("/pedigree", name="pedigree")
     */
    public function pedigree()
    {

        $nodes = $this->personRepository->getNodes($this->getUser());
        $nodesImages = $this->personRepository->getNodesImageList($this->getUser());
        $books = $this->bibleBooksRepository->getJSONNodes();
        $links = $this->personRepository->getLinks($this->getUser());

        $folkNodes = $this->folkRepository->getNodes();
        $folkLinks =  $this->folkRepository->getLinks();

        $nodes = array_merge($nodes, $folkNodes);
        usort($nodes, function($a, $b){
            return ($a['born'] < $b['born']) ? -1 : 1;
        });

        return $this->render('person/pedigree.html.twig', [
            'nodes' => json_encode($nodes),
            'images' => $nodesImages,
            'links'=>json_encode(array_merge($links,$folkLinks)),
            'books'=>$books,
            'person_info_url' => $this->router->generate('person_utility_info'),
        ]);
    }

    private function getUserChange(Person $person) : ?EntityChange
    {
        $user =$this->getUser();
            // check if change is stored in user's change list

        foreach ($person->getChanges() as $change) {
            if($change->getChangedBy() === $user) {
                      return $change;
            }
        }

        return null;
    }

}
