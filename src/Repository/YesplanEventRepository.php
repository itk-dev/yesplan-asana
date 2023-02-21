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
     * @return array[]
     */
    public function findOldEvents(): array
    {
        $entityManager = $this->getEntityManager();
        $timeNow = new \DateTimeImmutable('NOW');

        $query = $entityManager->createQuery(
            'SELECT e
             FROM App\Entity\YesplanEvent e
             WHERE e.eventDate < :timeNow'
        )->setParameter('timeNow', $timeNow->format('Y-m-d H:i:s:'));
        return $query->getResult();
    }

    /**
     * Finds all event id's with productionOnline = false and profile only internal events.
     *
     * @return array[]
     */
    public function findNewProductionOnlineEvents(): array
    {
        // get all events id's with productiononline = false not already created in Asana
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT y.id
             FROM App\Entity\YesplanEvent y
             LEFT JOIN App\Entity\AsanaEvent a WITH y.id=a.id
             WHERE y.productionOnline = true AND  (a.createdInNewEvents is null OR a.createdInNewEvents = false)
             AND y.profileId = :profileIdIntern
            '
        )->setParameter('profileIdIntern', $this->options['yesplan_intern_profile_id']);

        return $query->getResult();
    }

    /**
     * Finds all events with id's productionOnline = true and profile extern, intern and free events.
     *
     * @return array[]
     */
    public function findNewProductionOnlineIncludingGratisandExternEvents(): array
    {
        // get all events with productiononline = true not already created in Asana

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT y.id
             FROM App\Entity\YesplanEvent y
             LEFT JOIN App\Entity\AsanaEvent a WITH y.id=a.id
             WHERE y.productionOnline = true
             AND  (a.createdInNewEvents is null OR a.createdInNewEvents = false)
             AND (a.createdInNewEvents is null OR a.createdInNewEvents = false)
             AND (y.profileId = :profileIdEkstern OR y.profileId = :profileIdGratis)
            '
        )->setParameters(['profileIdEkstern' => $this->options['yesplan_external_profile_id'], 'profileIdGratis' => $this->options['yesplan_free_profile_id']]);
        return $query->getResult();
    }

    /**
     * Finds all events id's with eventOnline = true and profile only internal events.
     *
     * @return array[]
     */
    public function findNewEventOnlineEvents(): array
    {
        // get all events with eventonline = true not already created in Asana
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT y.id
             FROM App\Entity\YesplanEvent y
             LEFT JOIN App\Entity\AsanaEvent a
             WITH y.id=a.id
             WHERE y.eventOnline = true
             AND (a.createdInNewEventsOnline is null OR a.createdInNewEventsOnline = false)
             AND y.profileId = :profileId
            '
        )->setParameter('profileId', $this->options['yesplan_intern_profile_id']);
        return $query->getResult();
    }

    /**
     * Finds all id's with more than 90 % tiprofileIdInternckets sold
     * == less than 10% tickets are left.
     *
     * @return array[]
     */
    public function findFewTickets(): array
    {
        // (tixintegrations_ticketsavailable + tixintegrations_ticketsreserved) / (tixintegrations_capacity - tixintegrations_blocked - tixintegrations_allocated) * 100
        // <10%
        // Returns all evet id's of events not already in ASANA boards for few tickets, with capacitypercent < 10%

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT y.id
             FROM App\Entity\YesplanEvent y
             LEFT JOIN App\Entity\AsanaEvent a
             WITH y.id=a.id
             WHERE y.capacityPercent >= 90
             AND  (a.createdInFewTickets is null OR a.createdInFewTickets = false)
             AND y.profileId = :profileId
            '
        )->setParameter('profileId', $this->options['yesplan_intern_profile_id']);
        return $query->getResult();
    }

    /**
     * Finds all id's with less than 75% tickets sold less than 3 weeks before the event
     * == more than 25% tickets left 3 weeks before event.
     */
    public function findLastMinutTickets(): array
    {
        $nowPlus3Weeks = new \DateTimeImmutable('+3 weeks');
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT y.id
             FROM App\Entity\YesplanEvent y
             LEFT JOIN App\Entity\AsanaEvent a
             WITH y.id=a.id
             WHERE y.capacityPercent <= 75
             AND  (a.createdInLastMinute is null OR a.createdInLastMinute = false)
             AND y.eventDate <= :nowPlus3Weeks
             AND y.profileId = :profileId
            '
        )->setParameters(['nowPlus3Weeks' => date_format($nowPlus3Weeks, 'Y-m-d H:i:s'), 'profileId' => $this->options['yesplan_intern_profile_id']]);
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
                 FROM App\Entity\YesplanEvent y
                 LEFT JOIN App\Entity\AsanaEvent a
                 WITH y.id=a.id
                 WHERE ((a.createdInCalendar is null OR a.createdInCalendar = false)
                  OR y.inSaleDateUpdated = true
                  OR y.inPresaleDateUpdated = true
                  OR y.eventDateUpdated = true)
                 AND y.profileId = :profileId
            '
        )->setParameter('profileId', $this->options['yesplan_intern_profile_id']);
        return $query->getResult();
    }
}
