<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    /**
     * @var int
     */
    const PAGE_LIMIT = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getWithSearchQueryBuilder(?string $term) :QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');

        if ($term){
            $qb->andWhere('e.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        return $qb
            ->orderBy('e.orderedIndex', 'ASC');
    }


    public function getOrdered(?User $user)
    {
        //return $this->filterByUser($user)
        return $this->getOrCreateQueryBuilder()
            /* ->select(
                 [
                     'e.id + 20000 as id',
                     'e.name as name',
                     'e.name as full_name',
                     'COALESCE (e.year, e.yearCalculated) as born',
                     'COALESCE (e.year, e.yearCalculated+1) as died',
                     'e.image'
                 ]
             )*/
            //  ->andWhere('COALESCE (e.year, e.yearCalculated) IS NOT NULL')
            ->addOrderBy('e.orderedIndex', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getNodes(?User $user)
    {
        $results = $this->filterByUser($user)
            ->select(
                [
                    'e.id + 20000 as id',
                    'e.name as name',
                    'COALESCE (e.year, e.yearCalculated) as t',
                ]
            )
            //  ->andWhere('COALESCE (e.year, e.yearCalculated) IS NOT NULL')
            ->addOrderBy('e.orderedIndex', 'ASC')
            ->getQuery()
            ->getResult();


        $resultsPersons = $this->filterByUser($user)
            ->select(
                [
                    'p.id as id',
                    'case when LOCATE(\' \', p.name) > 0 then SUBSTRING(p.name, 1, LOCATE(\' \', p.name)-1) else p.name end as name',
                    'COALESCE (p.born, p.bornEstimated, p.bornCalculated) as t',
                    'p.image'
                ]
            )
            ->leftJoin('e.participants', 'p')
            ->andWhere('COALESCE (p.born, p.bornEstimated, p.bornCalculated) IS NOT NULL')
            ->addOrderBy('p.bornCalculated', 'ASC')
            ->addOrderBy('p.leafLevel', 'ASC')
            ->getQuery()
            ->getResult();


        return array_merge($results, $resultsPersons);
    }

    public function getLinks(?User $user)
    {
        $results = $this->filterByUser($user)
            ->select(
                [
                    'e.id + 20000 as id',
                    'COALESCE (e.year, e.yearCalculated) as born',
                    'COALESCE (e.year, e.yearCalculated+1) as died',
                    'e.image'
                ]
            )
            //  ->andWhere('COALESCE (e.year, e.yearCalculated) IS NOT NULL')
            ->addOrderBy('e.orderedIndex', 'ASC')
            ->getQuery()
            ->getResult();


        $resultsPersons = $this->filterByUser($user)
            ->select(
                [
                    'p.id as id',
                    'case when LOCATE(\' \', p.name) > 0 then SUBSTRING(p.name, 1, LOCATE(\' \', p.name)-1) else p.name end as name',
                    'p.name as full_name', 'p.gender as gender', 'p.leafLevel as level' ,
                    'COALESCE (p.born, p.bornEstimated, p.bornCalculated) as born',
                    'COALESCE (p.died, p.diedEstimated, p.diedCalculated, p.bornCalculated + 80) as died',
                    'p.image'
                ]
            )
            ->leftJoin('e.participants', 'p')
            ->andWhere('COALESCE (p.born, p.bornEstimated, p.bornCalculated) IS NOT NULL')
            ->addOrderBy('p.bornCalculated', 'ASC')
            ->addOrderBy('p.leafLevel', 'ASC')
            ->getQuery()
            ->getResult();


        return []; //array_merge($results, $resultsPersons);
    }


    public function checkIsOrderable(Event $event) : bool
    {

        if($event->getUncertainTime() !== null) {
            return true;
        }
        if($event->getHappenedBefore() !== null) {
          if($event->getHappenedAfter() !== null) {
              return true;
          }
          // first element
          if($event->getOrderedIndex() == 0) {
              return true;
          }
        }
        else {
            if($event->getHappenedAfter() !== null) {
                // last element
                $events = $this->getOrCreateQueryBuilder()->getQuery()->getResult();
                if($event->getHappenedAfter()->getIndex() == count($events) -1) {
                    return true;
                }
            }
        }
        return false;
    }

    private function filterByUser(?User $user, QueryBuilder $qb = null)
    {
        $qb = $this->getOrCreateQueryBuilder();

        if (!$user) {
            $qb->andWhere('e.approved > 0');
        } else {
            if (!in_array('ROLE_ACCEPT_CHANGES', $user->getRoles())) {
                // $qb->leftJoin(User::class, 'u', 'WITH', 'p.owner = u')
                $qb->andWhere('e.approved > 0 OR e.owner = :user')
                    ->setParameter('user', $user->getId());
            }
        }
        return $qb;
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('e');
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
            return $changeEntity->getEvent()->getId();
        }, $this->getEntityManager()
                ->getRepository(\App\Entity\EntityChange::class)
                ->createQueryBuilder('u')
                ->where('u.event IS NOT NULL')
                ->getQuery()
                ->getResult()
        ));

        $qb->where('p.updateOf IS NOT null')->orWhere($qb->expr()->in('p.id', $changesId));

        if ($term){
            $qb->andWhere('p.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        return $qb;
    }

    public function getPaginatedChanges(PaginatorInterface $paginator, $request)
    {
        $pagination = null;

        if (count($this->getEntityManager()
                ->getRepository(\App\Entity\EntityChange::class)
                ->createQueryBuilder('u')
                ->where('u.event IS NOT NULL')
                ->getQuery()
                ->getResult()
            )!== 0
        ) {
            $q = $request->query->get('q');
            $queryBuilder = $this->getForAdminChangesList($q);
            $paginationData = $this->preparePaginator($queryBuilder->getQuery()->getResult());

            $pagination = $paginator->paginate(
                $paginationData,
                $request->query->getInt('page', 1)/*page number*/,
                self::PAGE_LIMIT/*limit per page*/
            );
        }

        return $pagination;
    }

    /**
     * Prepare paginator data for rendering.
     *
     * @param array $pagination
     *
     * @return array
     */
    private function preparePaginator(array $pagination): array
    {
        $resultArray = [];
        foreach ($pagination as $entity) {
            if ($entity->getUpdateOf() != null) {
                $rootId = $entity->getUpdateOf()->getEvent()->getId();
            } else {
                $rootId = $entity->getId();
            }

            if (key_exists($rootId, $resultArray) === false) {
                $resultArray[$rootId] = [$entity];
            } else {
                array_push($resultArray[$rootId] , $entity);
            }
        }

        return $resultArray;
    }

    public function getEventsForTimemap()
    {
        return  $this->getOrCreateQueryBuilder()

            ->select(
                [
                    'e.name as name, COALESCE (e.year, e.yearCalculated) as t, l.lat, l.lon'
                ]
            )
            ->leftJoin('e.location', 'l')
            ->addOrderBy('e.orderedIndex', 'ASC')
            ->getQuery()
            ->getResult();

    }
}
