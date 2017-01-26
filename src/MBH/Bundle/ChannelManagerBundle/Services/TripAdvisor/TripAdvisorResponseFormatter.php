<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;

class TripAdvisorResponseFormatter
{
    const API_VERSION = 7;

    /** @var  TripAdvisorDataFormatter $responseDataFormatter */
    private $responseDataFormatter;
    private $domainName;

    public function __construct(TripAdvisorDataFormatter $responseDataFormatter, $domainName)
    {
        $this->responseDataFormatter = $responseDataFormatter;
        $this->domainName = $domainName;
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

    public function formatHotelAvailability(
        $apiVersion,
        $requestedHotels,
        $startDate,
        $endDate,
        $adultsChildrenCombination,
        $language,
        $queryKey,
        $currency,
        $userCountry,
        $deviceType
    ) {
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
            ->getAvailabilityData($startDate, $endDate, $requestedHotels);

        $hotelsAvailabilityData = [];
        foreach ($availabilityData as $ta_id => $hotelAvailabilityData) {
            $hotelAvailabilityResponseData = [];
            foreach ($hotelAvailabilityData as $roomTypeAvailabilityResults) {
                //Тариф используется только 1, поэтому 1 результат
                /** @var SearchResult $roomTypeAvailabilityData */
                $roomTypeAvailabilityData = $roomTypeAvailabilityResults['results'][0];
                $resultPrice = 0;
                $isAllCombinationExists = true;
                foreach ($adultsChildrenCombination as $combination) {
                    $adultsCount = $combination['adults'];
                    $childrenCount = isset($combination['children']) ? count($combination['children']) : 0;
                    $price = $roomTypeAvailabilityData->getPrice($adultsCount, $childrenCount);
                    if (!$price) {
//                        $price = $roomTypeAvailabilityData->g
                        $isAllCombinationExists = false;
                    }
                    $resultPrice += $price;
                }

                if ($roomTypeAvailabilityData->getRoomsCount() >= count($adultsChildrenCombination)
                    && $isAllCombinationExists
                ) {

                    $roomTypeResponseData = [];

                    //TODO: Получить URL страницы сайта
                    $roomTypeResponseData['url'] = '';

                    $roomTypeResponseData['price'] = $resultPrice;
                    $roomTypeResponseData['num_rooms'] = 1;
                    $roomTypeResponseData['fees'] = 0;
                    $roomTypeResponseData['fees_at_checkout'] = 0;
                    $roomTypeResponseData['taxes'] = 0;
                    $roomTypeResponseData['taxes_at_checkout'] = 0;
                    $roomTypeResponseData['final_price'] = $resultPrice;

                    /** @var RoomType $roomType */
                    $roomType = $roomTypeAvailabilityResults['roomType'];
                    $hotelAvailabilityResponseData['room_types'][$roomType->getName()] = $roomTypeResponseData;
                }
            }

            if (count($hotelAvailabilityResponseData) > 0) {
                $hotelAvailabilityResponseData['hotel_id'] = $ta_id;
            }
            $hotelsAvailabilityData[] = $hotelAvailabilityResponseData;
        }

        $response['num_hotels'] = count($hotelsAvailabilityData);
        $response['hotels'] = $hotelsAvailabilityData;

        return $response;
    }

    public function formatBookingAvailability(
        $apiVersion,
        $hotelData,
        $startDate,
        $endDate,
        $adultsChildrenCombination,
        $language,
        $queryKey,
        $userCountry,
        $deviceType
    ) {
        $response['api_version'] = $apiVersion;
        $response['hotel_id'] = $hotelData['ta_id'];
        $response['start_date'] = $startDate;
        $response['end_date'] = $endDate;
        $response['party'] = $adultsChildrenCombination;
        $response['lang'] = $language;
        $response['query_key'] = $queryKey;
        $response['user_country'] = $userCountry;
        if ($deviceType) {
            $response['device_type'] = $deviceType;
        }

        $availabilityDataArray = $this->responseDataFormatter
            ->getAvailabilityData($startDate, $endDate, [$hotelData['partner_hotel_code']]);

//        TODO: Убрать
        $availabilityData = null;
        if (count($availabilityDataArray)) {
            $availabilityData = current($availabilityDataArray);
        }

        $tariffData = [];
        $roomTypeData = [];
        $hotelRoomRates = [];

        foreach ($availabilityData as $roomTypeAvailabilityData) {
            $roomType = $roomTypeAvailabilityData['roomType'];
            foreach ($roomTypeAvailabilityData['results'] as $searchResult) {
                /** @var SearchResult $searchResult */
                $tariffData[] = $this->getTariffData($searchResult->getTariff());
                $roomTypeData[] = $this->getRoomTypeData($roomType);
            }
        }

    }

    private function getTariffData(Tariff $tariff)
    {
        /** @var Tariff $tariff */
        $tariffData = [
            'code' => $tariff->getId(),
            'name' => $tariff->getName(),
            'description' => $tariff->getDescription() ? $tariff->getDescription() : $tariff->getName(),
            //TODO: Добавить
            'rate_amenities' => [],
            //TODO: Узнать про него. У нас такого поля нет.
            'refundable',
            //TODO: То же самое, у нас данных об этом нет
            'cancellation_rules',
            //TODO: У нас есть услуги питания, но их название может меняться. Могу попробовать сделать соответствие
            'meal_plan'
        ];

        return $tariffData;
    }

    private function getHotelRoomRates(SearchResult $result, $adultChildrenCombinations, $currency)
    {
        $price = $this->getResultPriceByAdultsChildrenCombinations($result, $adultChildrenCombinations);
        if (!$price) {
            //TODO: Что с ошибкой?
            throw new \Exception();
        }
        $hotelRoomRates = [
            'hotel_room_type_code' => $result->getRoomType()->getId(),
            'hotel_rate_plan_code' => $result->getTariff()->getId(),
            'final_price_at_booking' => [
                'amount' => $price,
                'currency' => $currency
            ],
            //TODO: Пока что 0, может быть впоследствии другим значением
            'final_price_at_checkout' => [
                'amount' => 0,
                'currency' => $currency
            ]
            //TODO: Мб потребуется параметр
            //partner_data
        ];

        return $hotelRoomRates;
        /**
         * "final_price_at_booking": {
         * "amount": 240,
         * "currency": "USD"
         * },
         * "final_price_at_checkout": {
         * "amount": 0,
         * "currency": "USD"
         * },
         */
    }

    private function getResultPriceByAdultsChildrenCombinations($adultsChildrenCombinations, SearchResult $result)
    {
        $roomType = $result->getRoomType();
        $tariff = $result->getTariff();
        $adultsChildrenCounts = $this->getAdultsChildrenCount($adultsChildrenCombinations, $tariff);
        //Все ли кобминации количеств детей и взрослых имеют цену,
        $isAllHavenPrice = true;
        $resultPrice = 0;
        foreach ($adultsChildrenCounts as $estimatedAdultsChildrenCount) {
            $adultsChildrenCounts = $roomType->getAdultsChildrenCombination(
                $estimatedAdultsChildrenCount['childrenCount'], $estimatedAdultsChildrenCount['adultsCount']);
            $price = $result->getPrice($adultsChildrenCounts['adults'], $adultsChildrenCounts['children']);
            if ($price) {
                $resultPrice += $price;
            } else {
                $isAllHavenPrice = false;
                break;
            }
        }

        if ($isAllHavenPrice && $result->getRoomsCount() == count($adultsChildrenCounts)) {
            return $resultPrice;
        }

        return false;
    }

    private function getAdultsChildrenCount($adultsChildrenCombinations, Tariff $tariff)
    {
        $adultAndChildrenCounts = [];
        foreach ($adultsChildrenCombinations as $combination) {
            $adultsCount = $combination['adults'];
            $childrenAges = $combination['children'];
            $childrenCount = 0;
            foreach ($childrenAges as $childrenAge) {
                if ($childrenAge <= $tariff->getInfantAge()) {
                    continue;
                }
                if ($childrenAge <= $tariff->getChildAge()) {
                    $childrenCount++;
                } else {
                    $adultsCount++;
                }
            }

            $adultAndChildrenCounts[] = [
                'childrenCount' => $childrenCount,
                'adultsCount' => $adultsCount
            ];
        }

        return $adultAndChildrenCounts;
    }

    private function getRoomTypeData(RoomType $roomType)
    {
        $roomTypeResponseData = [
            'code' => $roomType->getId(),
            'name' => $roomType->getName(),
            'description' => $roomType->getDescription()
                ? $roomType->getDescription() : $roomType->getName(),
            'photos' => $this->getRoomTypePhotoData($roomType),
            //TODO: Сделать аменитисы
            'room_amenities' => [],
            'max_occupancy' => [
                'number_of_adults' => $roomType->getPlaces() + $roomType->getAdditionalPlaces(),
                'number_of_children' => 0
            ],
            //TODO: Что делать с кроватями? У нас о них инфо не хранится
            //TODO: Smoking или нет? У нас не указывается
        ];
        if ($roomType->getRoomSpace()) {
            $roomTypeResponseData['room_size_value'] = $roomType->getRoomSpace();
            $roomTypeResponseData['room_size_units'] = 'square_meters';
        }

        return $roomTypeResponseData;
    }

    private function getRoomTypePhotoData(RoomType $roomType)
    {
        $imagesData = [];
        foreach ($roomType->getImages() as $image) {
            $roomTypeImageData = [];
            /** @var RoomTypeImage $image */
            $roomTypeImageData['url'] = $this->domainName . '/' . $image->getPath();
            if ($image->getWidth()) {
                $roomTypeImageData['width'] = $image->getWidth();
            }
            if ($image->getHeight()) {
                $roomTypeImageData['height'] = $image->getHeight();
            }
            $imagesData[] = $roomTypeImageData;
        }

        return $imagesData;
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