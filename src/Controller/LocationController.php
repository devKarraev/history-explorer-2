<?php

namespace App\Controller;


use App\Entity\Location;
use App\Entity\LocationReference;
use App\Entity\Reference;
use App\Form\LocationFormType;
use App\Logic\SyntaxLogic;
use App\Repository\LocationRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends BaseController
{
    /**
     * @var LocationRepository
     */
    private $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @Route("/location", name="location")
     */
    public function index()
    {
        return $this->render('location/index.html.twig', [
            'controller_name' => 'LocationController',
        ]);
    }

    /**
     * @Route("/location/list", name="locations")
     */
    public function list(Request $request, PaginatorInterface $paginator)
    {
        //$locationsSerialized = $this->locationRepository->getAll();
       // $locations = $this->locationRepository->findAll();

        $sort = ['field' => 'l.name', 'direction' => 'asc'];

        $q = $request->query->get('q');
        $queryBuilder = $this->locationRepository->getWithSearchQueryBuilder($this->getUser(), $q);
     //   $queryBuilder->andWhere('l.approved = 1');
//dd($queryBuilder->getQuery());
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1),
            100, [
                'defaultSortFieldName' => $sort['field'],
                'defaultSortDirection' => $sort['direction']
            ]
        );

        $locationsSerialized = $this->locationRepository->serializeResults($queryBuilder);

        return $this->render('location/list.html.twig', [
            'locationsSerialized' => $locationsSerialized,
           // 'locations' => $locations,
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/location/edit/{id}", name="location_edit")
     * @IsGranted("ROLE_EDIT_ENTITY", subject="location")
     */
    public function edit(Location $location, Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper)
    {
        $form = $this->createForm(LocationFormType::class, $location, [
            /* 'include_x' => true*/
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $uploadedFile =  $form['imageFile']->getData();
            if($uploadedFile) {

                $newFilename = $uploaderHelper->uploadPersonImage($uploadedFile, $location->getImage());
                $location->setImage($newFilename);
            }

            $em->persist($location);
            $em->flush();

            $this->addFlash('success', 'Location updated');
            return $this->redirectToRoute('location_edit', [
                'id' => $location->getId()
            ]);
        }

        $addChildren = [];
        $q = $request->query->get('q');
        if(strlen($q) > 2) {
            $addChildren = $this->locationRepositoryRepository->findAllPossibleChildren($location, $q);
        }

        return $this->render(
            'location/edit.html.twig', [
            'locationForm' => $form->createView(),
            'location' => $location,
        ]);
    }
    /**
     * @Route("/location/show/{id}", name="location_show")
     */
    public function show(Location $location)
    {

        return $this->render(
            'location/show.html.twig', [
            'location' => $location,
        ]);
    }

    /**
     * @Route ("/location/recalc/latlng", name ="recalc_latlng")
     * @IsGranted("ROLE_ADMIN")
     */
    public function recalcLatLng(EntityManagerInterface $em, SyntaxLogic $syntaxLogic)
    {
        $syntaxLogic->latlngReader($em, $this->getUser());
    }

    /**
     * @Route("/location/remove_reference/{location}/{id}", name="location_remove_reference")
     *  @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function removeReference(Location $location, LocationReference $locationReference)
    {
        $location->removeLocationReference($locationReference);

        $em = $this->getDoctrine()->getManager();
        $em->remove($locationReference);
        $em->persist($location);
        $em->flush();
        return $this->redirectToRoute('location_edit', [
            'id' => $location->getId()
        ]);
    }

    /**
     * @Route("/location/add_reference/{location}", name="location_add_reference")
     * @IsGranted("ROLE_EDIT_ENTITY")
     */
    public function addReferenceAction(BibleBooksUtilityController $bibleBooksUtilityController, Request $request, Location $location) {

        $type = $request->request->get('reftype');
        $term = $request->request->get('submit_param');

        $valid = $bibleBooksUtilityController->checkIsValid($term);
        if($valid['fullReference']!="") {

            $em = $this->getDoctrine()->getManager();

            $reference = $em->getRepository("App:Reference")->findOneBy([
                'url' => $valid['fullReference']
            ]);
            if($reference == null) {
                $reference = new Reference();
                $reference->setUrl($valid['fullReference']);
                $reference->setIsBibleRef($valid['book']!="");
                $em->persist($reference);
            }

            $locationReference = new LocationReference();
            $locationReference->setType($type);
            $locationReference->setLocation($location);
            $locationReference->setReference($reference);
            $em->persist($locationReference);

            $location->addLocationReference($locationReference);

            $em->persist($location);
            $em->flush();
            $this->addFlash('success', 'Reference added');
        }else {
            $this->addFlash('error', 'Data not valid');
        }

        return $this->redirectToRoute('location_edit', [
            'id' => $location->getId()
        ]);
    }

}
