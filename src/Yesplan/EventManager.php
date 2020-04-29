<?php

namespace App\Yesplan;

use App\Entity\YesplanEvent;
use App\Repository\YesplanEventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class EventManager
{
    private $apiClient;
    private $eventRepository;
    private $entityManager;

    public function __construct(ApiClient $apiClient, YesplanEventRepository $eventRepository, EntityManagerInterface $entityManager)
    {
        $this->apiClient = $apiClient;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
    }

    public function updateEvents(): void
    {
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

    public function deleteOldEvents():void
    {
        $events = $this->eventRepository->findOldEvents();

        foreach($events as $event){
            $this->entityManager->remove($event);
            print_r($event->getId());
        }
        $this->entityManager->flush();
      
    }
}
