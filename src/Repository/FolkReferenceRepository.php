<?php

namespace App\Repository;

use App\Entity\FolkReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FolkReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method FolkReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method FolkReference[]    findAll()
 * @method FolkReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolkReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FolkReference::class);
    }

    // /**
    //  * @return FolkReference[] Returns an array of FolkReference objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FolkReference
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
