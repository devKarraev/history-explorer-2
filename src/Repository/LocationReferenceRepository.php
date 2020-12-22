<?php

namespace App\Repository;

use App\Entity\LocationReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LocationReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationReference[]    findAll()
 * @method LocationReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocationReference::class);
    }

    // /**
    //  * @return LocationReference[] Returns an array of LocationReference objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LocationReference
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
