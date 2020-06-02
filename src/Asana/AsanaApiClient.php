<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Asana;

use App\Controller\MailerController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AsanaApiClient
{
    private $options;
    //   private $asanaEventManager;
    private $eventDateFieldID = '1234567890';
    private $mailer;
    private $logger;
    /** @var HttpClientInterface */
    private $httpClient;

    public function __construct(array $asanaApiClientOptions, MailerController $mailer, LoggerInterface $logger)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($asanaApiClientOptions);
        $this->mailer = $mailer;
        $this->logger = $logger;

        $bearer = $this->options['bearer'];

        $this->httpClient = HttpClient::create(['headers' => ['Authorization' => 'Bearer '.$bearer]]);

        //   $this->asanaEventManager = $asanaEventManagers;

        // $this->httpClient = HttpClient::create(['base_uri' => $this->options['url']]);
    }

    public function post(string $path, array $options): ResponseInterface
    {
        return $this->request('POST', $path, $options);
    }

    protected function request(string $method, string $path, array $options): ResponseInterface
    {
        //  print_r($options);
        return $this->httpClient->request($method, $path, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'bearer',
            'asana_url',
            'asana_new_event',
            'asana_new_event_online',
            'asana_last_minute',
            'asana_few_tickets',
            'yesplan_id',
            'yesplan_eventDate',
            'yesplan_location',
            'yesplan_genre',
            'yesplan_marketingBudget',
            'yesplan_publicationDate',
            'yesplan_presaleDate',
            'yesplan_insaleDate',
            'yesplan_percent',
        ]);
    }

    //new events
    public function createCardNewEventsBoard(array $values): void
    {
        $boards = explode(',', $this->options['asana_new_event']);
        foreach ($boards as $board) {
            $this->createCard($board, $values);
        }
    }

    //new events online
    public function createCardsEventOnline(array $values): void
    {
        $boards = explode(',', $this->options['asana_new_event_online']);
        foreach ($boards as $board) {
            $this->createCard($board, $values);
        }
    }

    //last minute events
    public function createCardLastMinute(array $values): void
    {
        $title = 'Last Minute: '.$values['titel'];
        $boards = explode(',', $this->options['asana_last_minute']);
        foreach ($boards as $board) {
            $this->createCard($board, $values);
        }
    }

    //few events online
    public function createCartFewTickets(array $values): void
    {
        $title = 'Få billetter: '.$values['titel'];
        $boards = explode(',', $this->options['asana_few_tickets']);
        foreach ($boards as $board) {
            $this->createCard($board, $values);
        }
    }

    //Create card in Asana
    public function createCard(string $projectId, array $values): void
    {
        $publicationDate = '';
        $eventDate = '';
        $presaleDate = '';
        $insaleDate = '';
        if (!empty($values['publicationdate'])) {
            $publicationDate = $values['publicationdate']->format('Y-m-d H:i:s');
        }
        if (!empty($values['eventdate'])) {
            $eventDate = $values['eventdate']->format('Y-m-d H:i:s');
        }
        if (!empty($values['presaleDate'])) {
            $presaleDate = $values['presaleDate']->format('Y-m-d H:i:s');
        }
        if (!empty($values['insaleDate'])) {
            $insaleDate = $values['insaleDate']->format('Y-m-d H:i:s');
        }

        $url = $this->options['asana_url'];
        $options = [
            'body' => [
                'name' => $values['titel'],
                'custom_fields'.'['.$this->options['yesplan_id'].']' => $values['id'],
                'custom_fields'.'['.$this->options['yesplan_eventDate'].']' => $eventDate,
                'custom_fields'.'['.$this->options['yesplan_location'].']' => $values['location'],
                'custom_fields'.'['.$this->options['yesplan_genre'].']' => $values['genre'],
                'custom_fields'.'['.$this->options['yesplan_marketingBudget'].']' => $values['marketingBudget'],

                'custom_fields'.'['.$this->options['yesplan_publicationDate'].']' => $publicationDate,
                'custom_fields'.'['.$this->options['yesplan_presaleDate'].']' => $presaleDate,
                'custom_fields'.'['.$this->options['yesplan_insaleDate'].']' => $insaleDate,
                'custom_fields'.'['.$this->options['yesplan_percent'].']' => $values['percent'],

                'projects' => $projectId,
            ],
        ];

        // print_r($values['eventdate']->format('Y-m-d H:i:s'));
        $response = $this->post($url, $options);

        if (!(Response::HTTP_CREATED === $response->getStatusCode())) {
            //   $this->mailer->sendEmail('lilosti@aarhus.dk', 'Error creating card', 'Error ' . $response->getStatusCode() . 'URL: ' . $url . 'projectID: ' . $projectId);
            print_r($options);
            $this->logger->error('Card not created statuscode yesplan_id: '.$response->getStatusCode().' '.$response->getContent(false).' '.$values['id']);
        } else {
            $this->logger->debug('Card created yesplan_id: '.$this->options['yesplan_id'].'___'.$values['id']);
        }
    }
}
