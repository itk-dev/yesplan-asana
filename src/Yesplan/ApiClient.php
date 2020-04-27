<?php

namespace App\Yesplan;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClient
{
    private $options;
    private $eventArray;

    /** @var HttpClientInterface */
    private $httpClient;

    public function __construct(array $yesplanApiClientOptions)
    {

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($yesplanApiClientOptions);

        $this->httpClient = HttpClient::create(['base_uri' => $this->options['url']]);

        $this->eventArray = array();
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

        //$client = HttpClient::create(['base_uri' => $this->options['url']]);

        $url = "/api/events/date%3A%23next10years";
        //  httpClient
        while ($url !== null) {

            $response = $this->get($url, ['query' => ['api_key' => $this->options['apikey']]]);


            if ($response->getStatusCode() === Response::HTTP_OK) { //http statuskode ok


                $responseJson = json_decode($response->getContent(), true);
                $id = "";
                $responseArray = $response->toArray();


<<<<<<< HEAD

                foreach ($responseArray["data"] as $data) {
                    if (!empty($data["id"])) {
                        $id = $data["id"];
                        $this->eventArray[$id]['id'] = $id;
                        $this->eventArray[$id]['data'] = $data;
                        $this->eventArray[$id]['title'] = $data["name"];
                        $this->eventArray[$id]['marketing_budget'] = "";
                        $this->eventArray[$id]['genre'] = "";
                        $this->eventArray[$id]['publication_date'] = "";
                        $this->eventArray[$id]['ticketinfo_sale'] = "";
                        $this->eventArray[$id]['presale_date'] = "";

                        //if an event has multiple locations, this will only get the first
                        $this->eventArray[$id]['location'] = $data["locations"][0]["name"];

                        $this->eventArray[$id]['eventDate'] = $data["starttime"];
                        /*
                       
                        */
                    }
                }
            } else {
                echo $response->getStatusCode();
                echo $url;
                //       echo $response->getHeaders();
            }
            if (!empty($responseArray["pagination"]["next"])) {
                $url = $responseArray["pagination"]["next"];
            } else {
                $url = null;
=======
        if ('200' == $response->getStatusCode()) {
            $responseJson = json_decode($response->getContent(), true);
            $id = '';
            $responseArray = $response->toArray();

            if (isset($responseArray['pagination']['next'])) {
                sleep(6);
                $nextPageUrl = $responseArray['pagination']['next'].'&api_key=53FD0F325B0AE34B5D620ADFE6879F2D';
                $eventArray = array_merge($eventArray, $this->getEvents($nextPageUrl, $eventArray));
                //echo "page: " . $nextPageUrl;
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
            }
        }


        //    echo count($eventArray);
        //      print_r($eventArray);
        return $this->eventArray;
    }
    private function getCustomData(string $id): void
    {
        $customDataUrl = $id . "/customdata";

        $customDataResponse = $this->get($customDataUrl, ['query' => ['api_key' => $this->options['apikey']]]);

        if ($customDataResponse->getStatusCode() == "200") {
            $customDataResponseArray = $customDataResponse->toArray();

            foreach ($customDataResponseArray["groups"] as $group) {
                //Offentliggørelses dato
                //I salg dato
                //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public


<<<<<<< HEAD
                if ($group["keyword"] == "tix") {
                    foreach ($group["children"] as $tix) {
                        if ($tix["keyword"] == "tix_tixobligatoriskefelter") {
                            foreach ($tix["children"] as $ticketPublic) {
                                if ($ticketPublic["keyword"] == "ticketinfo_public") {
                                    $this->eventArray[$id]['publication_date'] = $ticketPublic["value"];
=======
            foreach ($responseArray['data'] as $data) {
                $id = $data['event']['id'];
                $eventArray[$id]['id'] = $id;
                $eventArray[$id]['data'] = $data;
                $eventArray[$id]['title'] = $data['event']['name'];
                $eventArray[$id]['marketing_budget'] = '';
                $eventArray[$id]['genre'] = '';
                $eventArray[$id]['publication_date'] = '';
                $eventArray[$id]['ticketinfo_sale'] = '';
                $eventArray[$id]['presale_date'] = '';

                $eventArray[$id]['location'] = '';
                $eventArray[$id]['eventDate'] = '';

                //$eventArray[$id]['location'] = $data["event"]["name"];
                $groups = $data['groups'];

                foreach ($groups as $group) {
                    $groupKeyword = $group['keyword'];
                    //marketing budget
                    //groups -> bugdetudgifter -> bugdetudgifter_andreudgifterekstern -> expences_marketing
                    if ('budgetudgifter' == $groupKeyword) {
                        foreach ($group['children'] as $budgetExpenses) {
                            if ('budgetudgifter_andreudgifterekstern' == $budgetExpenses['keyword']) {
                                foreach ($budgetExpenses['children'] as $externalExpenses) {
                                    if ('expences_marketing' == $externalExpenses['keyword']) {
                                        $eventArray[$id]['marketing_budget'] = $externalExpenses['value'];
                                    }
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
                                }
                            }
                        }
                    }
<<<<<<< HEAD
                }
                //genre
                //groups -> generelinformation -> generelinformation_data -> eventinfo_web_categori
                if ($group["keyword"] == "generelinformation") {
                    foreach ($group["children"] as $generelInformation) {
                        if ($generelInformation["keyword"] == "generelinformation_data") {
                            foreach ($generelInformation["children"] as $information) {
                                if ($information["keyword"] == "eventinfo_web_categori") {

                                    $this->eventArray[$id]['genre'] = $information["value"][0];
=======
                    //genre
                    //groups -> generelinformation -> generelinformation_data -> eventinfo_web_categori
                    if ('generelinformation' == $group['keyword']) {
                        foreach ($group['children'] as $generelInformation) {
                            if ('generelinformation_data' == $generelInformation['keyword']) {
                                foreach ($generelInformation['children'] as $information) {
                                    if ('eventinfo_web_categori' == $information['keyword']) {
                                        $eventArray[$id]['genre'] = $information['value'][0];
                                    }
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
                                }
                            }
                        }
                    }
<<<<<<< HEAD
                }
                //marketing budget
                //groups -> bugdetudgifter -> bugdetudgifter_andreudgifterekstern -> expences_marketing
                if ($group["keyword"]  == "budgetudgifter") {
                    foreach ($group["children"] as $budgetExpenses) {
                        if ($budgetExpenses["keyword"] == "budgetudgifter_andreudgifterekstern") {
                            foreach ($budgetExpenses["children"] as $externalExpenses) {
                                if ($externalExpenses["keyword"] == "expences_marketing") {
                                    $this->eventArray[$id]['marketing_budget'] = $externalExpenses["value"];
=======
                    //Offentliggørelses dato
                    //I salg dato
                    //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public
                    if ('tix' == $group['keyword']) {
                        foreach ($group['children'] as $tix) {
                            if ('tix_tixobligatoriskefelter' == $tix['keyword']) {
                                foreach ($tix['children'] as $ticketPublic) {
                                    if ('ticketinfo_public' == $ticketPublic['keyword']) {
                                        $eventArray[$id]['publication_date'] = $ticketPublic['value'];
                                    }
                                    if ('ticketinfo_sale' == $ticketPublic['keyword']) {
                                        $eventArray[$id]['ticketinfo_sale'] = $ticketPublic['value'];
                                    }
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
                                }
                            }
                        }
                    }
<<<<<<< HEAD
                }

                //Offentliggørelses dato
                //I salg dato
                //groups -> tix -> tix_tixobligatoriskefelter -> ticketinfo_public


                if ($group["keyword"] == "tix") {
                    foreach ($group["children"] as $tix) {
                        if ($tix["keyword"] == "tix_tixobligatoriskefelter") {
                            foreach ($tix["children"] as $ticketPublic) {

                                if ($ticketPublic["keyword"] == "ticketinfo_sale") {
                                    $this->eventArray[$id]['ticketinfo_sale'] = $ticketPublic["value"];
                                }
                                if ($ticketPublic["keyword"] == "publication_date") {
                                    $this->eventArray[$id]['publication_date'] = $ticketPublic["value"];
=======
                    //Presale dato + tid
                    //groups -> billetforhold -> billetforhold_billetinformation -> ticketinfo_presaledatetime

                    if ('billetforhold' == $group['keyword']) {
                        foreach ($group['children'] as $ticketinformation) {
                            if ('billetforhold_billetinformation' == $ticketinformation['keyword']) {
                                foreach ($ticketinformation['children'] as $ticketinfo) {
                                    if ('ticketinfo_presaledatetime' == $ticketinfo['keyword']) {
                                        $eventArray[$id]['presale_date'] = $ticketinfo['value'];
                                    }
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
                                }
                            }
                        }
                    }
                }
<<<<<<< HEAD
                //  $eventArray = array_merge($this->getPresaleDate($group, $id, $eventArray), $eventArray);
            }
            //  echo "customdata: OK";

        }
=======

                //  print_r($eventArray[$id]['genre']);
            }
        } else {
            echo $response->getStatusCode();
            //       echo $response->getHeaders();
        }

        //    print_r($response->getHeaders());
        //    echo count($eventArray);
        return $eventArray;
>>>>>>> d656e550e6a58c58ae843c0d0ff63e436da71c8f
    }
}
