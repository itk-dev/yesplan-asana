<?php

namespace App\Yesplan;
use App\Entity\YesplanEvent;
use App\Repository\YesplanEventRepository;
use Doctrine\ORM\EntityManagerInterface;


class EventManager{

    private $apiClient;
    private $eventRepository;
    private $entityManager;

    public function __construct(ApiClient $apiClient, YesplanEventRepository $eventRepository, EntityManagerInterface $entityManager){
        $this->apiClient = $apiClient;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
    }

    public function updateEvents():void
    {
        $events = $this->apiClient->getEvents();
        foreach($events as $data){
            $eventid = $data['id'];
            $event = $this->eventRepository->find($eventid);
            if(null === $event){
                $event = new YesplanEvent();
                $event->setId($eventid);
            }
            $event->setData($data);
            $this->entityManager->persist($event);
        }
        $this->entityManager->flush();
    }
}