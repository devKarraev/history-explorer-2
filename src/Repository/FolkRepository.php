<?php

namespace App\Repository;

use App\Entity\Folk;
use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Folk|null find($id, $lockMode = null, $lockVersion = null)
 * @method Folk|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folk[]    findAll()
 * @method Folk[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Folk::class);
    }

    public function getNodes()
    {
        $qb = $this->createQueryBuilder('f');

        $results = $qb->select(
                [
                    '10000 + f.id as id',
                    'f.name as full_name',
                    'f.name as name',
                    '\'folk\' as gender', '0 as level' ,
                    'COALESCE (p.born+40, p.bornEstimated+40, p.bornCalculated+40, ffp.born+40, -2000) as born',
                    'COALESCE (f.died, p.born+1000, 0) as died',
                ]
            )
            //->andWhere('COALESCE (p.born, p.bornEstimated, p.bornCalculated) IS NOT NULL')
            ->leftJoin('f.people', 'p')
            ->leftJoin('f.fatherFolk', 'ff')
            ->leftJoin('ff.people', 'ffp')
            ->andWhere('p.id IS NOT NULL OR ff.id IS NOT NULL')
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function getLinks()
    {
        $results = $this->createQueryBuilder('f')
            ->select(
                [
                    'p.id as source',
                    'f.id + 10000 as target',
                ]
            )
            ->leftJoin('f.people', 'p')
            ->andWhere('p.id IS NOT NULL')
            ->getQuery()
            ->getResult();

        $results2 = $this->createQueryBuilder('cf')
            ->select(
                [
                    'f.id + 10000 as source',
                    'cf.id + 10000 as target',
                ]
            )
            ->leftJoin('cf.fatherFolk', 'f')
            ->andWhere('f.id IS NOT NULL')
            ->getQuery()
            ->getResult();

        $results3 = $this->createQueryBuilder('f')
            ->select(
                [
                    'f.id + 10000 as source',
                    'p.id as target',
                ]
            )
            ->leftJoin(Person::class, 'p', 'WITH', 'p.folk = f')
            ->andWhere('p.id IS NOT NULL')
            ->getQuery()
            ->getResult();
//dd($results3);
        return array_merge($results, $results2, $results3);
    }

    // /**
    //  * @return Folk[] Returns an array of Folk objects
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
    public function findOneBySomeField($value): ?Folk
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
