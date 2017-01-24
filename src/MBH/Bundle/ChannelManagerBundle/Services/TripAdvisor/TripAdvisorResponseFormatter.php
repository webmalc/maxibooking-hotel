<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

class TripAdvisorResponseFormatter
{
    const API_VERSION = 7;

    /** @var  TripAdvisorDataFormatter $responseDataFormatter */
    private $responseDataFormatter;

    public function __construct(TripAdvisorDataFormatter $responseDataFormatter)
    {
        $this->responseDataFormatter = $responseDataFormatter;
    }

    public function formatConfigResponse()
    {
        $response = [];
        $response['api_version'] = TripAdvisorResponseFormatter::API_VERSION;

        $configurationData = [];

        //TODO: Получить данные
        $emergencyContacts = [];
        foreach ($emergencyContacts as $emergencyContact) {
            $configurationData['emergency_contacts'][] = $this->getContactInfo($emergencyContact['name'],
                $emergencyContact['email'], $emergencyContact['phone']);
        }

        //TODO: Получить данные
        $infoContacts = [];
        foreach ($infoContacts as $infoContact) {
            $configurationData['info_contacts'][] = $this->getContactInfo($infoContact['name'],
                $infoContact['email'], $infoContact['phone']);
        }

        //TODO: Какой и откуда брать язык
        $configurationData['languages'] = ['en'];
        //pref_hotels(предпочитаемое кол-во отелей за запрос)
        // и five_min_rate_limit(предпочитаемое кол-во запросов за 5 минут)

        $response['configuration'] = $configurationData;

        return json_encode($response);
    }

    public function formatHotelInventoryData()
    {
        $response = [];
    }

    public function formatHotelAvailability($apiVersion, $requestedHotels, $startDate, $endDate,
        $adultsChildrenCombination, $language, $queryKey, $currency, $userCountry, $deviceType)
    {
        $response = [];
        $response['api_version'] = $apiVersion;

        foreach ($requestedHotels as $requestedHotelData) {
            $response['hotel_ids'] = $requestedHotelData['ta_id'];
        }
        $response['start_date'] = $startDate;
        $response['end_date'] = $endDate;
        $response['party'] = $adultsChildrenCombination;
        $response['lang'] = $language;
        $response['query_key'] = $queryKey;
        $response['currency'] = $currency;
        $response['user_country'] = $userCountry;
        $response['device_type'] = $deviceType;

        $availabilityData = $this->responseDataFormatter
            ->getAvailabilityData($startDate, $endDate, $adultsChildrenCombination, $requestedHotels);

        $hotelsAvailabilityData = [];
        foreach ($availabilityData as $ta_id => $hotelAvailabilityData) {
            $hotelAvailabilityResponseData = ['hotel_id' => $ta_id];
            foreach ($hotelAvailabilityData as $roomTypeAvailabilityResults) {
                //Тариф используется только 1, поэтому 1 результат
                /** @var SearchResult $roomTypeAvailabilityData */
                $roomTypeAvailabilityData = $roomTypeAvailabilityResults['results'][0];
                $adultsChildrenCombinationStrings = [];
                foreach ($adultsChildrenCombination as $combination) {
                    $adultsCount = $combination['adults'];
                    $childrenCount = isset($combination['children']) ? count($combination['children']) : 0;
                    $adultsChildrenCombinationStrings[] = $adultsCount . '_' . $childrenCount;
                }
                if ($roomTypeAvailabilityData->getRoomsCount() >= count($adultsChildrenCombination)) {

                    $roomTypeResponseData = [];

                    //TODO: Получить URL страницы сайта
                    $roomTypeResponseData['url'] = '';
                    $price = current($roomTypeAvailabilityData->getPrices());

                    $roomTypeResponseData['price'] = $price;
                    $roomTypeResponseData['num_rooms'] = 1;
                    $roomTypeResponseData['fees'] = 0;
                    $roomTypeResponseData['fees_at_checkout'] = 0;
                    $roomTypeResponseData['taxes'] = 0;
                    $roomTypeResponseData['taxes_at_checkout'] = 0;
                    $roomTypeResponseData['final_price'] = $price;

                    /** @var RoomType $roomType */
                    $roomType = $roomTypeAvailabilityResults['roomType'];
                    $hotelAvailabilityResponseData['room_types'][$roomType->getName()] = $roomTypeResponseData;
                }
            }
            $hotelsAvailabilityData[] = $hotelAvailabilityResponseData;
        }

        $response['num_hotels'] = count($hotelsAvailabilityData);
        $response['hotels'] = $hotelsAvailabilityData;

        return $response;
    }




    private function getContactInfo($fullName, $email, $phoneNumber)
    {
        //TODO: Если email больше 256 и ном.телефона больше 50 что делать?
        return [
            'full_name' => $fullName,
            'email' => $email,
            'phone_number' => $phoneNumber
        ];
    }
}