<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Repository;

use App\Entity\YesplanEvent;
use DateInterval;
use Datetime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method YesplanEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method YesplanEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method YesplanEvent[]    findAll()
 * @method YesplanEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method YesplanEvent[]    findNewProductionOnlineEvents()
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
            'SELECT e
            FROM App\Entity\YesplanEvent e
            WHERE e.eventDate < :timeNow'
        )->setParameter('timeNow', $timeNow->format('Y-m-d H:i:s:'));
        // returns an array of event id's
        return $query->getResult();
    }

    public function findNewProductionOnlineEvents(): array
    {
        //get all events with productiononline = 1 not already created in Asana
        // returns an array of event id's

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.production_online = true AND  (a.created_in_new_events is null OR a.created_in_new_events = 0)
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([]);

        return $stmt->fetchAll();
    }

    public function findNewEventOnlineEvents(): array
    {
        //get all events with eventonline = 1 not already created in Asana

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
       SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.event_online = true AND  (a.created_in_new_events_online is null OR a.created_in_new_events_online = 0)
           ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([]);

        return $stmt->fetchAll();
    }

    public function findFewTickets(): array
    {
        //(tixintegrations_ticketsavailable + tixintegrations_ticketsreserved) / (tixintegrations_capacity - tixintegrations_blocked - tixintegrations_allocated) * 100
        //<10%
        //Returns all evet ids of events not already in ASANA boards for few tickets, with capacitypercent < 10%

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.capacity_percent >= 10 AND  (a.created_in_few_tickets is null OR a.created_in_few_tickets = 0)
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([]);
        //   print_r($stmt->fetchAll());
        return $stmt->fetchAll();

        /*

               $qb = $this->createQueryBuilder('p')
               ->select('e')
               ->from('App\Entity\YesplanEvent', 'e')
               ->leftjoin('App\Entity\AsanaEvent','a', 'e.id = a.id' )
               ->where('e.capacityPercent <= :capacityPercent AND a.createdInFewTickets <> true')
               ->setParameters(['capacityPercent' => 10]);
        */

    //   return $qb->getQuery()->getResult();
    }

    public function findLastMinutTickets(): array
    {
        //(tixintegrations_ticketsavailable + tixintegrations_ticketsreserved) / (tixintegrations_capacity - tixintegrations_blocked - tixintegrations_allocated) * 100
        //<25% + 3 weeks before event
        $entityManager = $this->getEntityManager();
        $nowPlus3Weeks = new DateTime('NOW');
        $nowPlus3Weeks->add(new DateInterval('P21D'));

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.capacity_percent <= 75 AND  (a.created_in_last_minute is null OR a.created_in_last_minute = 0) AND y.event_date <= :nowPlus3Weeks
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['nowPlus3Weeks' => date_format($nowPlus3Weeks, 'Y-m-d H:i:s')]);

        return $stmt->fetchAll();
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
