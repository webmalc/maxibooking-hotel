<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffService;

class TripAdvisorResponseFormatter
{
    const API_VERSION = 7;

    private $domainName;
    private $arrivalTime;
    private $departureTime;
    private $onlineFormUrl;

    private $rateAmenities = [

    ];

    private $rateMealTypes = [
        'All Inclusive' => 1,
        'Buffet breakfast' => 4,
        'Continental breakfast' => 6,
        'Full english breakfast' => 7,
        'Full board' => 10,
        'Half board' => 12,
        'Breakfast' => 19,
        'Lunch' => 21,
        'Dinner' => 22,
        'Breakfast and Lunch' => 23
    ];

    private $hotelAmenities = [
        'bath' => 'BATHROOMS',
        'apartments' => 'APARTMENTS',
        'beach' => 'BEACH',
//        'BED_AND_BREAKFAST'
        'credit-card' => 'CREDIT_CARDS_ACCEPTED',
        'fitness' => 'FITNESS_CENTER',
        'breakfast' => 'FREE_BREAKFAST',
        'parking' => 'PARKING_AVAILABLE',
        'free-wifi' => 'FREE_WIFI',
        'luxury_holidays' => 'LUXURY',
        'swimming' => 'SWIMMING_POOL',
    ];

    //TODO: Убрать response data formatter и передавать сюда готовые данные
    public function __construct(
        $domainName,
        $arrivalTime,
        $departureTime,
        $onlineFormUrl
    ) {
        $this->domainName = $domainName;
        $this->arrivalTime = $arrivalTime;
        $this->departureTime = $departureTime;
        $this->onlineFormUrl = $onlineFormUrl;
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
            $configurationData['info_contacts'][] =
                $this->getContactInfo($infoContact['name'], $infoContact['email'], $infoContact['phone']);
        }

        //TODO: Какой и откуда брать язык
        $configurationData['languages'] = ['en'];
        //pref_hotels(предпочитаемое кол-во отелей за запрос)
        // и five_min_rate_limit(предпочитаемое кол-во запросов за 5 минут)

        $response['configuration'] = $configurationData;

