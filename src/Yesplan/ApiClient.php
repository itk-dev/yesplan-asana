<?php

namespace App\Yesplan;

use Symfony\Component\HttpClient\HttpClient;

class ApiClient
{
    public function getEvents(string $url, array $eventArray): array
    {
        //https://musikhusetaarhus.yesplan.be/api/events/date%3A%23next10years/customdata?api_key=53FD0F325B0AE34B5D620ADFE6879F2D
        $client = HttpClient::create();
        $response = $client->request('GET', $url);

        if ('200' == $response->getStatusCode()) {
            $responseJson = json_decode($response->getContent(), true);
            $id = '';
            $responseArray = $response->toArray();

            if (isset($responseArray['pagination']['next'])) {
                sleep(6);
                $nextPageUrl = $responseArray['pagination']['next'].'&api_key=53FD0F325B0AE34B5D620ADFE6879F2D';
                $eventArray = array_merge($eventArray, $this->getEvents($nextPageUrl, $eventArray));
                //echo "page: " . $nextPageUrl;
            }

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
                                }
                            }
                        }
                    }
                    //genre
                    //groups -> generelinformation -> generelinformation_data -> eventinfo_web_categori
                    if ('generelinformation' == $group['keyword']) {
                        foreach ($group['children'] as $generelInformation) {
                            if ('generelinformation_data' == $generelInformation['keyword']) {
                                foreach ($generelInformation['children'] as $information) {
                                    if ('eventinfo_web_categori' == $information['keyword']) {
                                        $eventArray[$id]['genre'] = $information['value'][0];
                                    }
                                }
                            }
                        }
                    }
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
                                }
                            }
                        }
                    }
                    //Presale dato + tid
                    //groups -> billetforhold -> billetforhold_billetinformation -> ticketinfo_presaledatetime

                    if ('billetforhold' == $group['keyword']) {
                        foreach ($group['children'] as $ticketinformation) {
                            if ('billetforhold_billetinformation' == $ticketinformation['keyword']) {
                                foreach ($ticketinformation['children'] as $ticketinfo) {
                                    if ('ticketinfo_presaledatetime' == $ticketinfo['keyword']) {
                                        $eventArray[$id]['presale_date'] = $ticketinfo['value'];
                                    }
                                }
                            }
                        }
                    }
                }

                //  print_r($eventArray[$id]['genre']);
            }
        } else {
            echo $response->getStatusCode();
            //       echo $response->getHeaders();
        }

        //    print_r($response->getHeaders());
        //    echo count($eventArray);
        return $eventArray;
    }
}
