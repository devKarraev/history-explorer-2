<?php

namespace App\Repository;

use App\Entity\EntityChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntityChange|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityChange|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityChange[]    findAll()
 * @method EntityChange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityChange::class);
    }

    // /**
    //  * @return EntityChange[] Returns an array of EntityChange objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EntityChange
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
