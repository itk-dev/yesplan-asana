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
        $url = 'https://musikhusetaarhus.yesplan.be/api/events/date%3A%23next10years/customdata?api_key=53FD0F325B0AE34B5D620ADFE6879F2D';
        $eventArray = [];
        $events = $this->apiClient->getEvents($url, $eventArray);
        echo 'updateEvents() count: '.count($events);
        foreach ($events as $data) {
            $eventid = $data['id'];
            $event = $this->eventRepository->find($eventid);
            if (null === $event) {
                $event = new YesplanEvent();
                $event->setId($eventid);
            } else {
                //  echo $data['id'] . " ";
            }
            $event->setData($data);
            $event->setTitle($data['title']);

            $event->setMarketingBudget($data['marketing_budget']);
            //    $event->setLocation($data['location']);
            //   $event->setGenre($data['genre']);
            $event->setGenre($data['genre']);

            //     "value": "2020-05-01T12:00"
            //     if(empty($data['publication_date'])){
            //     $event->setPublicationDate(DateTime::createFromFormat("Y-m-d\TH:i",$data['publication_date']));
            //    }

            //if date is not empty convert to datetime before setting the value
            if (!empty($data['publication_date'])) {
                $publicationDate = DateTime::createFromFormat("Y-m-d\TH:i", $data['publication_date']);
                //dont add date if conversion fails - should be logged
                if ($publicationDate) {
                    $event->setPublicationDate($publicationDate);
                }
            }
            //if date is not empty convert to datetime before setting the value
            if (!empty($data['presale_date'])) {
                $presaleDate = DateTime::createFromFormat("Y-m-d\TH:i", $data['presale_date']);
                //dont add date if conversion fails - should be logged
                if ($presaleDate) {
                    $event->setPresaleDate($presaleDate);
                }
            }

            //if date is not empty convert to datetime before setting the value
            if (!empty($data['ticketinfo_sale'])) {
                $saleDate = DateTime::createFromFormat("Y-m-d\TH:i", $data['ticketinfo_sale']);
                //dont add date if conversion fails - should be logged
                if ($saleDate) {
                    $event->setInSaleDate($saleDate);
                }
            }

            // $event->setEventDate(DateTime::createFromFormat("Y-m-d\TH:i",$data['eventDate']));

            $this->entityManager->persist($event);
        }
        $this->entityManager->flush();
    }
}
