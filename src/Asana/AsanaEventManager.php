<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Asana;

use App\Entity\AsanaEvent;
use App\Entity\YesplanEvent;
use App\Repository\AsanaEventRepository;
use App\Repository\YesplanEventRepository;
use Doctrine\ORM\EntityManagerInterface;

class AsanaEventManager
{
    private const LAST_MINUTE = 'LastMinute';
    private const FEW_TICKETS = 'FewTickets';
    private const EVENTS_ONLINE = 'EventsOnline';
    private const EVENTS = 'Events';
    private const EVENTS_EXTERN = 'EventsExtern';
    private const CALENDAR_EVENTS = 'CalendarEvents';
    private $asanaApiClient;
    private $eventRepository;
    private $entityManager;
    private $asanaEventRepository;

    public function __construct(AsanaApiClient $asanaApiClient, EntityManagerInterface $entityManager, YesplanEventRepository $eventRepository, AsanaEventRepository $asanaEventRepository)
    {
        $this->asanaApiClient = $asanaApiClient;
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->asanaEventRepository = $asanaEventRepository;
    }

    /**
     * Create cards on boards depending on the events in yesplanEvents table
     * After creatioon AsanaEvent table is updated with information on where the card has been created.
     */
    public function createCards(): void
    {
        // get Yesplan events for the different boards/card types
        $lastMinutEvents = $this->eventRepository->findLastMinutTickets();
        $fewTicketEvents = $this->eventRepository->findFewTickets();
        $eventsOnlineEvents = $this->eventRepository->findNewEventOnlineEvents();
        $eventsNewEvents = $this->eventRepository->findNewProductionOnlineEvents();
        $eventsNewEventsExternal = $this->eventRepository->findNewProductionOnlineIncludingGratisandExternEvents();
        $calendarEvents = $this->eventRepository->findCalendarEvents();

        // create the cards, and update asanaEvent table
        foreach ($lastMinutEvents as $lastMinuteEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($lastMinuteEvent['id']));
            $this->asanaApiClient->createCardLastMinute($eventData);
            $this->cardCreated($lastMinuteEvent['id'], self::LAST_MINUTE);
        }

        foreach ($fewTicketEvents as $fewTicketEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($fewTicketEvent['id']));
            $this->asanaApiClient->createCartFewTickets($eventData);
            $this->cardCreated($fewTicketEvent['id'], self::FEW_TICKETS);
        }

        foreach ($eventsOnlineEvents as $eventsOnlineEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($eventsOnlineEvent['id']));
            $this->asanaApiClient->createCardsEventOnline($eventData);
            $this->cardCreated($eventsOnlineEvent['id'], self::EVENTS_ONLINE);
        }

        foreach ($eventsNewEvents as $eventsNewEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($eventsNewEvent['id']));
            $this->asanaApiClient->createCardNewEventsBoard($eventData);
            $this->cardCreated($eventsNewEvent['id'], self::EVENTS);
        }

        foreach ($eventsNewEventsExternal as $eventsNewEventExternal) {
            $eventData = $this->getEventData($this->eventRepository->find($eventsNewEventExternal['id']));
            $this->asanaApiClient->createCardNewEventsGratisandExternBoard($eventData);
            $this->cardCreated($eventsNewEventExternal['id'], self::EVENTS_EXTERN);
        }

        foreach ($calendarEvents as $calendarEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($calendarEvent['id']));
            $this->asanaApiClient->createCardCalendarEvent($eventData);
            $this->cardCreated($calendarEvent['id'], self::CALENDAR_EVENTS);
        }
    }

    /**
     * returns an array containing neccessary event data for 1 event.
     */
    private function getEventData(YesplanEvent $event): array
    {
        $eventData = [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'eventdate' => $event->getEventDate(),
            'location' => $event->getLocation(),
            'genre' => $event->getGenre(),
            'marketingBudget' => $event->getMarketingBudget(),
            'publicationdate' => $event->getPublicationDate(),
            'presaleDate' => $event->getPresaleDate(),
            'insaleDate' => $event->getInSaleDate(),
            'percent' => $event->getCapacityPercent(),
            'profile' => $event->getProfile(),
            'profileId' => $event->getProfileId(),
            'status' => $event->getStatus(),
            'statusId' => $event->getStatusId(),
            'inSaleDateUpdated' => $event->getInSaleDateUpdated(),
            'inPresaleDateUpdated' => $event->getInPresaleDateUpdated(),
            'eventDateUpdated' => $event->getEventDateUpdated(),
            'isNewEvent' => $event->getIsNewEvent(),
        ];

        return $eventData;
    }

    /**
     * Updates database when a new card has been created.
     *
     * @param id yesplan-id of the event created
     * @param type the type of card created - LastMinute, FewTickets, EventsOnline or Events
     */
    private function cardCreated(string $id, string $type)
    {
        $card = $this->asanaEventRepository->find($id);
        if (null === $card) {
            $card = new AsanaEvent();
            $card->setId($id);
        }
        switch ($type) {
            case self::LAST_MINUTE:
                $card->setCreatedInLastMinute(true);
                break;
            case self::FEW_TICKETS:
                $card->setCreatedInFewTickets(true);
                break;
            case self::EVENTS_ONLINE:
                $card->setCreatedInNewEventsOnline(true);
                break;
            case self::EVENTS:
                $card->setCreatedInNewEvents(true);
                break;
            case self::EVENTS_EXTERN:
                $card->setCreatedInNewEventsExternal(true);
                break;
            case self::CALENDAR_EVENTS:
                $card->setCreatedInCalendar(true);
                $yesplanevent = $this->eventRepository->find($id);
                if (null !== $yesplanevent) {
                    $yesplanevent->setInSaleDateUpdated(false);
                    $yesplanevent->setEventDateUpdated(false);
                    $yesplanevent->setInPresaleDateUpdated(false);
                    $yesplanevent->setIsNewEvent(false);
                }
        }
        $this->entityManager->persist($card);

        $this->entityManager->flush();
    }
}
