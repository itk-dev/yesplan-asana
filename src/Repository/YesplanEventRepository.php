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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method YesplanEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method YesplanEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method YesplanEvent[]    findAll()
 * @method YesplanEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method YesplanEvent[]    findNewProductionOnlineEvents()
 */
class YesplanEventRepository extends ServiceEntityRepository
{
    public function __construct(array $yesplanEventRepositoryOptions, ManagerRegistry $registry)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($yesplanEventRepositoryOptions);
        parent::__construct($registry, YesplanEvent::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'yesplan_intern_profile_id',
            'yesplan_external_profile_id',
            'yesplan_free_profile_id',
        ]);
    }

    /**
     * Returns all events older than today.
     *
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

    /**
     * Finds all events with productionOnline = 1, and profile only internal events.
     *
     * @return YesplanEvent[]
     */
    public function findNewProductionOnlineEvents(): array
    {
        //get all events with productiononline = 1 not already created in Asana
        // returns an array of event id's

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.production_online = true AND  (a.created_in_new_events is null OR a.created_in_new_events = 0) AND y.profile_id = :profileIdIntern
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['profileIdIntern' => $this->options['yesplan_intern_profile_id']]);

        return $stmt->fetchAll();
    }

    /**
     * Finds all events with productionOnline = 1 and profile extern, intern and free events.
     *
     * @return YesplanEvent[]
     */
    public function findNewProductionOnlineIncludingGratisandExternEvents(): array
    {
        //get all events with productiononline = 1 not already created in Asana
        // returns an array of event id's

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.production_online = true AND  (a.created_in_new_events_external is null OR a.created_in_new_events_external = 0) AND (a.created_in_new_events is null OR a.created_in_new_events = 0) AND (y.profile_id = :profileIdEkstern OR y.profile_id = :profileIdGratis)
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['profileIdEkstern' => $this->options['yesplan_external_profile_id'], 'profileIdGratis' => $this->options['yesplan_free_profile_id']]);

        return $stmt->fetchAll();
    }

    /**
     * Finds all events with eventOnline = 1, and profile only internal events.
     *
     * @return YesplanEvent[]
     */
    public function findNewEventOnlineEvents(): array
    {
        //get all events with eventonline = 1 not already created in Asana

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
       SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.event_online = true AND (a.created_in_new_events_online is null OR a.created_in_new_events_online = 0) AND y.profile_id = :profileId
           ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['profileId' => $this->options['yesplan_intern_profile_id']]);

        return $stmt->fetchAll();
    }

    /**
     * Finds all events with more than 90 % tiprofileIdInternckets sold
     * == less than 10% tickets are left.
     *
     * @return YesplanEvent[]
     */
    public function findFewTickets(): array
    {
        //(tixintegrations_ticketsavailable + tixintegrations_ticketsreserved) / (tixintegrations_capacity - tixintegrations_blocked - tixintegrations_allocated) * 100
        //<10%
        //Returns all evet ids of events not already in ASANA boards for few tickets, with capacitypercent < 10%

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.capacity_percent >= 90 AND  (a.created_in_few_tickets is null OR a.created_in_few_tickets = 0) AND y.profile_id = :profileId
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['profileId' => $this->options['yesplan_intern_profile_id']]);

        return $stmt->fetchAll();
    }

    /**
     * Finds all events with less than 75% tickets sold less than 3 weeks before the event
     * == more than 25% tickets left 3 weeks before event.
     *
     * @return YesplanEvent[]
     */
    public function findLastMinutTickets(): array
    {
        //(tixintegrations_ticketsavailable + tixintegrations_ticketsreserved) / (tixintegrations_capacity - tixintegrations_blocked - tixintegrations_allocated) * 100
        //<25% + 3 weeks before event
        $nowPlus3Weeks = new DateTime('NOW');
        $nowPlus3Weeks->add(new DateInterval('P21D'));

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT y.id FROM yesplan_event y LEFT JOIN asana_event a ON y.id=a.id WHERE y.capacity_percent <= 75 AND  (a.created_in_last_minute is null OR a.created_in_last_minute = 0) AND y.event_date <= :nowPlus3Weeks AND y.profile_id = :profileId
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['nowPlus3Weeks' => date_format($nowPlus3Weeks, 'Y-m-d H:i:s'), 'profileId' => $this->options['yesplan_intern_profile_id']]);

        return $stmt->fetchAll();
    }
}
