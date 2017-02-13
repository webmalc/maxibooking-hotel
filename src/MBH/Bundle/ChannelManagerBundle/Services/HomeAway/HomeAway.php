<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use GuzzleHttp\Client;

class HomeAway
{
    const GET_LISTINGS_URL = 'https://ws.homeaway.com/public/myListings';
    const TEST = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=22/01/2017';

    public function getRoomTypes()
    {
        $response = (new Client())->request('GET', self::GET_LISTINGS_URL);
        $decodedResponse = json_decode($response->getBody(), true);
        $roomTypesData = [];
        foreach ($decodedResponse['entries'] as $listingData) {
            $unitData = current($listingData['units']);
            $roomTypesData[] = [
                'name' => $unitData['name'],
                //TODO: Возможно другое значение
                'serviceId' => $unitData['unitNumber']
            ];
        }

        return $roomTypesData;
    }

    public function authorization()
    {
        //TODO: Реализовать после получения доступа
    }

    public function testRequest()
    {
        $response = (new Client())->request('GET', self::TEST);
        $xml = new \SimpleXMLElement($response->getBody());
        return $response;
    }
}