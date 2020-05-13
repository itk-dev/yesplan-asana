<?php

namespace App\Asana;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AsanaApiClient
{
    private $options;
    //   private $asanaEventManager;
    private $eventDateFieldID = '1234567890';

    /** @var HttpClientInterface */
    private $httpClient;

    public function __construct(array $asanaApiClientOptions)
    {

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($asanaApiClientOptions);

        //   $this->asanaEventManager = $asanaEventManagers;

        // $this->httpClient = HttpClient::create(['base_uri' => $this->options['url']]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'bearer',
            'asana_url',
            'asana_new_event',
            'asana_new_event_online',
            'asana_last_minute',
            'asana_few_tickets'

        ]);
    }

    //new events
    public function createCardNewEventsBoard(string $name): void
    {
        $boards = explode(',', $this->options['asana_new_event']);
        foreach ($boards as $board) {
            $this->createCard($name, $board, 'test');
        }
    }

    //new events online
    public function createCardsEventOnline(string $name): void
    {
        
        $boards = explode(',', $this->options['asana_new_event_online']);
        foreach ($boards as $board) {
            $this->createCard($name, $board, 'test');
        }
        
    }

    //last minute events
    public function createCardLastMinute(string $name): void
    {
        $title = 'Last Minute: ' . $name;
        $boards = explode(',', $this->options['asana_last_minute']);
        foreach ($boards as $board) {
            $this->createCard($name, $board, 'test');
        }
    }

    //few events online
    public function createCartFewTickets(string $name): void
    {
        $title = 'Få billetter: ' . $name;
        $boards = explode(',', $this->options['asana_few_tickets']);
        foreach ($boards as $board) {
            $this->createCard($title, $board, 'test');
        }
    }

    public function createCardEvent(string $name): void
    {
        $this->createCard($name, '1172168404311497', 'test');
    }

    //Create card in Asana
    public function createCard(string $name, string $projectId, string $eventDate): void
    {
        $bearer = $this->options['bearer'];
        $url = $this->options['asana_url'];

        $client = HttpClient::create(['headers' => ['Authorization' => 'Bearer ' . $bearer]]);
        $client->request('POST', $url, [
            'query' => [
                'name' => $name,
                'custom_fields' => [
                    $this->eventDateFieldID => $eventDate
                ],
                'projects' => $projectId
            ]
        ]);
    }
}
