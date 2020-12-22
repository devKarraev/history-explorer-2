<?php

namespace App\Repository;

use App\Entity\BibleBooks;
use App\Entity\Event;
use App\Entity\EventReference;
use App\Entity\Folk;
use App\Entity\FolkReference;
use App\Entity\Location;
use App\Entity\LocationReference;
use App\Entity\Person;
use App\Entity\PersonReference;
use App\Entity\Reference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reference|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reference|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reference[]    findAll()
 * @method Reference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reference::class);
    }

    public function findByBookName(string $name) {
        return $this->createQueryBuilder('r')
            ->select('b')
            ->leftJoin(BibleBooks::class, 'b', 'WITH', 'r.book = b')
            ->andWhere('b.name LIKE :term')
            ->setParameter('term', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    public function getAllByPerson(Person $person)
    {
        return $this->getOrCreateQueryBuilder()->select(['r.url', 'pr.type'])
            ->leftJoin(PersonReference::class, 'pr', 'WITH', 'r = pr.reference')
            ->leftJoin(Person::class, 'p', 'WITH', 'p = pr.person')
            ->where('p.leafLevel IS NOT NULL')
            ->groupBy('p.leafLevel')
            ->getQuery()
            ->getResult();
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('r');
    }

    public function getAllReferenced()
    {
        return $this->getOrCreateQueryBuilder()


            ->andWhere('r.book IS NOT NULL')
        ->orderBy('r.book, r.chapter, r.verse', 'ASC')->getQuery()->getResult()
        ;

    }
/*
    public function getReferencedFolks(Reference $r)
    {
        return $qb = $this->getOrCreateQueryBuilder()
            ->andWhere('r.id := rid')
            ->setParameter('rid',$r->getId() )
            ->addSelect('f.*')
            ->leftJoin(FolkReference::class, 'fr', Join::WITH, 'fr.reference = r')
            ->leftJoin(Folk::class, 'f', Join::WITH, 'fr.folk = f')
            ->getQuery()
            ->getResult();

    }
    public function getReferencedPersons(Reference $r)
    {
        return $qb = $this->getOrCreateQueryBuilder()
            ->andWhere('r.id = :rid')
            ->setParameter('rid',$r->getId() )
            ->leftJoin(PersonReference::class, 'pr', Join::WITH, 'pr.reference = r')
            ->leftJoin(Person::class, 'p', Join::WITH, 'pr.person = p')
            ->select('p' )
            ->getQuery()
         ->getResult();

    }

    public function getReferenced()
    {
        /* return $qb = $this->getOrCreateQueryBuilder()
           //  ->andWhere('r.id := rid')
            // ->setParameter('rid',$r->getId() )

             ->leftJoin(BibleBooks::class, 'b', 'WITH', 'r.book = b')
             ->leftJoin(PersonReference::class, 'pr', Join::WITH, 'pr.reference = r')
             ->leftJoin(Person::class, 'p', Join::WITH, 'pr.person = p')
             ->leftJoin(FolkReference::class, 'fr', Join::WITH, 'fr.reference = r')
             ->leftJoin(Folk::class, 'f', Join::WITH, 'fr.folk = f')
             ->leftJoin(LocationReference::class, 'lr', Join::WITH, 'lr.reference = r')
             ->leftJoin(Location::class, 'l', Join::WITH, 'lr.location = l')
             ->leftJoin(EventReference::class, 'er', Join::WITH, 'er.reference = r')
             ->leftJoin(Event::class, 'e', Join::WITH, 'er.event = e')
          //   ->select('b.name as name, p.name as personName, p.id as personId, f.name as folkName, f.id as folkId, l.name as locationName' )
            ->addSelect('r')
             //->addSelect('GROUP_CONCAT(p.id) as pids')
             ->getQuery()
          ->getResult();* /

       // $rsm->addRootEntityFromClassMetadata(Reference::class, 'r');
        //$rsm->addJoinedEntityFromClassMetadata(BibleBooks::class, 'b', 'r', 'address', array('id' => 'address_id'));



      //  $sqlBuilder = $this->_em->getConnection()->createQueryBuilder();

        $rsmBuilder = new ResultSetMappingBuilder($this->_em);
        $rsmBuilder->addRootEntityFromClassMetadata(Reference::class, 'r');
        $rsmBuilder->addJoinedEntityFromClassMetadata(BibleBooks::class, 'b', 'r', 'bibleBooks', array('id' => 'bid', 'name' => 'bName' ));

        //$sqlBuilder->addSelect($rsmBuilder->generateSelectClause() );

       // $sqlBuilder->addSelect('b.name AS bookName');
        $rsmBuilder->addScalarResult('bookName', 'bookName', 'string');

       // $sqlBuilder->from(Reference::class, 'r');
        $sql = "SELECT * FROM r";


        $query = $this->_em->createNativeQuery($sql, $rsmBuilder);
      //  $query->setParameters($sqlBuilder->getParameters() );
        $data = $query->getResult();
        dd($data);
    }*/
    // /**
    //  * @return Reference[] Returns an array of Reference objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function getReferencedPersons(Reference $reference)
    {
        return $qb = $this->getOrCreateQueryBuilder()
            ->select('p')
            ->andWhere('r.id = :rid')
            ->setParameter('rid',$reference->getId() )
            ->leftJoin(PersonReference::class, 'pr', Join::WITH, 'pr.reference = r')
            ->leftJoin(Person::class, 'p', Join::WITH, 'pr.person = p')
            ->getQuery()
            ->getResult();

    }
}
