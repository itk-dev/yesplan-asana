<?php

namespace App\Repository;

use App\Entity\YesplanEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use \Datetime;

/**
 * @method YesplanEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method YesplanEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method YesplanEvent[]    findAll()
 * @method YesplanEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class YesplanEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YesplanEvent::class);
    }

    /**
     * @return YesplanEvent[]
     */
    public function findOldEvents(): array
    {
        $entityManager = $this->getEntityManager();
        $timeNow = new DateTime('NOW');

        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\YesplanEvent p
            WHERE p.eventDate < :timeNow
            ORDER BY p.eventDate ASC'
        )->setParameter('timeNow', $timeNow->format('Y-m-d H:i:s:'));
        // returns an array of event id's
        return $query->getResult();
    }

    // /**
    //  * @return YesplanEvent[] Returns an array of YesplanEvent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('y')
            ->andWhere('y.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('y.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?YesplanEvent
    {
        return $this->createQueryBuilder('y')
            ->andWhere('y.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
