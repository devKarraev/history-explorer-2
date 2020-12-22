<?php

namespace App\Repository;

use App\Entity\ChapterVerses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChapterVerses|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChapterVerses|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChapterVerses[]    findAll()
 * @method ChapterVerses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChapterVersesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChapterVerses::class);
    }

    // /**
    //  * @return ChapterVerses[] Returns an array of ChapterVerses objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChapterVerses
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
