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
<<<<<<< HEAD
        $events = $this->apiClient->getEvents();
        //   echo 'updateEvents() count: ' . count($events);
=======
        $url = 'https://musikhusetaarhus.yesplan.be/api/events/date%3A%23next10years/customdata?api_key=53FD0F325B0AE34B5D620ADFE6879F2D';
        $eventArray = [];
        $events = $this->apiClient->getEvents($url, $eventArray);
        echo 'updateEvents() count: '.count($events);
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
        foreach ($events as $data) {
            $eventid = $data['id'];
            $event = $this->eventRepository->find($eventid);
            if (null === $event) {
                $event = new YesplanEvent();
                $event->setId($eventid);
<<<<<<< HEAD
            }

=======
            } else {
                //  echo $data['id'] . " ";
            }
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
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
<<<<<<< HEAD
                $saleDate = DateTime::createFromFormat('Y-m-d\TG:i:se', $data['ticketinfo_sale']);
=======
                $saleDate = DateTime::createFromFormat("Y-m-d\TH:i", $data['ticketinfo_sale']);
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
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
}
