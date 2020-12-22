<?php

namespace App\Repository;

use App\Entity\BibleBooks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BibleBooks|null find($id, $lockMode = null, $lockVersion = null)
 * @method BibleBooks|null findOneBy(array $criteria, array $orderBy = null)
 * @method BibleBooks[]    findAll()
 * @method BibleBooks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BibleBooksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BibleBooks::class);
    }

    public function findByName(string $name) {

        return $this->createQueryBuilder('b')
            ->andWhere('b.name LIKE :term')
            ->setParameter('term', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    public function getNodes()
    {
        $results =  $this->createQueryBuilder('b')
            ->select(
                [
                    'CONCAT(\'book_\', b.id) as id', 'b.name as name', '\'book\' as gender', 'b.fromYear as born', 'COALESCE (b.toYear, b.fromYear+40) as died'
                ]
            )
            ->andWhere('b.fromYear IS NOT NULL')
            ->orderBy('b.fromYear', 'ASC')
            ->getQuery()
            ->getResult();
        //  dd(json_encode($results));
        return json_encode($results);
    }

    public function getJSONNodes()
    {
        $results = $this->createQueryBuilder('b')
            ->select(
                [
                    'b.name as name, b.fromYear as born, COALESCE(b.toYear, b.fromYear +200) as died',
                ]
            )
            ->andWhere('b.fromYear IS NOT NULL')
            ->orderBy('b.fromYear')
            ->getQuery()
            ->getResult();
        // dd(json_encode($results));
        return json_encode($results);
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('b');
    }
}
