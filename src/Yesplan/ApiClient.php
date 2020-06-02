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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClient
{
    private $options;
    private $eventArray;
    private $logger;
    private $mailer;

    /** @var HttpClientInterface */
    private $httpClient;

    public function __construct(array $yesplanApiClientOptions, LoggerInterface $logger, MailerController $mailer)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($yesplanApiClientOptions);

        $this->httpClient = HttpClient::create(['base_uri' => $this->options['url']]);

        $this->eventArray = [];
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

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
        return $this->httpClient->request($method, $path, $options);
    }

    public function getEvents(): array
    {
        $this->logger->info('get events running');

        //$client = HttpClient::create(['base_uri' => $this->options['url']]);

        $url = 'api/events/event%3Adate%3A29-04-2020%20TO%2020-04-2030';
        //  httpClient
        while (null !== $url) {
            $response = $this->get($url, ['query' => ['api_key' => $this->options['apikey']]]);

            if (Response::HTTP_OK === $response->getStatusCode()) { //http statuskode ok
                $responseJson = json_decode($response->getContent(), true);
                $id = '';
                $responseArray = $response->toArray();

                foreach ($responseArray['data'] as $data) {
                    if (!empty($data['id'])) {
                        $id = $data['id'];
                        $this->eventArray[$id]['id'] = $id;
                        $this->eventArray[$id]['data'] = $data;
                        $this->eventArray[$id]['title'] = $data['name'];
                        $this->eventArray[$id]['marketing_budget'] = '';
                        $this->eventArray[$id]['genre'] = '';
                        $this->eventArray[$id]['publication_date'] = '';
                        $this->eventArray[$id]['ticketinfo_sale'] = '';
                        $this->eventArray[$id]['eventonline'] = '';
                        $this->eventArray[$id]['productiononline'] = '';
                        $this->eventArray[$id]['presale_date'] = '';
                        $this->eventArray[$id]['location'] = '';
                        $this->eventArray[$id]['ticketsavailable'] = '';
                        $this->eventArray[$id]['ticketsreserved'] = '';
                        $this->eventArray[$id]['capacity'] = '';
                        $this->eventArray[$id]['blocked'] = '';
                        $this->eventArray[$id]['allocated'] = '';

                        //if an event has multiple locations, this will only get the first

                        if (!empty($data['locations']['next'])) {
                            $this->eventArray[$id]['location'] = $data['locations'][0]['name'];
                        }

                        $this->eventArray[$id]['eventDate'] = $data['starttime'];

                        $this->getCustomData($id);
                    }
                }
                if (!empty($responseArray['pagination']['next'])) {
                    $url = $responseArray['pagination']['next'];
                } else {
                    $url = null;
                }
            } elseif (Response::HTTP_TOO_MANY_REQUESTS === $response->getStatusCode()) {
                sleep(6);
            } else {
                $this->mailer->sendEmail('lilosti@aarhus.dk', 'Error getting data', 'Error '.$response->getStatusCode().'URL: '.$url);
                $this->logger->error('Error getting data', ['HTTPResponseCode' => $response->getStatusCode(), 'url' => $url]);
            }
        }

        return $this->eventArray;
    }

    private function getCustomData(string $id): void
    {
        $customDataUrl = 'api/event/'.$id.'/customdata';

        $customDataResponse = $this->get($customDataUrl, ['query' => ['api_key' => $this->options['apikey']]]);
        if (Response::HTTP_OK === $customDataResponse->getStatusCode()) {
            $customDataResponseArray = $customDataResponse->toArray();

            foreach ($customDataResponseArray['groups'] as $group) {
                //Offentliggørelses dato
                //I salg dato
                //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public

                if ('tix' === $group['keyword']) {
                    foreach ($group['children'] as $tix) {
                        if ('tix_tixobligatoriskefelter' === $tix['keyword']) {
                            foreach ($tix['children'] as $ticketPublic) {
                                if ('ticketinfo_public' === $ticketPublic['keyword']) {
                                    $this->eventArray[$id]['publication_date'] = $ticketPublic['value'];
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
                                    $this->eventArray[$id]['genre'] = $information['value'][0];
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
                                    $this->eventArray[$id]['marketing_budget'] = $externalExpenses['value'];
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
                                    $this->eventArray[$id]['ticketinfo_sale'] = $ticketPublic['value'];
                                }
                                if ('publication_date' === $ticketPublic['keyword']) {
                                    $this->eventArray[$id]['publication_date'] = $ticketPublic['value'];
                                }
                            }
                        }

                        if ('tix_billetsalgtix' === $tix['keyword']) {
                            foreach ($tix['children'] as $billetsalg) {
                                if ('tixintegrations_productiononline' === $billetsalg['keyword']) {
                                    $this->eventArray[$id]['productiononline'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_eventonline' === $billetsalg['keyword']) {
                                    $this->eventArray[$id]['eventonline'] = $billetsalg['value'];
                                }
                                if ('tixintegration_ticketsavailable' === $billetsalg['keyword']) {
                                    $this->eventArray[$id]['ticketsavailable'] = $billetsalg['value'];
                                }
                                if ('tixintegration_ticketsreserved' === $billetsalg['keyword']) {
                                    $this->eventArray[$id]['ticketsreserved'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_capacity' === $billetsalg['keyword']) {
                                    $this->eventArray[$id]['capacity'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_blocked' === $billetsalg['keyword']) {
                                    $this->eventArray[$id]['blocked'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_allocated' === $billetsalg['keyword']) {
                                    $this->eventArray[$id]['allocated'] = $billetsalg['value'];
                                }
                            }
                        }
                    }
                }
            }
        } elseif (Response::HTTP_TOO_MANY_REQUESTS === $customDataResponse->getStatusCode()) {
            sleep(6);
            $this->getCustomData($id);
        } else {
            $this->mailer->sendEmail('lilosti@aarhus.dk', 'Error getting customdata', 'Error '.$customDataResponse->getStatusCode().'URL: '.$customDataUrl.'ID: '.$id);
            $this->logger->error('Error getting custom data', ['HTTPResponseCode' => $customDataResponse->getStatusCode(), 'id' => $id, 'url' => $customDataUrl]);
        }
    }
}
