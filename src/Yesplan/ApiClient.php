<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Yesplan;

use App\Controller\MailerController;
use App\Traits\LoggerTrait;
use DateInterval;
use Datetime;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClient
{
    use LoggerTrait;

    private $options;
    private $mailer;
    private $httpClient;

    public function __construct(array $yesplanApiClientOptions, LoggerInterface $logger, MailerController $mailer)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($yesplanApiClientOptions);
        $this->setLogger($logger);
        $this->mailer = $mailer;
    }

    /**
     * Resolve env variables.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'apikey',
            'url',
        ]);
    }

    public function get(string $path, array $options): ResponseInterface
    {
        return $this->request('GET', $path, $options);
    }

    protected function request(string $method, string $path, array $options): ResponseInterface
    {
        $this->httpClient = HttpClient::create(['base_uri' => $this->options['url']]);

        return $this->httpClient->request($method, $path, $options);
    }

    /**
     * Get all events from now + 10 years from Yesplan.
     */
    public function getEvents(): array
    {
        $this->info('Get events running');
        $events = [];

        $timeNow = new DateTime();
        $time10Years = (new DateTime())->add(new DateInterval('P10Y'));

        $dateString = $timeNow->format('d-m-Y').'%20TO%20'.$time10Years->format('d-m-Y');

        $url = 'api/events/event%3Adate%3A'.$dateString;
        while (null !== $url) {
            $response = $this->get($url, ['query' => ['api_key' => $this->options['apikey']]]);

            if (Response::HTTP_OK === $response->getStatusCode()) {
                $result = $response->toArray();

                foreach ($result['data'] as $data) {
                    if (!empty($data['id'])) {
                        $id = $data['id'];
                        $event = [
                            'id' => $id,
                            'data' => $data,
                            'title' => $data['name'],
                             //if an event has multiple locations, this will only get the first
                            'location' => $data['locations'][0]['name'] ?? '',
                            'eventDate' => $data['starttime'],
                        ];

                        $this->loadCustomData($event);

                        $events[$id] = $event;
                    }
                }
                $url = $result['pagination']['next'] ?? null;
            } elseif (Response::HTTP_TOO_MANY_REQUESTS === $response->getStatusCode()) {
                //if Yesplan receives to many requests, take a coffee break
                //@TODO
                sleep(6);
            } else {
                $this->mailer->sendEmail('Error getting data', 'Error '.$response->getStatusCode().'URL: '.$url);
                $this->error('Error getting data', ['HTTPResponseCode' => $response->getStatusCode(), 'url' => $url]);
            }
        }

        return $events;
    }

    /**
     * Load customdata from Yesplan by eventID.
     */
    private function loadCustomData(array &$event)
    {
        $customDataUrl = 'api/event/'.$event['id'].'/customdata';

        $customDataResponse = $this->get($customDataUrl, ['query' => ['api_key' => $this->options['apikey']]]);
        if (Response::HTTP_OK === $customDataResponse->getStatusCode()) {
            $customDataResult = $customDataResponse->toArray();

            foreach ($customDataResult['groups'] as $group) {
                $event['marketing_budget'] = '';
                $event['genre'] = '';
                $event['publication_date'] = '';
                $event['ticketinfo_sale'] = '';
                $event['eventonline'] = '';
                $event['productiononline'] = '';
                $event['presale_date'] = '';
                $event['ticketsavailable'] = '';
                $event['ticketsreserved'] = '';
                $event['capacity'] = '';
                $event['blocked'] = '';
                $event['allocated'] = '';

                //Offentliggørelses dato
                //I salg dato
                //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public
                if ('tix' === $group['keyword']) {
                    foreach ($group['children'] as $tix) {
                        if ('tix_tixobligatoriskefelter' === $tix['keyword']) {
                            foreach ($tix['children'] as $ticketPublic) {
                                if ('ticketinfo_public' === $ticketPublic['keyword']) {
                                    $event['publication_date'] = $ticketPublic['value'];
                                }
                            }
                        }
                    }
                }
                //genre
                //groups -> generelinformation -> generelinformation_data -> eventinfo_web_categori
                if ('generelinformation' === $group['keyword']) {
                    foreach ($group['children'] as $generelInformation) {
                        if ('generelinformation_data' === $generelInformation['keyword']) {
                            foreach ($generelInformation['children'] as $information) {
                                if ('eventinfo_web_categori' === $information['keyword']) {
                                    $event['genre'] = $information['value'][0];
                                }
                            }
                        }
                    }
                }
                //marketing budget
                //groups -> bugdetudgifter -> bugdetudgifter_andreudgifterekstern -> expences_marketing
                if ('budgetudgifter' === $group['keyword']) {
                    foreach ($group['children'] as $budgetExpenses) {
                        if ('budgetudgifter_andreudgifterekstern' === $budgetExpenses['keyword']) {
                            foreach ($budgetExpenses['children'] as $externalExpenses) {
                                if ('expences_marketing' === $externalExpenses['keyword']) {
                                    $event['marketing_budget'] = $externalExpenses['value'];
                                    $event['marketing_budget'] = 'test';
                                }
                            }
                        }
                    }
                }

                //Offentliggørelses dato
                //I salg dato
                //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public

                if ('tix' === $group['keyword']) {
                    foreach ($group['children'] as $tix) {
                        if ('tix_tixobligatoriskefelter' === $tix['keyword']) {
                            foreach ($tix['children'] as $ticketPublic) {
                                if ('ticketinfo_sale' === $ticketPublic['keyword']) {
                                    $event['ticketinfo_sale'] = $ticketPublic['value'];
                                }
                                if ('publication_date' === $ticketPublic['keyword']) {
                                    $event['publication_date'] = $ticketPublic['value'];
                                }
                            }
                        }

                        if ('tix_billetsalgtix' === $tix['keyword']) {
                            foreach ($tix['children'] as $billetsalg) {
                                if ('tixintegrations_productiononline' === $billetsalg['keyword']) {
                                    $event['productiononline'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_eventonline' === $billetsalg['keyword']) {
                                    $event['eventonline'] = $billetsalg['value'];
                                }
                                if ('tixintegration_ticketsavailable' === $billetsalg['keyword']) {
                                    $event['ticketsavailable'] = $billetsalg['value'];
                                }
                                if ('tixintegration_ticketsreserved' === $billetsalg['keyword']) {
                                    $event['ticketsreserved'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_capacity' === $billetsalg['keyword']) {
                                    $event['capacity'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_blocked' === $billetsalg['keyword']) {
                                    $event['blocked'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_allocated' === $billetsalg['keyword']) {
                                    $event['allocated'] = $billetsalg['value'];
                                }
                            }
                        }
                    }
                }
            }
        } elseif (Response::HTTP_TOO_MANY_REQUESTS === $customDataResponse->getStatusCode()) {
            //if Yesplan API receives to many requests, take a short break, and continue
            //@TODO
            sleep(6);
            $this->loadCustomData($event);
        } else {
            //something failed
            $this->mailer->sendEmail('Error getting customdata', 'Error '.$customDataResponse->getStatusCode().'URL: '.$customDataUrl.'ID: '.$event['id']);
            $this->error('Error getting custom data', ['HTTPResponseCode' => $customDataResponse->getStatusCode(), 'id' => $event['id'], 'url' => $customDataUrl]);
        }
    }
}
