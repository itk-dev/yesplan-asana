<?php

namespace App\Asana;

use App\Entity\YesplanEvent;
use App\Repository\YesplanEventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Asana\AsanaApiClient;
use App\Entity\AsanaEvent;
use App\Repository\AsanaEventRepository;

class AsanaEventManager
{
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



    public function createCards(): void
    {
        $lastMinutEvents = $this->eventRepository->findLastMinutTickets();
        $fewTicketEvents = $this->eventRepository->findFewTickets();
        $eventsOnlineEvents = $this->eventRepository->findNewEventOnlineEvents();
        $eventsNewEvents = $this->eventRepository->findNewProductionOnlineEvents();

        foreach ($lastMinutEvents as $lastMinuteEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($lastMinuteEvent['id']));
            $this->asanaApiClient->createCardLastMinute($eventData['titel'], $lastMinuteEvent['id'], $eventData['eventdate'], $eventData['location'], $eventData['genre'], $eventData['marketingBudget'], $eventData['publicationdate'], $eventData['presaleDate'], $eventData['insaleDate']);
            $this->cardCreated($lastMinuteEvent['id'], 'LastMinute');
        }


        foreach ($fewTicketEvents as $fewTicketEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($fewTicketEvent['id']));
            $this->asanaApiClient->createCartFewTickets($eventData['titel'], $fewTicketEvent['id'], $eventData['eventdate'], $eventData['location'], $eventData['genre'], $eventData['marketingBudget'], $eventData['publicationdate'], $eventData['presaleDate'], $eventData['insaleDate']);
            $this->cardCreated($fewTicketEvent['id'], 'FewTickets');
        }
        

        foreach ($eventsOnlineEvents as $eventsOnlineEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($eventsOnlineEvent['id']));
            $this->asanaApiClient->createCardsEventOnline($eventData['titel'], $eventsOnlineEvent['id'], $eventData['eventdate'], $eventData['location'], $eventData['genre'], $eventData['marketingBudget'], $eventData['publicationdate'], $eventData['presaleDate'], $eventData['insaleDate']);
            $this->cardCreated($eventsOnlineEvent['id'], 'EventsOnline');
        }

        foreach ($eventsNewEvents as $eventsNewEvent) {
            $eventData = $this->getEventData($this->eventRepository->find($eventsNewEvent['id']));
            $this->asanaApiClient->createCardNewEventsBoard($eventData['titel'], $eventsNewEvent['id'], $eventData['eventdate'], $eventData['location'], $eventData['genre'], $eventData['marketingBudget'], $eventData['publicationdate'], $eventData['presaleDate'], $eventData['insaleDate']);
            $this->cardCreated($eventsNewEvent['id'], 'Events');
        }
        
    }

    private function getEventData(YesplanEvent $event): array
    {

        $eventArray = [
            'titel' => $event->getTitle(),
            'eventdate' => $event->getEventDate(),
            'location' => $event->getLocation(),
            'genre' => $event->getGenre(),
            'marketingBudget' => $event->getMarketingBudget(),
            'publicationdate' => $event->getPublicationDate(),
            'presaleDate' => $event->getPresaleDate(),
            'insaleDate' => $event->getInSaleDate()
        ];



        return $eventArray;
    }
    private function cardCreated(string $id, string $type)
    {
        $card = $this->asanaEventRepository->find($id);
        if (null === $card) {
            //    echo $cardid;
            $card = new AsanaEvent();
            $card->setId($id);
            //    echo $card->getId();
        }
        switch ($type) {
            case 'LastMinute':
                $card->setCreatedInLastMinute(true);
                break;
            case 'FewTickets':
                $card->setCreatedInFewTickets(true);
                break;
            case 'EventsOnline':
                $card->setCreatedInNewEventsOnline(true);
                break;
            case 'Events':
                $card->setCreatedInNewEvents(true);
                break;
        }
        $this->entityManager->persist($card);

        $this->entityManager->flush();
    }
}
