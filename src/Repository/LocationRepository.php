<?php

namespace App\Repository;

use App\Entity\Location;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Location|null find($id, $lockMode = null, $lockVersion = null)
 * @method Location|null findOneBy(array $criteria, array $orderBy = null)
 * @method Location[]    findAll()
 * @method Location[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function serializeResults(QueryBuilder $qb)
    {
        return $qb->select(
                [
                    'l.id as id', 'l.name as name', 'l.type as type', 'l.lat as lat', 'l.lon as lon',
                ]
            )
            ->getQuery()->getResult();
    }
    /*public function getAll()
    {
        $results = $this->getAllQuery()
            ->getResult();
       return $results;
    }*/

    public function getWithSearchQueryBuilder(?User $user, ?string $term) :QueryBuilder
    {
        $qb =$this->filterByUser($user);

        if ($term){
            $qb->andWhere('l.name LIKE :term')
                ->setParameter('term', $term . '%');
        }

        return $qb
            ->orderBy('l.name', 'ASC');
    }

    private function filterByUser(?User $user, QueryBuilder $qb = null)
    {
        $qb = $qb ? : $this->createQueryBuilder('l');

        if (!$user) {
            $qb->andWhere('l.approved > 0');
        } else if (!in_array('ROLE_ACCEPT_CHANGES', $user->getRoles() )) {
            $qb->andWhere('l.approved > 0 OR l.owner = :user')
                ->setParameter('user', $user->getId());
        }
        return $qb;
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('l');
    }
}
