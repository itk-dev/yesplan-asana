<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Asana;

use App\Contracts\HttpClient\AsanaMockResponse;
use App\Controller\MailerController;
use App\Traits\LoggerTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AsanaApiClient
{
    use LoggerTrait;

    private $options;
    private $mailer;

    /** @var HttpClientInterface */
    private $httpClient;
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(array $asanaApiClientOptions, MailerController $mailer, LoggerInterface $logger)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($asanaApiClientOptions);
        $this->mailer = $mailer;
        $this->setLogger($logger);
    }

    public function post(string $path, array $options): ResponseInterface
    {
        if ($this->options['dry-run']) {
            return new AsanaMockResponse($path, $options);
        }

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
            'asana_external_event',
            'yesplan_id',
            'yesplan_eventDate',
            'yesplan_location',
            'yesplan_genre',
            'yesplan_marketingBudget',
            'yesplan_publicationDate',
            'yesplan_presaleDate',
            'yesplan_insaleDate',
            'yesplan_percent',
            'yesplan_status',
            'yesplan_profile',
            'asana_calendar',
            'asana_calendar_colorfield',
            'asana_calendar_colorfield_red',
            'asana_calendar_colorfield_green',
            'asana_calendar_colorfield_yellow',
        ]);

        $resolver->setDefault('dry-run', false);

        $resolver->setNormalizer('asana_new_event', function (Options $options, $value) {
            $value = explode(',', $value);

            return $value;
        });
        $resolver->setNormalizer('asana_new_event_online', function (Options $options, $value) {
            $value = explode(',', $value);

            return $value;
        });
        $resolver->setNormalizer('asana_last_minute', function (Options $options, $value) {
            $value = explode(',', $value);

            return $value;
        });
        $resolver->setNormalizer('asana_few_tickets', function (Options $options, $value) {
            $value = explode(',', $value);

            return $value;
        });
        $resolver->setNormalizer('asana_external_event', function (Options $options, $value) {
            $value = explode(',', $value);

            return $value;
        });
    }

    /**
     * Create cards on the boards in env var ASANA_NEW_EVENT.
     */
    public function createCardNewEventsBoard(array $values): void
    {
        foreach ($this->options['asana_new_event'] as $board) {
            $this->createCard($board, $values);
        }
    }

    public function createCardNewEventsGratisandExternBoard(array $values): void
    {
        foreach ($this->options['asana_external_event'] as $board) {
            $this->createCard($board, $values);
        }
    }

    /**
     * Create cards on the boards in env var ASANA_NEW_EVENT_ONLINE.
     */
    public function createCardsEventOnline(array $values): void
    {
        foreach ($this->options['asana_new_event_online'] as $board) {
            $this->createCard($board, $values);
        }
    }

    /**
     * Create cards on the boards in env var ASANA_LAST_MINUTE, and prefix the card name with "Last Minute ".
     */
    public function createCardLastMinute(array $values): void
    {
        $values['title'] = 'Last Minute: '.$values['title'];
        foreach ($this->options['asana_last_minute'] as $board) {
            $this->createCard($board, $values);
        }
    }

    /**
     * Create cards on the boards in env var ASANA_FEW_TICKETS, and prefix the card name with "Få billetter ".
     */
    public function createCartFewTickets(array $values): void
    {
        $values['title'] = 'Få billetter: '.$values['title'];
        foreach ($this->options['asana_few_tickets'] as $board) {
            $this->createCard($board, $values);
        }
    }

    public function createCardCalendarEvent(array $values): void
    {
        $this->createCalendarCard($this->options['asana_calendar'], $values);
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
                'name' => $values['title'],
                'custom_fields'.'['.$this->options['yesplan_id'].']' => $values['id'],
                'custom_fields'.'['.$this->options['yesplan_eventDate'].']' => $eventDate,
                'custom_fields'.'['.$this->options['yesplan_location'].']' => $values['location'],
                'custom_fields'.'['.$this->options['yesplan_genre'].']' => $values['genre'],
                'custom_fields'.'['.$this->options['yesplan_marketingBudget'].']' => $values['marketingBudget'],
                'custom_fields'.'['.$this->options['yesplan_publicationDate'].']' => $publicationDate,
                'custom_fields'.'['.$this->options['yesplan_presaleDate'].']' => $presaleDate,
                'custom_fields'.'['.$this->options['yesplan_insaleDate'].']' => $insaleDate,
                'custom_fields'.'['.$this->options['yesplan_percent'].']' => $values['percent'],
                'custom_fields'.'['.$this->options['yesplan_status'].']' => $values['status'],
                'custom_fields'.'['.$this->options['yesplan_profile'].']' => $values['profile'],
                'projects' => $projectId,
            ],
        ];

        $response = $this->post($url, $options);

        if (!(Response::HTTP_CREATED === $response->getStatusCode())) {
            $this->mailer->sendEmail('Error creating card', 'Error '.$response->getStatusCode().'URL: '.$url.'projectID: '.$projectId);
            $this->error('Card not created {status_code}, response {response}', ['status_code' => $response->getStatusCode(), 'response' => $response]);
        } else {
            $this->debug('Card created yesplan_id: ', ['yesplan_id' => $this->options['yesplan_id']]);
        }
    }

    /**
     * Create calender card in asana using the ids for customfields put in the env, and on the calendarboard.
     *
     * @param projectID id of the board the card should be created on
     * @param values array containing information about the event created
     */
    public function createCalendarCard(string $projectId, array $values): void
    {
        $eventDate = !empty($values['eventdate']) ? $values['eventdate']->format(self::DATETIME_FORMAT) : '';
        $presaleDate = !empty($values['presaleDate']) ? $values['presaleDate']->format(self::DATETIME_FORMAT) : '';
        $insaleDate = !empty($values['insaleDate']) ? $values['insaleDate']->format(self::DATETIME_FORMAT) : '';

        $insaleDateUpdated = $values['inSaleDateUpdated'];
        $inPresaleDateUpdated = $values['inPresaleDateUpdated'];
        $eventDateUpdated = $values['eventDateUpdated'];
        $isNewEvent = $values['isNewEvent'];

        if ($eventDateUpdated || $isNewEvent) {
            //create green cards in calendar on event date
            $this->createCardWithColorCode($eventDate, $this->options['asana_calendar_colorfield_green'], $values, $projectId);
        }
        if ($insaleDateUpdated || $isNewEvent) {
            //create yellow cards in calendar on insale date
            $this->createCardWithColorCode($insaleDate, $this->options['asana_calendar_colorfield_yellow'], $values, $projectId);
        }
        if ($inPresaleDateUpdated || $isNewEvent) {
            //create red cards in calendar on presale date
            $this->createCardWithColorCode($presaleDate, $this->options['asana_calendar_colorfield_red'], $values, $projectId);
        }
    }

    /**
     * Create calender card in asana using the ids for customfields put in the env, and on the calendarboard, using different colorcodes, in the customfield for colorcodes.
     *
     * @param dueDate date you want the calendar card created on
     * @param colorCodeId asana id of the wanted coloroption from the colorcode field (taken from env)
     * @param projectID id of the board the card should be created on
     * @param values array containing information about the event created
     */
    private function createCardWithColorCode(string $dueDate, string $colorCodeId, array $values, string $projectId)
    {
        $publicationDate = !empty($values['publicationdate']) ? $values['publicationdate']->format(self::DATETIME_FORMAT) : '';
        $eventDate = !empty($values['eventdate']) ? $values['eventdate']->format(self::DATETIME_FORMAT) : '';
        $presaleDate = !empty($values['presaleDate']) ? $values['presaleDate']->format(self::DATETIME_FORMAT) : '';
        $insaleDate = !empty($values['insaleDate']) ? $values['insaleDate']->format(self::DATETIME_FORMAT) : '';

        $url = $this->options['asana_url'];
        if (!empty($dueDate)) {
            $options = [
            'body' => [
                'name' => $values['title'],
                'due_on' => $dueDate,
                'custom_fields'.'['.$this->options['asana_calendar_colorfield'].']' => $colorCodeId,
                'custom_fields'.'['.$this->options['yesplan_id'].']' => $values['id'],
                'custom_fields'.'['.$this->options['yesplan_eventDate'].']' => $eventDate,
                'custom_fields'.'['.$this->options['yesplan_location'].']' => $values['location'],
                'custom_fields'.'['.$this->options['yesplan_genre'].']' => $values['genre'],
                'custom_fields'.'['.$this->options['yesplan_marketingBudget'].']' => $values['marketingBudget'],
                'custom_fields'.'['.$this->options['yesplan_publicationDate'].']' => $publicationDate,
                'custom_fields'.'['.$this->options['yesplan_presaleDate'].']' => $presaleDate,
                'custom_fields'.'['.$this->options['yesplan_insaleDate'].']' => $insaleDate,
                'custom_fields'.'['.$this->options['yesplan_percent'].']' => $values['percent'],
                'custom_fields'.'['.$this->options['yesplan_status'].']' => $values['status'],
                'custom_fields'.'['.$this->options['yesplan_profile'].']' => $values['profile'],
                'projects' => $projectId,
            ],
        ];

            $response = $this->post($url, $options);

            if (!(Response::HTTP_CREATED === $response->getStatusCode())) {
                $this->mailer->sendEmail('Error creating card', 'Error '.$response->getStatusCode().'URL: '.$url.'projectID: '.$projectId);
                $this->error('Card not created {status_code}, response {response}', ['status_code' => $response->getStatusCode(), 'response' => $response]);
            } else {
                $this->debug('Card created yesplan_id: ', ['yesplan_id' => $this->options['yesplan_id']]);
            }
        }
    }
}
