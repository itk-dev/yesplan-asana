<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Yesplan;

use App\Entity\YesplanEvent;
use App\Repository\YesplanEventRepository;
use App\Traits\LoggerTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class EventManager
{
    use LoggerTrait;

    private $apiClient;
    private $eventRepository;
    private $entityManager;

    public function __construct(ApiClient $apiClient, YesplanEventRepository $eventRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->apiClient = $apiClient;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->setLogger($logger);
    }

    /**
     * Create events from Yesplan in local database.
     */
    public function updateEvents(): void
    {
        $this->info('update events');

        $events = $this->apiClient->getEvents();

        foreach ($events as $data) {
            $eventid = $data['id'];
            $event = $this->eventRepository->find($eventid);
            //event does not exist in database
            if (null === $event) {
                $event = new YesplanEvent();
                $event->setId($eventid);
                $event->setIsNewEvent(true);
            }
            //event already exists in database
            else {
                //if any eventdates have been updated this will be set in the database for use by the celendar integration
                if (!empty($data['ticketinfo_sale']) && $event->getInSaleDate() !== $this->getDateTime($data['ticketinfo_sale'])) {
                    $event->setInSaleDateUpdated(true);
                }
                if (!empty($data['presale_date']) && $event->getPresaleDate() !== $this->getDateTime($data['presale_date'])) {
                    $event->setInPresaleDateUpdated(true);
                }
                if (!empty($data['eventDate']) && $event->getEventDate() !== $this->getDateTime($data['eventDate'])) {
                    $event->setEventDateUpdated(true);
                }
            }

            $event
                ->setData($data)
                ->setTitle($data['title'])
                ->setMarketingBudget($data['marketing_budget'])
                ->setGenre($data['genre'])
                ->setLocation($data['location'])
                ->setTicketCapacity((int) $data['capacity'])
                ->setTicketsAllocated((int) $data['allocated'])
                ->setTicketsBlocked((int) $data['blocked'])
                ->setTicketsReserved((int) $data['ticketsreserved'])
                ->setTicketsAvailable((int) $data['ticketsavailable'])
                ->setProductionOnline($data['productiononline'])
                ->setEventOnline($data['eventonline'])
                ->setStatus($data['status'])
                ->setStatusId($data['statusId'])
                ->setProfile($data['profile'])
                ->setProfileId($data['profileId']);

            $capacityPercentage = 0;

            //(tixintegrations_ticketsavailable + tixintegrations_ticketsreserved) / (tixintegrations_capacity - tixintegrations_blocked - tixintegrations_allocated) * 100
            if ($event->getTicketCapacity() - $event->getTicketsBlocked() - $event->getTicketsAllocated() > 0) {
                $capacityPercentage = 100 - ($event->getTicketsAvailable() + $event->getTicketsReserved()) / ($event->getTicketCapacity() - $event->getTicketsBlocked() - $event->getTicketsAllocated()) * 100;
            }
            $event->setCapacityPercent($capacityPercentage);

            //if date is not empty convert to datetime before setting the value
            if (!empty($data['publication_date'])) {
                $event->setPublicationDate($this->getDateTime($data['publication_date']));
            }
            //if date is not empty convert to datetime before setting the value
            if (!empty($data['presale_date'])) {
                $event->setPresaleDate($this->getDateTime($data['presale_date']));
            }

            //if date is not empty convert to datetime before setting the value
            if (!empty($data['ticketinfo_sale'])) {
                $event->setInSaleDate($this->getDateTime($data['ticketinfo_sale']));
            }

            //if date is not empty convert to datetime before setting the value
            if (!empty($data['eventDate'])) {
                $event->setEventDate($this->getDateTime($data['eventDate']));
            }
            $this->entityManager->persist($event);
        }
        $this->entityManager->flush();
    }

    /**
     * Delete old events from local database.
     */
    public function deleteOldEvents(): void
    {
        $this->info('Deleting events');

        $events = $this->eventRepository->findOldEvents();

        foreach ($events as $event) {
            $this->entityManager->remove($event);
        }
        $this->entityManager->flush();
    }

    /**
     * Get datetime from string - log conversion errors.
     */
    private function getDateTime(string $dateTimeString)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d+', $dateTimeString);
        if (!$dateTime) {
            $dateTime = DateTime::createFromFormat('G:i', $dateTimeString);
        }
        if (!$dateTime) {
            $this->error('DateConversion failed {date}', ['date' => $dateTimeString, 'formatError' => DateTime::getLastErrors()]);
            $dateTime = null;
        }

        return $dateTime;
    }
}
