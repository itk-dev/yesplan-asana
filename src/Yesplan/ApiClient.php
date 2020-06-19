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
                        $events[$id]['id'] = $id;
                        $events[$id]['data'] = $data;
                        $events[$id]['title'] = $data['name'];
                        $events[$id]['location'] = '';

                        //if an event has multiple locations, this will only get the first

                        if (!empty($data['locations']['next'])) {
                            $events[$id]['location'] = $data['locations'][0]['name'];
                        }

                        $events[$id]['eventDate'] = $data['starttime'];

                        $events = array_merge_recursive($events, $this->getCustomData($id));
                        //  $events = $this->getCustomData($id);
                    }
                }
                if (!empty($result['pagination']['next'])) {
                    $url = $result['pagination']['next'];
                } else {
                    $url = null;
                }
            } elseif (Response::HTTP_TOO_MANY_REQUESTS === $response->getStatusCode()) {
                //if Yesplan receives to many requests, take a coffee break
                sleep(6);
            } else {
                $this->mailer->sendEmail('Error getting data', 'Error '.$response->getStatusCode().'URL: '.$url);
                $this->error('Error getting data', ['HTTPResponseCode' => $response->getStatusCode(), 'url' => $url]);
            }
        }

        return $events;
    }

    /**
     * Get customdata from Yesplan by eventID.
     *
     * @return YesplanEvent[]
     */
    private function getCustomData(string $id): array
    {
        $customData = [];
        $customDataUrl = 'api/event/'.$id.'/customdata';

        $customDataResponse = $this->get($customDataUrl, ['query' => ['api_key' => $this->options['apikey']]]);
        if (Response::HTTP_OK === $customDataResponse->getStatusCode()) {
            $customDataresult = $customDataResponse->toArray();

            foreach ($customDataresult['groups'] as $group) {
                $customData[$id]['marketing_budget'] = '';
                $customData[$id]['genre'] = '';
                $customData[$id]['publication_date'] = '';
                $customData[$id]['ticketinfo_sale'] = '';
                $customData[$id]['eventonline'] = '';
                $customData[$id]['productiononline'] = '';
                $customData[$id]['presale_date'] = '';
                $customData[$id]['ticketsavailable'] = '';
                $customData[$id]['ticketsreserved'] = '';
                $customData[$id]['capacity'] = '';
                $customData[$id]['blocked'] = '';
                $customData[$id]['allocated'] = '';

                //Offentliggørelses dato
                //I salg dato
                //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public
                if ('tix' === $group['keyword']) {
                    foreach ($group['children'] as $tix) {
                        if ('tix_tixobligatoriskefelter' === $tix['keyword']) {
                            foreach ($tix['children'] as $ticketPublic) {
                                if ('ticketinfo_public' === $ticketPublic['keyword']) {
                                    $customData[$id]['publication_date'] = $ticketPublic['value'];
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
                                    $customData[$id]['genre'] = $information['value'][0];
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
                                    $customData[$id]['marketing_budget'] = $externalExpenses['value'];
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
                                    $customData[$id]['publication_date'] = $ticketPublic['value'];
                                }
                            }
                        }

                        if ('tix_billetsalgtix' === $tix['keyword']) {
                            foreach ($tix['children'] as $billetsalg) {
                                if ('tixintegrations_productiononline' === $billetsalg['keyword']) {
                                    $customData[$id]['productiononline'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_eventonline' === $billetsalg['keyword']) {
                                    $customData[$id]['eventonline'] = $billetsalg['value'];
                                }
                                if ('tixintegration_ticketsavailable' === $billetsalg['keyword']) {
                                    $customData[$id]['ticketsavailable'] = $billetsalg['value'];
                                }
                                if ('tixintegration_ticketsreserved' === $billetsalg['keyword']) {
                                    $customData[$id]['ticketsreserved'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_capacity' === $billetsalg['keyword']) {
                                    $customData[$id]['capacity'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_blocked' === $billetsalg['keyword']) {
                                    $customData[$id]['blocked'] = $billetsalg['value'];
                                }
                                if ('tixintegrations_allocated' === $billetsalg['keyword']) {
                                    $customData[$id]['allocated'] = $billetsalg['value'];
                                }
                            }
                        }
                    }
                }
            }
        } elseif (Response::HTTP_TOO_MANY_REQUESTS === $customDataResponse->getStatusCode()) {
            //if Yesplan API receives to many requests, take a short break, and continue
            sleep(6);
            $this->getCustomData($id);
        } else {
            //something failed
            $this->mailer->sendEmail('Error getting customdata', 'Error '.$customDataResponse->getStatusCode().'URL: '.$customDataUrl.'ID: '.$id);
            $this->error('Error getting custom data', ['HTTPResponseCode' => $customDataResponse->getStatusCode(), 'id' => $id, 'url' => $customDataUrl]);
        }

        return $customData;
    }
}
