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
    private $options;

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
        $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
            'SELECT y.id
            FROM App\Entity\YesplanEvent y
            LEFT JOIN App\Entity\AsanaEvent a WITH y.id=a.id
            WHERE y.productionOnline = true AND  (a.createdInNewEvents is null OR a.createdInNewEvents = 0)
            AND y.profileId = :profileIdIntern
            '
        )->setParameter('profileIdIntern', $this->options['yesplan_intern_profile_id']);
        // returns an array of event id's
        return $query->getResult();

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

        $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
              'SELECT y.id
               FROM App\Entity\YesplanEvent y
               LEFT JOIN App\Entity\AsanaEvent a
               WHERE y.productionOnline = true
               AND  (a.createdInNewEvents is null OR a.createdInNewEvents = 0)
               AND (a.createdInNewEvents is null OR a.createdInNewEvents = 0)
               AND (y.profileId = :profileIdEkstern OR y.profileId = :profileIdGratis)
              '


        )->setParameters(['profileIdEkstern' => $this->options['yesplan_external_profile_id'], 'profileIdGratis' => $this->options['yesplan_free_profile_id']]);
        // returns an array of event id's
        return $query->getResult();

    }

    /**
     * Finds all events with eventOnline = 1, and profile only internal events.
     *
     * @return YesplanEvent[]
     */
    public function findNewEventOnlineEvents(): array
    {
        //get all events with eventonline = 1 not already created in Asana
        $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
            'SELECT y.id
            FROM App\Entity\YesplanEvent y
            LEFT JOIN App\Entity\AsanaEvent a
            WITH y.id=a.id
            WHERE y.eventOnline = true
            AND (a.createdInNewEventsOnline is null OR a.createdInNewEventsOnline = 0)
            AND y.profileId = :profileId
            '
        )->setParameter('profileId', $this->options['yesplan_intern_profile_id']);
        // returns an array of event id's
        return $query->getResult();

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

        $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
            'SELECT y.id
            FROM App\Entity\YesplanEvent y
            LEFT JOIN App\Entity\AsanaEvent a
            WITH y.id=a.id
            WHERE y.capacityPercent >= 90
            AND  (a.createdInFewTickets is null OR a.createdInFewTickets = 0)
            AND y.profileId = :profileId
            '
        )->setParameter('profileId' , $this->options['yesplan_intern_profile_id']);
        // returns an array of event id's
        return $query->getResult();


    }

    /**
     * Finds all events with less than 75% tickets sold less than 3 weeks before the event
     * == more than 25% tickets left 3 weeks before event.
     *
     */
    public function findLastMinutTickets(): array
    {
        $nowPlus3Weeks = new DateTime('NOW');
        $nowPlus3Weeks->add(new DateInterval('P21D'));
        $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
            'SELECT y.id
                FROM App\Entity\YesplanEvent y
                LEFT JOIN App\Entity\AsanaEvent a
                WITH y.id=a.id
                WHERE y.capacityPercent <= 75
                AND  (a.createdInLastMinute is null OR a.createdInLastMinute = 0)
                AND y.eventDate <= :nowPlus3Weeks
                AND y.profileId = :profileId

            '
        )->setParameters(['nowPlus3Weeks' => date_format($nowPlus3Weeks, 'Y-m-d H:i:s'), 'profileId' => $this->options['yesplan_intern_profile_id']]);
        // returns an array of event id's
        return $query->getResult();

    }

    /**
     * Finds all events not yet created in the calendar, or events with updated dates, with internal profileID.
     */
    public function findCalendarEvents(): array
    {

        $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
            'SELECT y.id
            FROM yesplan_event y
            LEFT JOIN asana_event a
            WITH y.id=a.id
           WHERE ((a.created_in_calendar is null OR a.created_in_calendar = 0)
                  OR y.in_sale_date_updated = 1
                  OR y.in_presale_date_updated = 1
                  OR y.event_date_updated = 1)
             AND y.profileId = :profileId
            '
        )->setParameter('profileId' , $this->options['yesplan_intern_profile_id']);
        // returns an array of event id's
        return $query->getResult();

    }
}
