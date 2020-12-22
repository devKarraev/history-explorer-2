<?php

namespace App\Repository;

use App\Entity\EventReference;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventReference[]    findAll()
 * @method EventReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventReference::class);
    }

}
