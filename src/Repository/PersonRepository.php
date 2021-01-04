<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Folk;
use App\Entity\Person;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function getWithSearchQueryBuilder(?User $user, ?string $term, bool $startsWith = false) :QueryBuilder
    {
        $qb =$this->filterByUser($user);

        if ($term){
            if($startsWith) {
                $qb->andWhere('p.name LIKE :term')
                    ->setParameter('term', $term . '%');
            } else {
                $qb->andWhere('p.name LIKE :term')
                    ->setParameter('term', '%' . $term . '%');
            }
        }

        return $qb
            ->orderBy('p.name', 'ASC');
    }

    /**
     * Get records for admin change list.
     *
     * @param string|null $term
     *
     * @return QueryBuilder
     */
    public function getForAdminChangesList(?string $term): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        $changesId = array_unique(array_map(function ($changeEntity) {
            return $changeEntity->getPerson()->getId();
        }, $this->getEntityManager()->getRepository(\App\Entity\EntityChange::class)->findAll()));

        $qb->where('p.updateOf IS NOT null')->orWhere($qb->expr()->in('p.id', $changesId));

        if ($term){
            $qb->andWhere('p.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        return $qb;
    }

    public function getIdByGender(?User $user, string $gender, bool $includeUnknown = true, ?int $olderThan = null, ?int $youngerThan = null) {

        $qb = $this->filterByUser($user)
            ->select('p.id');
        if( $includeUnknown) {
            $qb->andWhere('p.gender IN (:gender , \'\')');
        } else {
            $qb->andWhere('p.gender =:gender');
        }

        $qb->setParameter('gender', $gender);
        return $qb->getQuery()->getResult();

    }

    public function findPossibleParents(?User $user, string $term, bool $begin, string $gender ="m", int $limit = 5) {

        $qb = $this->filterByUser($user);

        $parameters = [
            'gender' => $gender,
            'termBegin' => (!$begin ? '%' :'') .$term.'%',
        ];

        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :termBegin')
            ->andWhere('p.gender = :gender');
        if(!$begin) {
            $qb = $qb->andWhere('p.name NOT LIKE :termBeginNotIncluded');
            $parameters['termBeginNotIncluded'] = $term.'%';
        }

        return $qb->setParameters($parameters)
            ->setMaxResults($limit)
            ->orderBy('p.name')
            ->getQuery()
            ->getResult();
    }

    public function getLinks()
    {

// TODO user
        $results = $this->createQueryBuilder('c')
            ->select(
                [
                    'p.id as source',
                    'c.id as target',
                ]
            )
            ->andwhere('p.id IS NOT NULL')
            ->leftJoin(Person::class, 'p', 'WITH', 'c.father = p OR c.mother = p')
            ->getQuery()
            ->getResult();
       // dd(json_encode($results));
        //return json_encode($results);p
        return $results;
    }

    public function getNodesImageList(?User $user) {
        $pictures = [];
        $persons =  $this->filterByUser($user)
            ->andWhere('COALESCE (p.born, p.bornEstimated, p.bornCalculated) IS NOT NULL')
            ->addOrderBy('p.bornCalculated', 'ASC')
            ->addOrderBy('p.leafLevel', 'ASC')
            ->getQuery()
            ->getResult();
        /** @var Person $person */
        foreach ($persons as $person) {
            $pictures[$person->getId()] = $person->getImage();
        }
        return $pictures;
    }

    public function getNodes(?User $user)
    {
        $results =  $this->filterByUser($user)
            ->select(
                [
                    'p.id as id',
                    'case when LOCATE(\' \', p.name) > 0 then SUBSTRING(p.name, 1, LOCATE(\' \', p.name)-1) else p.name end as name',
                    'p.name as full_name', 'p.gender as gender', 'p.leafLevel as level',
                    'COALESCE (p.born, p.bornEstimated, p.bornCalculated) as born',
                    'COALESCE (p.died, p.diedEstimated, p.diedCalculated, p.bornCalculated + 80) as died',
                    'case when p.born IS NULL then (case when p.bornEstimated IS NULL then \'c\' else \'e\' end) else \'d\' end  as fuzzyBegin',
                    'case when p.died IS NULL then (case when p.diedEstimated IS NULL then \'c\' else \'e\' end) else \'d\' end as fuzzyEnd',
                    'p.image',
                ]
            )
            ->andWhere('COALESCE (p.born, p.bornEstimated, p.bornCalculated) IS NOT NULL')
            ->addOrderBy('p.bornCalculated', 'ASC')
            ->addOrderBy('p.leafLevel', 'ASC')
            ->getQuery()
            ->getResult();


       // $resultsFolk =  $this->filterByUser($user)
           /* ->select(
                [
                    'f.id  as id',
                    'f.name as name',
                  //  'f.name as full_name', '"folk" as gender', '0 as level' ,
                 //   'COALESCE (p.born, p.bornEstimated, p.bornCalculated) as born',
                 //   'COALESCE (p.died, p.diedEstimated, p.diedCalculated, p.bornCalculated + 80) as died',
                 //   'p.image'
                ]
            )*/
            //->innerJoin('u.groups', 'g')
           // ->where('g.id = :group_id')
        //    ->leftJoin('p.progenitor', 'f')//, 'WITH', 'p.progenitor  = f')
            //->andWhere('COALESCE (p.born, p.bornEstimated, p.bornCalculated) IS NOT NULL')
            //    ->andWhere('f.id = p.progenitor')
           // ->addOrderBy('p.bornCalculated', 'ASC')
            //->addOrderBy('p.leafLevel', 'ASC')
         /*   ->addSelect('f')
            ->getQuery()
            ->getResult();*/

       // dd($resultsFolk);
        //return json_encode($results);
        return $results;
    }

   /* public function getNodes(?User $user)
    {
        return $this->filterByUser($user)

            ->andWhere('COALESCE (p.born, p.bornEstimated, p.bornCalculated) IS NOT NULL')
            ->orderBy('p.bornCalculated', 'ASC')
            ->getQuery()
            ->getResult();
        //  dd(json_encode($results));
       // return json_encode($results);
    }*/

    public function getAllOrderedByBirth(?User $user, bool $calculated = false)
    {
        return $this->filterByUser($user)
            ->orderBy(($calculated ? 'p.bornCalculated' : 'p.bornEstimated'), 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllPossibleEventPeople(User $user, Event $event, ?string $term, ?int $tolerance)
    {
        $qb = $this->filterByUser($user);


        $parameters =[];
        //$qb = $this->getWithSearchQueryBuilder($term);
            //->andWhere('e.id' <> '')
        if ($term) {
            $qb->andWhere('p.name LIKE :term')
            ->setParameter('term', '%'.$term.'%');

        }

        $ta = $tb = $event->getYearCalculated();// $year = $event->guessTime(null);
        if(!$ta) {
            $ta = $event->getHappenedAfter() ? $event->getHappenedAfter()->getYearCalculated() : null;
            $tb = $event->getHappenedBefore() ? $event->getHappenedBefore()->getYearCalculated() : null;
        }
        if($tolerance) {
            if ($ta) $ta -= $tolerance;
            if($tb) $tb += $tolerance;
        }

        if($ta) {
            if ($term) {
                $qb->andWhere(
                    'p.diedCalculated >= :yearAfter OR (p.diedCalculated IS NULL OR p.name LIKE :term2)'
                );
            }
            else {
                $qb->andWhere(
                    'p.diedCalculated >= :yearAfter');
            }
            $qb->setParameter('yearAfter', $ta);
        }
        if($tb) {
            if ($term) {
                $qb->andWhere(
                    'p.bornCalculated <= :yearBefore OR (p.bornCalculated IS NULL OR p.name LIKE :term2)'
                );
            }
            else {
                $qb->andWhere(
                    'p.bornCalculated <= :yearBefore'
                );
            }
            $qb->setParameter('yearBefore', $tb);
        }
        if($term  && ($ta || $tb)) {
            $qb->setParameter('term2', $term.'%');
        }
//dd($qb->getQuery());

             /* $qb->andWhere('p.bornCalculated <= :year + :tolerance AND p.diedCalculated IS NULL OR p.diedCalculated >= :year - :tolerance')
                    ->setParameter('year', $year)
                    ->setParameter('tolerance', $tolerance);
           } else {
                $qb->andWhere('p.bornEstimated <= :year AND p.diedEstimated >= :year')
                    ->setParameter('year', $year);
            }*/

        $result = $qb
            ->getQuery()
            ->getResult();

        return array_diff($result, $event->getParticipants()->toArray());

    }


    public function findAllPossibleChildren(?User $user, Person $person, ?string $term)
    {

        $qb = $this->filterByUser($user);

        //$qb = $this->getWithSearchQueryBuilder($term);
        if ($term){
            $qb->andWhere('p.name LIKE :term');
        }
        $qb->setParameter('p', $person)
            ->setParameter('parentLevel', $person->getLeafLevel())
            ->setParameter('term' ,'%' . $term . '%');
     // dd($person->getLeafLevel());
        /*$parameters = [
            'p' => $person,
            'parentLevel' => $person->getLeafLevel() ?? -1,
            'term' => '%' . $term . '%',
        ];*/
        $qb = $person->getGender() == 'm' ?
            $qb->andWhere('p.father <> :p OR p.father IS NULL') :
            $qb->andWhere('p.mother <> :p OR p.mother IS NULL');

         //  ->setParameters($parameters)

        return $qb->andWhere('p.leafLevel IS NULL OR p.leafLevel > :parentLevel')

          //  ->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }

    public function findNoParents()
    {
        $qb = $this->createQueryBuilder('p');

        //$qb = $this->getWithSearchQueryBuilder($term);

        return  $qb->andWhere('p.father IS NULL AND p.mother IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function resetLeaves() {
        $this->getOrCreateQueryBuilder()
            ->update(Person::class, 'p')
            ->set('p.leafLevel', '?1')
            ->set('p.leafStart', '?1')
            ->set('p.leafOut', '?1')
            ->setParameter(1, null)
            ->getQuery()
            ->execute();
    }

    public function resetCalcEstimations() {
        $this->getOrCreateQueryBuilder()
            ->update(Person::class, 'p')
            ->set('p.bornCalculated', '?1')
            ->set('p.diedCalculated', '?1')
            ->setParameter(1, null)
            ->getQuery()
            ->execute();
    }
    public function getAvgAgeInGeneration()
    {
        return $this->getOrCreateQueryBuilder()->select(['p.leafLevel', 'AVG(COALESCE(p.born, p.bornEstimated, p.bornCalculated)) as born', 'AVG(COALESCE(p.died, p.diedEstimated, p.diedCalculated)) as died', 'AVG(COALESCE(p.died - p.born, p.diedEstimated - p.bornEstimated, p.diedCalculated - p.bornCalculated)) as age'])
        ->where('p.leafLevel IS NOT NULL')
            ->groupBy('p.leafLevel')
            ->getQuery()
            ->getResult();
    }


   /* public function getAllParentless() {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->andWhere('p.father is NULL AND p.mother IS NULL')

            ->getQuery()
            ->getResult();
    }*/

    public function getAllGenderless() {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->andWhere('p.gender = \'\'')

            ->getQuery()
            ->getResult();
    }

    private function filterByUser(?User $user, QueryBuilder $qb = null)
    {
        $qb = $qb ? : $this->createQueryBuilder('p');

        if (!$user) {
            $qb->andWhere('p.approved > 0');
        } else if (!in_array('ROLE_ACCEPT_CHANGES', $user->getRoles() )) {
            // $qb->leftJoin(User::class, 'u', 'WITH', 'p.owner = u')
            $qb->andWhere('p.approved > 0 OR p.owner = :user')
                ->setParameter('user', $user->getId());
        }
        return $qb;
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('p');
            //->addSelect('COALESCE(p.born, p.bornEstimated, p.bornCalculated) as bornOrder');
    }
}
