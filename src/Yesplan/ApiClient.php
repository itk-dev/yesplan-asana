<?php

namespace App\Yesplan;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpClient\HttpClient;

class ApiClient
{
    public function getEvents(string $url, array $eventArray): array
    {
        //https://musikhusetaarhus.yesplan.be/api/events/date%3A%23next10years/customdata?api_key=53FD0F325B0AE34B5D620ADFE6879F2D
        $client = HttpClient::create();
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() == "200") {
            

            $responseJson = json_decode($response->getContent(), true);
            $id = "";
            $responseArray = $response->toArray();


            if (isset($responseArray["pagination"]["next"])) {
                sleep(6);
                $nextPageUrl = $responseArray["pagination"]["next"] . "&api_key=53FD0F325B0AE34B5D620ADFE6879F2D";
                $eventArray = array_merge($eventArray, $this->getEvents($nextPageUrl, $eventArray));
                //echo "page: " . $nextPageUrl;
            }

            foreach ($responseArray["data"] as $data) {

                $id = $data["event"]["id"];
                $eventArray[$id]['id'] = $id;
                $eventArray[$id]['data'] = $data;
                $eventArray[$id]['title'] = $data["event"]["name"];
                $eventArray[$id]['marketing_budget'] = "";
                $eventArray[$id]['genre'] = "";
                $eventArray[$id]['publication_date'] = "";
                $eventArray[$id]['ticketinfo_sale'] = "";
                $eventArray[$id]['presale_date'] = "";

                $eventArray[$id]['location'] = "";
                $eventArray[$id]['eventDate'] = "";

                //$eventArray[$id]['location'] = $data["event"]["name"];
                $groups = $data["groups"];

                foreach ($groups as $group) {
                    $groupKeyword = $group["keyword"];
                    //marketing budget
                    //groups -> bugdetudgifter -> bugdetudgifter_andreudgifterekstern -> expences_marketing
                    if ($groupKeyword == "budgetudgifter") {
                        foreach ($group["children"] as $budgetExpenses) {
                            if ($budgetExpenses["keyword"] == "budgetudgifter_andreudgifterekstern") {
                                foreach ($budgetExpenses["children"] as $externalExpenses) {
                                    if ($externalExpenses["keyword"] == "expences_marketing") {
                                        $eventArray[$id]['marketing_budget'] = $externalExpenses["value"];
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

                                        $eventArray[$id]['genre'] = $information["value"][0];
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
                                foreach ($tix["children"] as $ticketPublic) {
                                    if ($ticketPublic["keyword"] == "ticketinfo_public") {
                                        $eventArray[$id]['publication_date'] = $ticketPublic["value"];
                                    }
                                    if ($ticketPublic["keyword"] == "ticketinfo_sale") {
                                        $eventArray[$id]['ticketinfo_sale'] = $ticketPublic["value"];
                                    }
                                }
                            }
                        }
                    }
                    //Presale dato + tid
                    //groups -> billetforhold -> billetforhold_billetinformation -> ticketinfo_presaledatetime

                    if ($group["keyword"] == "billetforhold") {
                        foreach ($group["children"] as $ticketinformation) {
                            if ($ticketinformation["keyword"] == "billetforhold_billetinformation") {
                                foreach ($ticketinformation["children"] as $ticketinfo) {
                                    if ($ticketinfo["keyword"] == "ticketinfo_presaledatetime") {
                                        $eventArray[$id]['presale_date'] = $ticketinfo["value"];
                                    }
                                }
                            }
                        }
                    }
                }

                //  print_r($eventArray[$id]['genre']);

            }

        }
        else{
            echo $response->getStatusCode();
     //       echo $response->getHeaders();
        }

    //    print_r($response->getHeaders());
    //    echo count($eventArray);
        return $eventArray;
    }
}
