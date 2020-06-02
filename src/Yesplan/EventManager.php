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
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class EventManager
{
    private $apiClient;
    private $eventRepository;
    private $entityManager;
    private $logger;

    public function __construct(ApiClient $apiClient, YesplanEventRepository $eventRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->apiClient = $apiClient;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function updateEvents(): void
    {
        $this->logger->info('update events');

        $events = $this->apiClient->getEvents();
        //   echo 'updateEvents() count: ' . count($events);
        foreach ($events as $data) {
            $eventid = $data['id'];
            $event = $this->eventRepository->find($eventid);
            if (null === $event) {
                $event = new YesplanEvent();
                $event->setId($eventid);
            }

            $event->setData($data);
            $event->setTitle($data['title']);

            $event->setMarketingBudget($data['marketing_budget']);

            $event->setGenre($data['genre']);

            $event->setLocation($data['location']);

            $event->setTicketCapacity((int) ($data['capacity']));
            $event->setTicketsAllocated((int) ($data['allocated']));
            $event->setTicketsBlocked((int) ($data['blocked']));
            $event->setTicketsReserved((int) ($data['ticketsreserved']));
            $event->setTicketsAvailable((int) ($data['ticketsavailable']));

            $event->setProductionOnline($data['productiononline']);
            $event->setEventOnline($data['eventonline']);

            $calculateCapacityPercentage = 0;

            //(tixintegrations_ticketsavailable + tixintegrations_ticketsreserved) / (tixintegrations_capacity - tixintegrations_blocked - tixintegrations_allocated) * 100
            if ($event->getTicketCapacity() - $event->getTicketsBlocked() - $event->getTicketsAllocated() > 0) {
                $calculateCapacityPercentage = ($event->getTicketsAvailable() + $event->getTicketsReserved()) / ($event->getTicketCapacity() - $event->getTicketsBlocked() - $event->getTicketsAllocated()) * 100;
            }
            $event->setCapacityPercent($calculateCapacityPercentage);

            //if date is not empty convert to datetime before setting the value
            if (!empty($data['publication_date'])) {
                $publicationDate = DateTime::createFromFormat('Y-m-d\TG:i:se', $data['publication_date']);
                //dont add date if conversion fails - should be logged
                if ($publicationDate) {
                    $event->setPublicationDate($publicationDate);
                }
            }
            //if date is not empty convert to datetime before setting the value
            if (!empty($data['presale_date'])) {
                $presaleDate = DateTime::createFromFormat('Y-m-d\TG:i:se', $data['presale_date']);
                //dont add date if conversion fails - should be logged
                if ($presaleDate) {
                    $event->setPresaleDate($presaleDate);
                }
            }

            //if date is not empty convert to datetime before setting the value
            if (!empty($data['ticketinfo_sale'])) {
                $saleDate = DateTime::createFromFormat('Y-m-d\TG:i:se', $data['ticketinfo_sale']);
                //dont add date if conversion fails - should be logged
                if ($saleDate) {
                    $event->setInSaleDate($saleDate);
                }
            }

            //if date is not empty convert to datetime before setting the value
            if (!empty($data['eventDate'])) {
                $eventDate = DateTime::createFromFormat('Y-m-d\TG:i:se', $data['eventDate']);
                //2029-06-18T13:00:00+02:00
                //  echo 'trnsaformed' . $eventDate . '.' .  $data['eventDate'];
                //dont add date if conversion fails - should be logged
                if ($eventDate) {
                    $event->setEventDate($eventDate);
                }
            }
            // $event->setEventDate(DateTime::createFromFormat('Y-m-d\TH:i',$data['eventDate']));

            $this->entityManager->persist($event);
        }
        $this->entityManager->flush();
    }

    public function deleteOldEvents(): void
    {
        $this->logger->info('Deleting events');

        $events = $this->eventRepository->findOldEvents();

        foreach ($events as $event) {
            $this->entityManager->remove($event);
            //  print_r($event->getId());
        }
        $this->entityManager->flush();
    }
}
