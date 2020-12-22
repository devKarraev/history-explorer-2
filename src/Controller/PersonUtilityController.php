<?php


namespace App\Controller;


use App\Repository\PersonRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PersonUtilityController extends AbstractController
{
    /**
     * @Route("/person/utility/fathers", methods="GET", name="person_utility_fathers")
     *
     */
    public function getFathersApi(PersonRepository $personRepository, Request $request)
    {
        $term = $request->query->get('query');

        $fathersWordBegin = $personRepository->findPossibleParents($this->getUser(), $term, true, 'm');//($request->query->get('query'));
        $fathers = array_merge($fathersWordBegin, $personRepository->findPossibleParents($this->getUser(), $term, false, 'm'));

        return $this->json([
            'persons' => $fathers
        ], 200, [] , [
                'groups' => ['main']
            ]

        );
    }

    /**
     * @Route("/person/utility/names", methods="GET", name="person_utility_names")
     *
     */
    public function getNamesApi(PersonRepository $personRepository, Request $request)
    {
        $term = $request->query->get('query');

        $queryBuilder = $personRepository->getWithSearchQueryBuilder($this->getUser(), $term, true);
        $persons = $queryBuilder->select('p.name as name')->getQuery()->getResult();

        return $this->json([
            'persons' => $persons
        ], 200, [] , [
                'groups' => ['main']
            ]

        );
    }

    /**
     * @Route("/person/utility/info", methods="GET", name="person_utility_info")
     *
     */
    public function getInfoApi(PersonRepository $personRepository, Request $request)
    {
        $id = $request->query->get('query');
        $person = $personRepository->findOneBy(['id' => $id]);
      //

        return $this->render('person/info.html.twig', ['person' => $person]);


        /*return $this->json([
            'data' => $person
        ], 200, [] , [
                'groups' => ['info']
            ]

        );*/
    }

    /**
     * @Route("/person/utility/mothers", methods="GET", name="person_utility_mothers")
     *
     */
    public function getMothersApi(PersonRepository $personRepository, Request $request)
    {
        $term = $request->query->get('query');
        $mothersWordBegin = $personRepository->findPossibleParents($term, true, 'f');//($request->query->get('query'));
        $mothers = array_merge($mothersWordBegin, $personRepository->findPossibleParents($term, false, 'f'));

        return $this->json([
            'persons' => $mothers
        ], 200, [] , [
                'groups' => ['main']
            ]

        );
    }

    /**
     * @Route("/person/utility/children", methods="GET", name="person_utility_children")
     *
     */
    /*
    public function getChildrenApi(PersonRepository $personRepository, Request $request)
    {
        $term = $request->query->get('query');
        $childrenWordBegin = $personRepository->findPossibleParents($term, true, '');//($request->query->get('query'));
        $children = array_merge($childrenWordBegin, $personRepository->findPossibleParents($term, false, 'm'));

        return $this->json([
            'persons' => $children
        ], 200, [] , [
                'groups' => ['main']
            ]

        );
    }*/
}