        return json_encode($response);
    }

    public function formatHotelInventoryData($apiVersion, $language, $inventoryType, $configs)
    {
        $hotelsData = [];
        /** @var TripAdvisorConfig $config */
        foreach ($configs as $config) {
            /** @var Hotel $hotel */
            $hotel = $config->getHotel();
            $hotelAmenities = $this->getAvailableAmenities($hotel->getFacilities());

            /** @var Hotel $hotel */
            $hotelData = [
                'ta_id' => $config->getHotelId(),
                'partner_id' => $hotel->getId(),
                'name' => $hotel->getInternationalTitle(),
                //TODO: Улица, город должны быть на английском
                'street' => $hotel->getStreet(),
                'city' => $hotel->getCity(),
                //TODO: Можно добавить область на английском
//                'state',
                //TODO: Имя тоже на английском
                'country' => $hotel->getRegion()->getCountry(),
//                'postal_code' => $hotel->get
                'amenities' => $hotelAmenities,
                //TODO: Добавить откуда-то
                'url' => '',
                //TODO: Добавить опционально email, phone, fax,
            ];

            if ($hotel->getLatitude()) {
                $hotelData['latitude'] = $hotel->getLatitude();
            }
            if ($hotel->getLongitude()) {
                $hotelData['longitude'] = $hotel->getLongitude();
            }
            if ($hotel->getDescription()) {
                $hotelData['desc'] = $hotel->getDescription();
            }

            foreach ($hotel->getRoomTypes() as $roomType) {
                if ($roomType->getDescription()) {
                    $roomTypeData = [];
                    if ($roomType->getDescription()) {
                        $roomTypeData['desc'] = $roomType->getDescription();
                    }
                    //TODO: Если будет URL
//                    if ($roomType->getUrl()) {
//                        $hotelData['url'] = $roomType->getUrl();
//                    }
                    $hotelData['room_types'][] = $roomTypeData;
                }
            }
            $hotelsData[] = $hotelData;
        }

        $response = [
            'api_version' => $apiVersion,
            'lang' => $language,
            'hotels' => $hotelsData
        ];

        return $response;
    }

    public function formatHotelAvailability(
        $availabilityData,
        $apiVersion,
        $requestedHotels,
        $startDate,
        $endDate,
        $adultsChildrenCombinations,
        $language,
        $queryKey,
        $currency,
        $userCountry,
        $deviceType
    ) {
        $response = [
            'api_version' => $apiVersion,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'party' => $adultsChildrenCombinations,
            'currency' => $currency,
            'user_country' => $userCountry,
            'device_type' => $deviceType,
            'query_key' => $queryKey,
            'lang' => $language
        ];

        foreach ($requestedHotels as $requestedHotelData) {
            $response['hotel_ids'][] = $requestedHotelData['ta_id'];
        }

        $hotelsAvailabilityData = [];
        foreach ($availabilityData as $ta_id => $hotelAvailabilityData) {
            $hotelAvailabilityResponseData = [];
            foreach ($hotelAvailabilityData as $roomTypeAvailabilityResults) {
                //Для каждого типа номера используется только 1 тариф, поэтому 1 результат
                /** @var SearchResult $roomTypeAvailabilityData */
                $roomTypeAvailabilityData = current($roomTypeAvailabilityResults['results']);
                if ($roomTypeAvailabilityData) {
                    $priceData = $this->getPriceDataByAdultsChildrenCombinations($adultsChildrenCombinations,
                        $roomTypeAvailabilityData);

                    if ($priceData != false) {
                        $roomTypeResponseData = [];

                        $begin = \DateTime::createFromFormat('Y-m-d', $startDate);
                        $end = \DateTime::createFromFormat('Y-m-d', $endDate);
                        $firstAdultsChildrenCombination = current($adultsChildrenCombinations);
                        $firstAdultsChildrenCounts = current($this->getAdultsChildrenCount([$firstAdultsChildrenCombination],
                            $roomTypeAvailabilityData->getTariff()));
                        $locale = substr($language, 0, 2);
                        $roomTypeResponseData['url'] =
                            $this->getSearchUrl($roomTypeAvailabilityData->getRoomType()->getId(), $begin, $end,
                                $firstAdultsChildrenCounts['adultsCount'], $firstAdultsChildrenCounts['childrenCount'],
                                $locale);

                        $roomTypeResponseData['price'] = $priceData['price'];
                        $roomTypeResponseData['num_rooms'] = count($adultsChildrenCombinations);
                        $roomTypeResponseData['fees'] = 0;
                        $roomTypeResponseData['fees_at_checkout'] = 0;
                        $roomTypeResponseData['taxes'] = 0;
                        $roomTypeResponseData['taxes_at_checkout'] = 0;
                        $roomTypeResponseData['final_price'] = $priceData['price'];

                        /** @var RoomType $roomType */
                        $roomType = $roomTypeAvailabilityResults['roomType'];
                        $amenities = $this->getAvailableAmenities($roomType->getFacilities());
                        if (count($amenities) > 0) {
                            $roomTypeResponseData['room_amenities'] = $amenities;
                        }
                        $hotelAvailabilityResponseData['room_types'][$roomType->getName()] = $roomTypeResponseData;
                    }
                }
            }

            if (count($hotelAvailabilityResponseData) > 0) {
                $hotelAvailabilityResponseData['hotel_id'] = $ta_id;
                $hotelsAvailabilityData[] = $hotelAvailabilityResponseData;
            }
        }

        $response['num_hotels'] = count($hotelsAvailabilityData);
        $response['hotels'] = $hotelsAvailabilityData;

        return $response;
    }

    public function formatBookingAvailability(
        $availabilityData,
        Hotel $hotel,
        $apiVersion,
        $hotelData,
        $startDate,
        $endDate,
        $adultsChildrenCombination,
        $language,
        $queryKey,
        $userCountry,
        $deviceType,
        $currency
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

        foreach ($availabilityData as $roomTypeAvailabilityData) {
            /** @var RoomType $roomType */
            $roomType = $roomTypeAvailabilityData['roomType'];
            foreach ($roomTypeAvailabilityData['results'] as $searchResult) {
                /** @var SearchResult $searchResult */
                $tariff = $searchResult->getTariff();
                $response['hotel_rate_plans'][$tariff->getId()] = $this->getTariffData($tariff);
                $response['hotel_room_types'][$roomType->getId()] = $this->getRoomTypeData($roomType);
                $hotelRoomRate = $this->getHotelRoomRates($searchResult, $adultsChildrenCombination, $currency);
                if ($hotelRoomRate) {
                    $response['hotel_room_rates'][] = $hotelRoomRate;
                }
            }
        }

        $response['hotel_details'] = $this->getHotelDetails($hotel);
//        //TODO: Такого тоже нет
//            'terms_and_conditions',
//            'terms_and_conditions_url',
//            'payment_policy',
        //Такого нет
//        'customer_support' => [
//            'phone_numbers' => [
//                'contact' => $hotel->getContactPhoneNumber(),
//                'description' => 'Support phone line'
//            ]
//        ],
        //TODO: нет
//        $response['accepted_credit_cards'] (Обязательно) [Visa, MasterCard, AmericanExpress, Discover]
        //TODO: Посмотреть так же
//        'errors' => [
//
//        ]

        return $response;
    }

    public function formatSubmitBookingResponse(
        $bookingSession,
        $bookingCreationResult,
        $messages,
        $countryCode,
        $roomStayData,
        $currency
    ) {
        if ($bookingCreationResult instanceof Order) {
            $creationResultStatus = 'Success';
        } else {
            $creationResultStatus = 'Failure';
        }

        $response = [
            'reference_id' => $bookingSession,
            'status' => $creationResultStatus,
            //TODO: Информация о поддержке клиента
            'customer_support' => $this->getCustomerSupportData()
        ];

        if ($creationResultStatus == 'Success') {
            $response['reservation'] =
                $this->getReservationData($bookingCreationResult, $countryCode, $roomStayData, $currency);
        }

        //TODO: Переделать, должен быть объект
        if (count($messages['problems']) > 0) {
            $response['problems'] = $messages['problems'];
        }

        return $response;
    }

    public function formatBookingVerificationResponse(?Order $order, $channelManagerOrderId)
    {
        $isCreated = is_array($order) ? false : true;
        $response = [
            //TODO: Не знаю где хранить подобные данные
            'problems' => [

            ],
            'reference_id' => $channelManagerOrderId,
            'status' => $isCreated ? 'Success' : 'Failure',
            'customer_support' => $this->getCustomerSupportData()
        ];

        if ($isCreated) {
            //TODO: Не могу закончить, так как нигде не хранятся данные о коде страны, детях и валюте, в которой будут платить
            $response['reservation'] = $this->getReservationData($order);
        }

        return $response;
    }

    public function formatBookingCancelResponse($removalStatus, $hotelId, $orderId)
    {
        return [
            'partner_hotel_code' => $hotelId,
            'reservation_id' => $orderId,
            "status" => $removalStatus,
            'cancellation_number' => $orderId,
            'customer_support' => $this->getCustomerSupportData()
        ];
    }

    public function formatBookingSyncResponse($syncOrderData)
    {
        //TODO: Получить currency
        $currency = '';
        $bookingSyncResponse = [];
        foreach ($syncOrderData as $orderData) {
            /** @var Order $order */
            $order = $orderData['order'];
            $packages = $orderData['packages'];
            $status = $this->getSyncOrderData($order, $packages);

            $totalFee = 0;
            foreach ($order->getFee() as $feeCashDocument) {
                /** @var CashDocument $feeCashDocument */
                $totalFee += $feeCashDocument->getTotal();
            }

            $orderResponseData = [
                'reservation_id' => $orderData['orderId'],
                'partner_hotel_code' => $orderData['hotelId'],
                'status' => $status,
                'total_rate' => $this->getPriceObject($order->getPrice(), $currency),
                //TODO: У нас пока нет никаких данных о налогах
                'total_taxes' => $this->getPriceObject(0, $currency),
                'total_fees' => $this->getPriceObject($totalFee, $currency)
            ];

            if ($status != 'Cancelled') {
                $orderResponseData['checkin_date'] = $this->getOrderCheckedIn($packages);
                $orderResponseData['checkout_date'] = $this->getOrderCheckedOut($packages);
            } else {
                $orderResponseData['cancelled_date'] = $order->getDeletedAt()->format('Y-m-d');
                $orderResponseData['cancellation_number'] = $order->getChannelManagerId();
            }

            $bookingSyncResponse[] = $orderResponseData;
        }

        return $bookingSyncResponse;
    }

    public function formatRoomInformationResponse($apiVersion, $hotelData, $language, $queryKey, Hotel $hotel)
    {
        $hotelRoomTypes = [];
        foreach ($hotel->getRoomTypes() as $roomType) {
            $hotelRoomTypes[] = $this->getRoomTypeData($roomType);
        }

        $tariffsData = [];
        foreach ($hotel->getTariffs() as $tariff) {
            $tariffsData[] = $this->getTariffData($tariff);
        }

        $response = [
            'api_version' => $apiVersion,
            'hotel_id' => $hotelData['ta_id'],
            'language_response' => [
                'code_str' => $language,
                'error_log' => ''
            ],
            'unique_query_key' => $queryKey,
            'hotel_room_types' => $hotelRoomTypes,
            'hotel_rate_plans' => $tariffsData
        ];

        return $response;
    }

    private function getAvailableAmenities($amenities)
    {
        $availableAmenities = [];
        foreach ($amenities as $amenity) {
            if (isset($this->hotelAmenities[$amenity]) && !in_array($amenity, $amenities)) {
                $availableAmenities[] = $this->hotelAmenities[$amenity];
            }
        }

        return $availableAmenities;
    }

    private function getSearchUrl($roomTypeId, \DateTime $begin, \DateTime $end, $adultsCount, $childrenCount, $locale)
    {
        $params = [
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
            'roomType' => $roomTypeId,
            'adults' => $adultsCount,
            'children' => $childrenCount,
            'locale' => $locale
        ];

        return $this->onlineFormUrl . '?' . http_build_query($params);
    }

    private function getOrderCheckedIn($packages)
    {
        $firstBeginDate = null;
        foreach ($packages as $package) {
            /** @var Package $package */
            if (is_null($firstBeginDate) || $firstBeginDate > $package->getBegin()) {
                $firstBeginDate = $package->getBegin();
            }
        }

        return $firstBeginDate;
    }

    //TODO: Реализовать
    private function getOrderCheckedOut($packages)
    {
        return new \DateTime();
    }

    private function getSyncOrderData(?Order $order, $packages)
    {
        if (is_null($order)) {
            return 'UnknownReference';
        }

        if ($order->isDeleted()) {
            return 'Cancelled';
        }
        if ($this->isAllPackagesCheckedOut($packages)) {
            return 'CheckedOut';
        }
        if ($this->isAnyPackageCheckedIn($packages)) {
            return 'CheckedIn';
        }

        //TODO: Есть еще "NoShow", не знаю что это.
        return 'Booked';
    }

    private function isAllPackagesCheckedOut($packages)
    {
        $isAllPackagesCheckedOut = true;
        foreach ($packages as $package) {
            /** @var Package $package */
            if (!$package->getIsCheckOut()) {
                $isAllPackagesCheckedOut = false;
            }
        }

        return $isAllPackagesCheckedOut;
    }

    private function isAnyPackageCheckedIn($packages)
    {
        foreach ($packages as $package) {
            /** @var Package $package */
            if ($package->getIsCheckIn()) {
                return true;
            }
        }

        return false;
    }

    //TODO: Реализовать
    private function getCustomerSupportData()
    {
        return [

        ];
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
            //TODO: Можно добавить фотки
        ];

        $rateMealPlanes = [];
        foreach ($tariff->getDefaultServices() as $service) {
            /** @var TariffService $service */
            $serviceCode = $service->getService()->getCode();
            if (isset($this->rateMealTypes[$serviceCode])) {
                $rateMealPlanes['standard'][] = $this->rateMealTypes[$serviceCode];
            } else {
                $rateMealPlanes['custom'][] = $serviceCode;
            }
        }
        $tariffData['meal_plan'] = $rateMealPlanes;

        return $tariffData;
    }

    private function getReservationData(Order $order, $countryCode, $roomStayData, $currency)
    {
        /** @var Package $orderFirstPackage */
        $orderFirstPackage = $order->getPackages()[0];
        /** @var Tourist $payer */
        $payer = $order->getPayer();
//        $roomStayData = [];
//        foreach ($order->getPackages() as $package) {
//            /** @var Tourist $mainTraveller */
//            $mainTraveller = $package->getTourists()[0];
//            $roomStayData[] = [
//                'party' => [
//                    'adults' => $package->getAdults(),
//                    //TODO: Неоткуда получить данные. Хорошо бы добавить поле данных возрастов киндеров
//                    'children'
//                ],
//                'traveler_first_name' => $mainTraveller->getFirstName(),
//                'traveler_last_name' => $mainTraveller->getLastName()
//            ];
//        }
        $cashDocumentsData = [];
        foreach ($order->getCashDocuments() as $cashDocument) {
            /** @var CashDocument $cashDocument */
            if ($cashDocument->getOperation() == 'in') {
                $cashDocType = 'rate';
            } else {
                $cashDocType = 'fee';
            }
            $cashDocumentsData[] = [
                'price' => [
                    'amount' => $cashDocument->getTotal(),
                    'currency' => $currency
                ],
                //TODO: Мб потом появятся еще данные о налогах
                'type' => $cashDocType
            ];
        }

        $reservationData = [
            'reservation_id' => $order->getId(),
            'status' => 'Booked',
            //TODO: Сделать URL для подтверждения брони
            'confirmation_url',
            'checkin_date' => $orderFirstPackage->getBegin(),
            'checkout_date' => $orderFirstPackage->getEnd(),
            'partner_hotel_code' => $orderFirstPackage->getHotel()->getId(),
            'hotel' => $this->getHotelDetails($orderFirstPackage->getHotel()),
            'customer' => [
                'first_name' => $payer->getFirstName(),
                'last_name' => $payer->getLastName(),
                'phone_number' => $payer->getPhone(),
                'email' => $payer->getEmail(),
                'country' => $countryCode
            ],
            'rooms' => $roomStayData,
            'receipt' => [
                'line_items' => $cashDocumentsData,
                'final_price_at_booking' => $this->getPriceObject($order->getPrice(), $currency),
                //TODO: Пока что вроде как только при бронировании берется, но мб потом добавим
                'final_price_at_checkout' => $this->getPriceObject(0, $currency)
            ]
            //TODO: Можно добавить доп. данные
//            'legal_text' 'comments'
        ];

        return $reservationData;
    }

    private function getPriceObject($priceValue, $currency)
    {
        return [
            'amount' => $priceValue,
            'currency' => $currency
        ];
    }

    private function getHotelDetails(Hotel $hotel)
    {
        $hotelDetails = [
            'name' => $hotel->getName(),
            //TODO: Это необязательные данные для ввода
            'address1' => $hotel->getStreet() . ', ' . $hotel->getHouse(),
            'city' => $hotel->getCity()->getName(),
            //TODO: Должен быть ISO-3166 код
            'country' => $hotel->getCountry()->getTitle(),
//            'phone' => $hotel->getP
            //TODO: Заполнить
            'hotel_amenities' => [

            ],
            //TODO: Нет фотографий отеля
            'photos' => [],
            //TODO: Инструкции при выезде из отеля
            'checkinout_policy' => '',
            //TODO: Уточнить так ли
            'checkin_time' => $this->arrivalTime,
            'checkout_time' => $this->departureTime,
            //TODO: Такого тоже нет
            'hotel_smoking_policy' => '',
        ];
        //TODO: Добавить  номер теелфона(phone), url контактов отеля,
        //('child_policy','pet_policy', 'parking_shuttle'), 'extra_bed_policy_hotel', 'extra_fields', 'other_policy'
        if ($hotel->getLatitude()) {
            $hotelDetails['latitude'] = $hotel->getLatitude();
        }
        if ($hotel->getLongitude()) {
            $hotelDetails['longitude'] = $hotel->getLongitude();
        }

        return $hotelDetails;
    }

    private function getHotelRoomRates(SearchResult $result, $adultChildrenCombinations, $currency)
    {
        $priceData = $this->getPriceDataByAdultsChildrenCombinations($adultChildrenCombinations, $result);
        if (!$priceData) {
            return false;
        }
        $resultPrice = $priceData['price'];

        $hotelRoomRates = [
            'hotel_room_type_code' => $result->getRoomType()->getId(),
            'hotel_rate_plan_code' => $result->getTariff()->getId(),
            'final_price_at_booking' => $this->getPriceObject($resultPrice, $currency),
            //TODO: Пока что 0, может быть впоследствии другим значением
            'final_price_at_checkout' => $this->getPriceObject(0, $currency),
            'line_items' => [
                "price" => $this->getPriceObject($resultPrice, $currency),
                'type' => 'rate',
                'paid_at_checkout' => true,
                'description' => 'Base rate'
            ],
            //TODO: Других сборов у нас пока нигде не учитывается
            //TODO: Для чего у нас используются данные кредитной карты?
            'payment_policy' => '',
            'rooms_remaining' => $result->getRoomsCount(),
            'partnerData' => [
                'pricesByDate' => $priceData['pricesByDate'],
                'roomTypeId' => $result->getRoomType()->getId(),
                'tariffId' => $result->getTariff()->getId(),
                'hotelId' => $result->getRoomType()->getHotel()->getId()
            ],
        ];

        return $hotelRoomRates;
    }

    private function getPriceDataByAdultsChildrenCombinations($adultsChildrenCombinations, SearchResult $result)
    {
        $roomType = $result->getRoomType();
        $tariff = $result->getTariff();
        $adultsChildrenCounts = $this->getAdultsChildrenCount($adultsChildrenCombinations, $tariff);
        //Все ли кобминации количеств детей и взрослых имеют цену?
        $isAllHavenPrice = true;
        $resultPriceData = ['price' => 0];
        foreach ($adultsChildrenCounts as $estimatedAdultsChildrenCount) {
            $adultsChildrenCounts = $roomType->getAdultsChildrenCombination(
                $estimatedAdultsChildrenCount['adultsCount'], $estimatedAdultsChildrenCount['childrenCount']);
            $adultsCount = $adultsChildrenCounts['adults'];
            $childrenCount = $adultsChildrenCounts['children'];
            $price = $result->getPrice($adultsCount, $childrenCount);
            if ($price) {
                $resultPriceData['price'] += $price;
                $resultPriceData['pricesByDate'][] = $result->getPricesByDate($adultsCount, $childrenCount);
            } else {
                $isAllHavenPrice = false;
                break;
            }
        }

        if ($isAllHavenPrice && $result->getRoomsCount() >= count($adultsChildrenCounts)) {
            return $resultPriceData;
        }

        return false;
    }

    private function getAdultsChildrenCount($adultsChildrenCombinations, Tariff $tariff)
    {
        $adultAndChildrenCounts = [];
        foreach ($adultsChildrenCombinations as $combination) {
            $adultsCount = $combination['adults'];
            $childrenAges = isset($combination['children']) ? $combination['children'] : [];
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
            //TODO: Добавить
            'room_amenities' => [],
            'max_occupancy' => [
                'number_of_adults' => $roomType->getPlaces(),
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