<?php

namespace App\Repository;

use App\Entity\PersonReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PersonReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonReference[]    findAll()
 * @method PersonReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonReference::class);
    }

    // /**
    //  * @return PersonReference[] Returns an array of PersonReference objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PersonReference
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
