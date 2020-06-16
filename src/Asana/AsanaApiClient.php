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
use Symfony\Component\OptionsResolver\Options;


class AsanaApiClient
{
    private $options;
    private $mailer;
    private $logger;
    /** @var HttpClientInterface */
    private $httpClient;
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(array $asanaApiClientOptions, MailerController $mailer, LoggerInterface $logger)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($asanaApiClientOptions);
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function post(string $path, array $options): ResponseInterface
    {
        return $this->request('POST', $path, $options);
    }

    protected function request(string $method, string $path, array $options): ResponseInterface
    {
        if (null === $this->httpClient) {
            $this->httpClient = HttpClient::create(['headers' => ['Authorization' => 'Bearer '.$this->options['bearer']]]);
        }

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

    /**
     * Create cards on the boards in env var ASANA_NEW_EVENT.
     */
    public function createCardNewEventsBoard(array $values): void
    {
        $boards = explode(',', $this->options['asana_new_event']);
        foreach ($this->options['asana_new_event'] as $board) {
            $this->createCard($board, $values);
        }
    }

    /**
     * Create cards on the boards in env var ASANA_NEW_EVENT_ONLINE.
     */
    public function createCardsEventOnline(array $values): void
    {
        $boards = explode(',', $this->options['asana_new_event_online']);
        foreach ($boards as $board) {
            $this->createCard($board, $values);
        }
    }

    /**
     * Create cards on the boards in env var ASANA_LAST_MINUTE, and prefix the card name with "Last Minute ".
     */
    public function createCardLastMinute(array $values): void
    {
        $boards = explode(',', $this->options['asana_last_minute']);
        foreach ($boards as $board) {
            $this->createCard($board, $values);
        }
    }

    /**
     * Create cards on the boards in env var ASANA_FEW_TICKETS, and prefix the card name with "Få billetter ".
     */
    public function createCartFewTickets(array $values): void
    {
        $boards = explode(',', $this->options['asana_few_tickets']);
        foreach ($boards as $board) {
            $this->createCard($board, $values);
        }
    }

    /**
     * Creates card in asana using the ids for customfields put in the env.
     *
     * @param projectID id of the board the card should be created on
     * @param values array containing information about the event created
     */
    public function createCard(string $projectId, array $values): void
    {
        $publicationDate = !empty($values['publicationdate']) ? $values['publicationdate']->format(self::DATETIME_FORMAT) : '';
        $eventDate = !empty($values['eventdate']) ? $values['eventdate']->format(self::DATETIME_FORMAT) : '';
        $presaleDate = !empty($values['presaleDate']) ? $values['presaleDate']->format(self::DATETIME_FORMAT) : '';
        $insaleDate = !empty($values['insaleDate']) ? $values['insaleDate']->format(self::DATETIME_FORMAT) : '';

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

        $response = $this->post($url, $options);

        if (!(Response::HTTP_CREATED === $response->getStatusCode())) {
            $this->mailer->sendEmail('Error creating card', 'Error '.$response->getStatusCode().'URL: '.$url.'projectID: '.$projectId);
            $this->logger->error('Card not created {status_code}, response {response}', ['status_code' => $response->getStatusCode(), 'response' => $response]);

        } else {
            $this->logger->debug('Card created yesplan_id: ', ['yesplan_id' => $this->options['yesplan_id']]);
        }
    }
}
