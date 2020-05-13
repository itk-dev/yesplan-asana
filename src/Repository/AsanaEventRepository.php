<?php

namespace App\Repository;

use App\Entity\AsanaEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AsanaEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method AsanaEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method AsanaEvent[]    findAll()
 * @method AsanaEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AsanaEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AsanaEvent::class);
    }

    // /**
    //  * @return AsanaEvent[] Returns an array of AsanaEvent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AsanaEvent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
