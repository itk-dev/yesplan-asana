<?php

namespace App\Yesplan;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;

class ApiClient{
    public function getEvents():array
    {
        $client = new CurlHttpClient();
        $response = $client->request('GET', 'https://musikhusetaarhus.yesplan.be/api/events?api_key=38102428C8331E40E3D65E867DE96B1A');
        $responseJson = json_decode($response->getContent(), true);
        $id;
        $eventArray = array();
        foreach($responseJson as $data){
            if(is_array($data)){
                foreach($data as $dataValues){
                    if(is_array($dataValues)){
                       $id = $dataValues["id"];
                       $eventArray[$id]['id'] = $id;  
                       $eventArray[$id]['data'] = $dataValues; 
                    }

                }
            }
        }

 //      $id = $responseJson["data"]["0"]["id"];

        return 
                      $eventArray;
                   
            

    }
}