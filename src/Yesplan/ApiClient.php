<?php

namespace App\Yesplan;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

use Monolog\Logger as MonologLogger;


class ApiClient
{
    private $options;
    private $eventArray;
    private $logger;

    /** @var HttpClientInterface */
    private $httpClient;

    

    public function __construct(array $yesplanApiClientOptions, MonologLogger $logger)
    {

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($yesplanApiClientOptions);

        $this->httpClient = HttpClient::create(['base_uri' => $this->options['url']]);

        $this->eventArray = array();
        $this->logger = $logger;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'apikey',
            'url'

        ]);
    }
    public function get(string $path, array $options): ResponseInterface
    {
        return $this->request("GET", $path, $options);
    }
    protected function request(string $method, string $path, array $options): ResponseInterface
    {
        return $this->httpClient->request($method, $path, $options);
    }
    public function getEvents(): array
    {
        $this->logger->info('get events running');

        //$client = HttpClient::create(['base_uri' => $this->options['url']]);

        $url = 'api/events/event%3Adate%3A29-04-2020%20TO%2020-04-2021';
        //  httpClient
        while ($url !== null) {

            $response = $this->get($url, ['query' => ['api_key' => $this->options['apikey']]]);


            if ($response->getStatusCode() === Response::HTTP_OK) { //http statuskode ok


                $responseJson = json_decode($response->getContent(), true);
                $id = "";
                $responseArray = $response->toArray();

                foreach ($responseArray["data"] as $data) {
                    if (!empty($data['id'])) {
                        $id = $data['id'];
                        $this->eventArray[$id]['id'] = $id;
                        $this->eventArray[$id]['data'] = $data;
                        $this->eventArray[$id]['title'] = $data["name"];
                        $this->eventArray[$id]['marketing_budget'] = "";
                        $this->eventArray[$id]['genre'] = "";
                        $this->eventArray[$id]['publication_date'] = "";
                        $this->eventArray[$id]['ticketinfo_sale'] = "";
                        $this->eventArray[$id]['eventonline'] = "";
                        $this->eventArray[$id]['productiononline'] = "";
                        $this->eventArray[$id]['presale_date'] = "";
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


                        $this->eventArray[$id]['eventDate'] = $data["starttime"];
                        /*
                       
                        */
                        $this->getCustomData($id);
                    }
                }
                if (!empty($responseArray["pagination"]["next"])) {
                    $url = $responseArray["pagination"]["next"];
                } else {
                    $url = null;
                }
            } else if ($response->getStatusCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                sleep(6);
            } else { 
                $this->logger->error('Error getting data', ['HTTPResponseCode' => $response->getStatusCode(), 'url' => $url]);
            }
        }
        return $this->eventArray;
    }
    private function getCustomData(string $id): void
    {
        $customDataUrl = 'api/event/' . $id . '/customdata';

        $customDataResponse = $this->get($customDataUrl, ['query' => ['api_key' => $this->options['apikey']]]);
        if ($customDataResponse->getStatusCode()  === Response::HTTP_OK) {

            $customDataResponseArray = $customDataResponse->toArray();

            foreach ($customDataResponseArray["groups"] as $group) {
                //Offentliggørelses dato
                //I salg dato
                //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public


                if ($group["keyword"] == "tix") {
                    foreach ($group["children"] as $tix) {
                        if ($tix["keyword"] == "tix_tixobligatoriskefelter") {
                            foreach ($tix["children"] as $ticketPublic) {
                                if ($ticketPublic["keyword"] == "ticketinfo_public") {
                                    $this->eventArray[$id]['publication_date'] = $ticketPublic["value"];
                                }
                            }
                        }
                    }
                }
                //genre
                //groups -> generelinformation -> generelinformation_data -> eventinfo_web_categori
                if ($group["keyword"] == "generelinformation") {
                    foreach ($group["children"] as $generelInformation) {
                        if ($generelInformation["keyword"] == "generelinformation_data") {
                            foreach ($generelInformation["children"] as $information) {
                                if ($information["keyword"] == "eventinfo_web_categori") {

                                    $this->eventArray[$id]['genre'] = $information["value"][0];
                                }
                            }
                        }
                    }
                }
                //marketing budget
                //groups -> bugdetudgifter -> bugdetudgifter_andreudgifterekstern -> expences_marketing
                if ($group["keyword"]  == "budgetudgifter") {
                    foreach ($group["children"] as $budgetExpenses) {
                        if ($budgetExpenses["keyword"] == "budgetudgifter_andreudgifterekstern") {
                            foreach ($budgetExpenses["children"] as $externalExpenses) {
                                if ($externalExpenses["keyword"] == "expences_marketing") {
                                    $this->eventArray[$id]['marketing_budget'] = $externalExpenses["value"];
                                }
                            }
                        }
                    }
                }

                //Offentliggørelses dato
                //I salg dato
                //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public


                if ($group["keyword"] == "tix") {

                    foreach ($group["children"] as $tix) {
                        if ($tix["keyword"] == "tix_tixobligatoriskefelter") {
                            foreach ($tix['children'] as $ticketPublic) {

                                if ($ticketPublic['keyword'] == 'ticketinfo_sale') {
                                    $this->eventArray[$id]['ticketinfo_sale'] = $ticketPublic["value"];
                                }
                                if ($ticketPublic['keyword'] == 'publication_date') {
                                    $this->eventArray[$id]['publication_date'] = $ticketPublic['value'];
                                }
                            }
                        }

                        if ($tix['keyword'] == 'tix_billetsalgtix') {

                            foreach ($tix['children'] as $billetsalg) {
                                if ($billetsalg['keyword'] == 'tixintegrations_productiononline') {
                                    $this->eventArray[$id]['productiononline'] = $billetsalg["value"];
                                }
                                if ($billetsalg['keyword'] == 'tixintegrations_eventonline') {
                                    $this->eventArray[$id]['eventonline'] = $billetsalg["value"];
                                }
                                if ($billetsalg['keyword'] == 'tixintegration_ticketsavailable') {
                                    $this->eventArray[$id]['ticketsavailable'] = $billetsalg["value"];
                                }
                                if ($billetsalg['keyword'] == 'tixintegration_ticketsreserved') {
                                    $this->eventArray[$id]['ticketsreserved'] = $billetsalg["value"];
                                }
                                if ($billetsalg['keyword'] == 'tixintegrations_capacity') {
                                    $this->eventArray[$id]['capacity'] = $billetsalg["value"];
                                }
                                if ($billetsalg['keyword'] == 'tixintegrations_blocked') {
                                    $this->eventArray[$id]['blocked'] = $billetsalg["value"];
                                }
                                if ($billetsalg['keyword'] == 'tixintegrations_allocated') {
                                    $this->eventArray[$id]['allocated'] = $billetsalg["value"];
                                }
                            }
                        }
                    }
                }
            }
        } else if ($customDataResponse->getStatusCode()  === Response::HTTP_TOO_MANY_REQUESTS) {
            sleep(6);
            $this->getCustomData($id);
        } else {
            $this->logger->error('Error getting custom data', ['HTTPResponseCode' => $customDataResponse->getStatusCode(), 'id' => $id, 'url' => $customDataUrl]);
        }
    }
}
