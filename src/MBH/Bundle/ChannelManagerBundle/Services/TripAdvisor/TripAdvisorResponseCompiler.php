<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Lib\TranslatableInterface;
use MBH\Bundle\BaseBundle\Service\Currency;
use MBH\Bundle\CashBundle\Document\CardType;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorRoomType;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorTariff;
use MBH\Bundle\ChannelManagerBundle\Services\OrderHandler;
use MBH\Bundle\HotelBundle\Document\ContactInfo;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffService;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class TripAdvisorResponseCompiler
{
    const API_VERSION = 8;
    const TRIP_ADVISOR_DATE_FORMAT = 'Y-m-d';
    const TRIP_ADVISOR_AVAILABLE_CARD_TYPES = ['Visa', 'MasterCard', 'AmericanExpress', 'Discover'];
    const ROOM_NOT_AVAILABLE_ERROR = 'RoomNotAvailable';
    const PRICE_MISMATCH = 'PriceMismatch';
    const MISSING_EMAIL = 'MissingEmail';
    const MISSING_PAYER_FIRST_NAME = 'MissingTravelerFirstName';
    const CREDIT_CARD_DECLINED = 'CreditCardDeclined';
    const CREDIT_CARD_NOT_SUPPORTED = 'CreditCardTypeNotSupported';

    const RATE_MEAL_TYPES = [
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

    const RATE_AMENITIES = [
        'bar' => 69,
        'bath' => 85,
        'toilets' => 120,
        'wifi' => 900128
    ];

    const HOTEL_AMENITIES = [
        'bath' => 'BATHROOMS',
        'apartments' => 'APARTMENTS',
        'beach' => 'BEACH',
        'credit-card' => 'CREDIT_CARDS_ACCEPTED',
        'fitness' => 'FITNESS_CENTER',
        'breakfast' => 'FREE_BREAKFAST',
        'parking' => 'PARKING_AVAILABLE',
        'free-wifi' => 'FREE_WIFI',
        'luxury_holidays' => 'LUXURY',
        'swimming' => 'SWIMMING_POOL'
    ];

    const ROOM_AMENITIES = [
        'conditioner' => 2,
        'radio' => 5,
        'double-bed' => 33,
        'internet' => 54,
        'iron' => 55,
        'kitchen' => 59,
        'bar' => 69,
        'bath' => 85,
        'fridge' => 88,
        'telephone' => 107,
        'toilets' => 120,
        'shower' => 142,
        'free-wifi' => 900126,
        'wifi' => 900128
    ];

    private $confirmationPage;
    private $domainName;
    private $arrivalTime;
    private $departureTime;
    private $onlineFormUrl;
    private $locale;
    /** @var  DocumentManager $dm */
    private $dm;
    /** @var  UploaderHelper */
    private $uploaderHelper;
    /** @var  Currency $currencyHandler */
    private $currencyHandler;
    private $localCurrency;
    /** @var  OrderHandler $channelManagerHelper */
    private $orderHandler;

    public function __construct(
        $confirmationPageUrl,
        $domainName,
        $arrivalTime,
        $departureTime,
        $onlineFormUrl,
        $locale,
        DocumentManager $dm,
        UploaderHelper $uploaderHelper,
        Currency $currencyHandler,
        $localCurrency,
        OrderHandler $orderHandler
    )
    {
        $this->confirmationPage = $confirmationPageUrl;
        $this->domainName = $domainName;
        $this->arrivalTime = $arrivalTime;
        $this->departureTime = $departureTime;
        $this->onlineFormUrl = $onlineFormUrl;
        $this->locale = $locale;
        $this->dm = $dm;
        $this->uploaderHelper = $uploaderHelper;
        $this->currencyHandler = $currencyHandler;
        $this->localCurrency = $localCurrency;
        $this->orderHandler = $orderHandler;
    }

    /**
     * @param TripAdvisorConfig $config
     * @return array
     */
    public function formatHotelInventoryData(TripAdvisorConfig $config)
    {
        $hotel = $config->getHotel();
        $contactInformation = $hotel->getContactInformation();
        /** @var Hotel $hotel */
        $hotelData = [
            'ta_id' => (int)$config->getHotelId(),
            'partner_id' => $hotel->getId(),
            'name' => $hotel->getInternationalTitle(),
            'street' => $hotel->getInternationalStreetName(),
            'city' => $this->getTranslatableTitle($hotel->getCity()),
            'state' => $this->getTranslatableTitle($hotel->getRegion()),
            'country' => $this->getTranslatableTitle($hotel->getCountry()),
            'amenities' => $this->getAvailableHotelAmenities($hotel->getFacilities()),
            'url' => $config->getHotelUrl(),
            'room_types' => []
        ];
        if ($contactInformation->getPhoneNumber()) {
            $hotelData['phone'] = $contactInformation->getPhoneNumber();
        }
        if ($contactInformation->getEmail()) {
            $hotelData['email'] = $contactInformation->getEmail();
        }
        if ($hotel->getLatitude()) {
            $hotelData['latitude'] = $hotel->getLatitude();
        }
        if ($hotel->getLongitude()) {
            $hotelData['longitude'] = $hotel->getLongitude();
        }
        if ($hotel->getDescription()) {
            $hotelData['desc'] = $hotel->getDescription();
        }
        if ($hotel->getZipCode()) {
            $hotelData['postal_code'] = $hotel->getZipCode();
        }

        foreach ($hotel->getRoomTypes() as $roomType) {
            $tripAdvisorRoomType = $config->getTARoomTypeByMBHRoomTypeId($roomType->getId());
            if ($roomType->getDescription() && !is_null($tripAdvisorRoomType) && $tripAdvisorRoomType->getIsEnabled()) {
                $roomTypeData = [];
                if ($roomType->getDescription()) {
                    $description = str_replace('</p>', '', str_replace('<p>', '', $roomType->getDescription()));
                    $roomTypeData['desc'] = $description;
                }
                $hotelData['room_types'][$roomType->getName()] = $roomTypeData;
            }
        }

        return $hotelData;
    }

    /**
     * @param $configs
     * @param $availabilityData
     * @param $adultsChildrenCombinations
     * @param $language
     * @param $currency
     * @param $categories
     * @param $categoryModifiers
     * @param $requestedHotelsData
     * @return array
     */
    public function getHotelsAvailabilityData(
        $configs,
        $availabilityData,
        $adultsChildrenCombinations,
        $language,
        $currency,
        $categories,
        $categoryModifiers,
        $requestedHotelsData
    )
    {
        $hotelsAvailabilityData = [];
        foreach ($requestedHotelsData as $hotelId) {
            if (!isset($availabilityData[$hotelId])) {
                $hotelResponseData = ["response_type" => "unavailable"];
            } else {
                /** @var TripAdvisorConfig $config */
                $config = $configs[$hotelId];
                $hotelRoomRates = [];
                foreach ($availabilityData[$hotelId] as $roomTypeAvailabilityData) {
                    /** @var RoomType $roomType */
                    $roomType = $roomTypeAvailabilityData['roomType'];
                    /** @var TripAdvisorRoomType $taRoomType */
                    $taRoomType = $config->getTARoomTypeByMBHRoomTypeId($roomType->getId());
                    if ($taRoomType->getIsEnabled()) {
                        foreach ($roomTypeAvailabilityData['results'] as $searchResult) {
                            /** @var SearchResult $searchResult */
                            $tariff = $searchResult->getTariff();
                            $tripAdvisorTariff = $config->getTATariffByMBHTariffId($tariff->getId());
                            if (!is_null($tripAdvisorTariff) && $tripAdvisorTariff->getIsEnabled()) {
                                $hotelRoomRate = $this->getHotelRoomRates($searchResult, $adultsChildrenCombinations,
                                    $currency, $config, $language, $categoryModifiers['partner_booking_data']);
                                if ($hotelRoomRate !== false) {
                                    $hotelRoomRates[] = $hotelRoomRate;
                                }
                            }
                        }
                    }
                }
                if (count($hotelRoomRates) > 0) {
                    $hotelResponseData = [
                        "response_type" => "available",
                        'available' => [
                            'room_types' => $this->getRoomTypesData($config, $language, $categories['room_type_details'], $categoryModifiers['photos']),
                            'rate_plans' => $this->getRatePlansData($config, $categories['rate_plan_details']),
                            'room_rates' => $hotelRoomRates,
                            'partner_booking_details' => $this->getPartnerBookingDetails($config->getHotel())
                        ]
                    ];
                    if ($categories['hotel_details']) {
                        $hotelResponseData['available']['hotel_details']
                            = $this->getHotelDetails($config->getHotel(), $language, $categoryModifiers['photos']);
                    }
                } else {
                    $hotelResponseData = ["response_type" => "unavailable"];
                }
            }
            $hotelsAvailabilityData[] = $hotelResponseData;
        }

        return $hotelsAvailabilityData;
    }

    /**
     * @param Hotel $hotel
     * @return array
     */
    private function getPartnerBookingDetails(Hotel $hotel)
    {
        return [
            'accepted_credit_cards' => $this->getAcceptedCardTypes($hotel),
            'terms_and_conditions' => $hotel->getTripAdvisorConfig()->getTermsAndConditions(),
            'payment_policy' => $hotel->getTripAdvisorConfig()->getPaymentPolicy(),
            'customer_support' => $this->getCustomerSupportData($hotel->getContactInformation())
        ];
    }

    /**
     * @param TripAdvisorConfig $config
     * @param bool $isFull
     * @return array
     */
    private function getRatePlansData(TripAdvisorConfig $config, bool $isFull)
    {
        $tariffsData = [];
        foreach ($config->getTariffs() as $tariff) {
            if ($tariff->getIsEnabled()) {
                if ($isFull) {
                    $tariffData = $this->getTariffData($tariff);
                }
                $tariffData['persistent_rate_plan_code'] = $tariff->getTariff()->getId();
                $tariffsData[] = $tariffData;
            }
        }

        return $tariffsData;
    }

    /**
     * @param TripAdvisorConfig $config
     * @param $language
     * @param bool $isWithDetails
     * @param bool $isWithPhotos
     * @return array
     */
    private function getRoomTypesData(TripAdvisorConfig $config, $language, bool $isWithDetails, bool $isWithPhotos)
    {
        $roomTypesData = [];
        /** @var TripAdvisorRoomType $room */
        foreach ($config->getRooms() as $room) {
            if ($room->getIsEnabled()) {
                if ($isWithDetails) {
                    $roomTypeData = $this->getRoomTypeData($room->getRoomType(), $language, $isWithPhotos);
                }
                $roomTypeData["persistent_room_type_code"] = $room->getRoomType()->getId();
                $roomTypesData[] = $roomTypeData;
            }
        }

        return $roomTypesData;
    }

    public function formatSubmitBookingResponse(
        $bookingSession,
        $bookingCreationResult,
        $messages,
        Hotel $hotel
    )
    {
        $isSuccessfully = $bookingCreationResult instanceof Order;

        $response = [
            'reference_id' => $bookingSession,
            'status' => $isSuccessfully ? 'Success' : 'Failure',
            'customer_support' => $this->getCustomerSupportData($hotel->getContactInformation())
        ];

        if ($isSuccessfully) {
            $response['reservation'] =
                $this->getReservationData($bookingCreationResult, $hotel, $hotel->getTripAdvisorConfig(), true);
        } else {
            $response['problems'] = $messages;
        }

        return $response;
    }

    public function formatBookingVerificationResponse(?Order $order, $channelManagerOrderId, Hotel $hotel)
    {
        $isCreated = !is_null($order);
        $response = [
            'problems' => [],
            'reference_id' => $channelManagerOrderId,
            'status' => $isCreated ? 'Success' : 'Failure',
            'customer_support' => $this->getCustomerSupportData($hotel->getContactInformation())
        ];

        if ($isCreated) {
            $response['reservation'] = $this->getReservationData($order, $hotel, $hotel->getTripAdvisorConfig(), true);
        }

        return $response;
    }

    public function formatBookingCancelResponse($removalStatus, Hotel $hotel, $orderId)
    {
        return [
            'partner_hotel_code' => $hotel->getId(),
            'reservation_id' => $orderId,
            "status" => $removalStatus,
            'cancellation_number' => $orderId,
            'customer_support' => $this->getCustomerSupportData($hotel->getContactInformation())
        ];
    }


    private function getAvailableHotelAmenities($amenities)
    {
        $availableAmenities = [];
        foreach ($amenities as $amenity) {
            if (in_array($amenity, array_keys(self::HOTEL_AMENITIES)) && !in_array($amenity, $availableAmenities)) {
                $availableAmenities[] = self::HOTEL_AMENITIES[$amenity];
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

    private function getCustomerSupportData(ContactInfo $contactInfo)
    {
        return [
            'phone_numbers' => [
                [
                    "contact" => $contactInfo->getPhoneNumber(),
                    "description" => "Support phone line"
                ]
            ],
            'emails' => [
                [
                    'contact' => $contactInfo->getEmail(),
                    'description' => 'Support email'
                ]
            ]
        ];
    }

    private function getTariffData(TripAdvisorTariff $tripAdvisorTariff)
    {
        $tariff = $tripAdvisorTariff->getTariff();

        /** @var Tariff $tariff */
        $tariffData = [
            'name' => $tariff->getName(),
            'description' => $tariff->getDescription() ? $tariff->getDescription() : $tariff->getName(),
            'rate_amenities' => $this->getRateAmenities($tariff),
            'refundable' => $tripAdvisorTariff->getRefundableType(),
            'cancellation_rules' => $this->getCancellationRules($tripAdvisorTariff),
            'meal_plan' => $this->getRateMealPlanes($tariff)
        ];

        return $tariffData;
    }

    /**
     * @param TripAdvisorTariff $tariff
     * @return array
     */
    private function getCancellationRules(TripAdvisorTariff $tariff)
    {
        if ($tariff->getRefundableType() == 'full') {
            $deadLineDate = (new \DateTime('midnight'))->modify('+ ' . $tariff->getDeadline() . 'days');
            $result = [
                'penalty_exists' => $tariff->getIsPenaltyExists(),
                'policy_info' => $tariff->getPolicyInfo(),
                'deadline' => $deadLineDate->format('Y-m-d\TH:i:s')
            ];
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param Hotel $hotel
     * @param TripAdvisorConfig $config
     * @param $isWithPhotos
     * @return array
     */
    private function getReservationData(Order $order, Hotel $hotel, TripAdvisorConfig $config, $isWithPhotos)
    {
        /** @var Package $orderFirstPackage */
        $orderFirstPackage = $order->getFirstPackage();
        /** @var Tourist $payer */
        $payer = $order->getPayer();
        $orderAdditionalData = $order->getAdditionalData();
        $countryCode = $orderAdditionalData['countryCode'];
        $currency = $orderAdditionalData['currency'];
        $language = $orderAdditionalData['language'];

        $cashDocumentsData = [];
        $this->dm->refresh($order);
        foreach ($order->getCashDocuments() as $cashDocument) {
            /** @var CashDocument $cashDocument */
            if ($cashDocument->getOperation() == 'in') {
                $cashDocType = 'rate';
                $description = 'This is the base rate.';
            } else {
                $cashDocType = 'fee';
                $description = 'This is fee';
            }
            $cashDocumentsData[] = [
                'price' => $this->getPriceObject(
                    $this->currencyHandler->convertFromRub($cashDocument->getTotal(), $currency), $currency),
                'type' => $cashDocType,
                'paid_at_checkout' => $cashDocument->getMethod() == 'cash',
                'description' => $description
            ];
        }

        $convertedOrderPrice = $this->currencyHandler->convertFromRub($order->getPrice(), $currency);
        $finalPrice = $this->getFinalPriceData($config->getPaymentType(), $convertedOrderPrice, $currency);
        $reservationData = [
            'reservation_id' => (string)$order->getId(),
            'status' => $this->getOrderStatus($order),
            'confirmation_url' => $this->confirmationPage . '?'
                . http_build_query([
                    'sessionId' => $order->getChannelManagerId(),
                    'order' => $order->getId(),
                    'hotelId' => $hotel->getId()
                ]),
            'checkin_date' => $orderFirstPackage->getBegin()->format(self::TRIP_ADVISOR_DATE_FORMAT),
            'checkout_date' => $orderFirstPackage->getEnd()->format(self::TRIP_ADVISOR_DATE_FORMAT),
            'partner_hotel_code' => $orderFirstPackage->getHotel()->getId(),
            'hotel' => $this->getHotelDetails($orderFirstPackage->getHotel(), $language, $isWithPhotos),
            'customer' => [
                'first_name' => $payer->getFirstName(),
                'last_name' => $payer->getLastName(),
                'phone_number' => $payer->getPhone(),
                'email' => $payer->getEmail(),
                'country' => $countryCode
            ],
            'rooms' => $this->getRoomStayData($order->getPackages()->toArray()),
            'receipt' => [
                'line_items' => $cashDocumentsData,
                'final_price_at_booking' => $finalPrice['atBooking'],
                'final_price_at_checkout' => $finalPrice['atCheckOut']
            ]
        ];

        return $reservationData;
    }

    private function getFinalPriceData($paymentType, $price, $currency)
    {
        $finalPriceAtBooking = 0;
        $finalPriceAtCheckOut = 0;
        switch ($paymentType) {
            case 'in_hotel':
                $finalPriceAtCheckOut = $price;
                break;
            case 'online_full':
                $finalPriceAtBooking = $price;
                break;
            case 'online_half':
                $finalPriceAtBooking = $price / 2;
                $finalPriceAtCheckOut = $price / 2;
                break;
        }

        return [
            'atCheckOut' => $this->getPriceObject($finalPriceAtCheckOut, $currency),
            'atBooking' => $this->getPriceObject($finalPriceAtBooking, $currency)
        ];
    }

    private function getOrderStatus(Order $order)
    {
        if ($order->isDeleted()) {
            return 'Cancelled';
        }
        if ($order->getFirstPackage()->getIsCheckOut()) {
            return 'CheckedOut';
        }
        if ($order->getFirstPackage()->getIsCheckIn()) {
            return 'CheckedIn';
        }

        return 'Booked';
    }

    /**
     * @param Package[] $packages
     * @return array
     */
    private function getRoomStayData($packages)
    {
        $roomStayData = [];
        usort($packages, function ($packageOne, $packageTwo) {
            /** @var Package $packageOne */
            /** @var Package $packageTwo */
            return ($packageOne->getId() < $packageTwo->getId()) ? -1 : 1;
        });

        $groupedPackages = [];
        $currentPackageGroup = [];
        for ($i = (count($packages) - 1); $i >= 0; $i--) {
            $package = $packages[$i];
            $currentPackageGroup[] = $package;
            if ($package->getTourists()->count() > 0) {
                $groupedPackages[] = $currentPackageGroup;
                $currentPackageGroup = [];
            }
        }

        foreach ($groupedPackages as $groupedByPartyPackages) {
            $adultsCount = 0;
            foreach ($groupedByPartyPackages as $package) {
                /** @var Package $package */
                $adultsCount += $package->getAdults();
            }
            /** @var Package $mainDataPackage */
            $mainDataPackage = end($groupedByPartyPackages);
            foreach ($mainDataPackage->getChildAges() as $childAge) {
                if ($childAge >= $mainDataPackage->getTariff()->getChildAge()) {
                    $adultsCount--;
                }
            }
            $mainTourist = $mainDataPackage->getTourists()[0];
            $roomStayData[] = $this->getRoomsData($adultsCount, $mainDataPackage->getChildAges(),
                $mainTourist->getFirstName(), $mainTourist->getLastName());
        }

        return $roomStayData;
    }

    private function getRoomsData($adultsCount, $childrenAges, $firstName, $lastName)
    {
        return [
            'party' => [
                'adults' => $adultsCount,
                'children' => $childrenAges
            ],
            'traveler_first_name' => $firstName,
            'traveler_last_name' => $lastName
        ];
    }

    private function getPriceObject($priceValue, $currency)
    {
        return ['requested_currency_price' => [
            'amount' => $priceValue,
            'currency' => $currency
        ]
        ];
    }

    private function getHotelDetails(Hotel $hotel, $requestedLocale, $withPhotos)
    {
        if ($this->isRequestedLanguageLocal($requestedLocale)) {
            $hotelName = $hotel->getName();
            $streetName = $hotel->getStreet();
            $cityName = $hotel->getCity()->getName();
            $regionName = $hotel->getRegion()->getName();
        } else {
            $hotelName = $hotel->getInternationalTitle();
            $streetName = $hotel->getInternationalStreetName();
            $cityName = $this->getTranslatableTitle($hotel->getCity());
            $regionName = $this->getTranslatableTitle($hotel->getRegion());
        }

        $hotelDetails = [
            'name' => $hotelName,
            'address1' => $streetName . ', ' . $hotel->getHouse(),
            'city' => $cityName,
            'state' => $regionName,
            'country' => $hotel->getCountry()->getIsoAlpha2(),
            'phone' => $hotel->getContactInformation()->getPhoneNumber(),
            'url' => $hotel->getTripAdvisorConfig()->getHotelUrl(),
            'hotel_amenities' => $this->getHotelAmenities($hotel),
            'photos' => $this->getHotelPhotoData($hotel),
            'checkinout_policy' => $hotel->getCheckinoutPolicy(),
            'checkin_time' => $this->arrivalTime . ':00',
            'checkout_time' => $this->departureTime . ':00',
            'hotel_smoking_policy' => ['custom' => [$hotel->getSmokingPolicy()], 'standard' => []],
        ];
        if ($withPhotos) {
            $hotelDetails['photos'] = $this->getHotelPhotoData($hotel);
        }
        if ($hotel->getTripAdvisorConfig()->getChildPolicy()) {
            $hotelDetails['child_policy'] = $hotel->getTripAdvisorConfig()->getChildPolicy();
        }
        if ($hotel->getLatitude()) {
            $hotelDetails['latitude'] = $hotel->getLatitude();
        }
        if ($hotel->getLongitude()) {
            $hotelDetails['longitude'] = $hotel->getLongitude();
        }
        if ($hotel->getZipCode()) {
            $hotelDetails['postal_code'] = $hotel->getZipCode();
        }

        return $hotelDetails;
    }

    private function getHotelRoomRates(
        SearchResult $result,
        $adultChildrenCombinations,
        $currency,
        TripAdvisorConfig $config,
        $requestedLanguage,
        bool $isFull
    )
    {
        $priceData = $this->getPriceDataByAdultsChildrenCombinations($adultChildrenCombinations, $result);
        if (!$priceData) {
            return false;
        }
        $resultPrice = $this->currencyHandler->convertFromRub($priceData['price'], $currency);
        $finalPriceData = $this->getFinalPriceData($config->getPaymentType(), $resultPrice, $currency);
        $hotelRoomRates = [
            'persistent_room_rate_code' => $result->getRoomType()->getId() . '_' . $result->getTariff()->getId(),
            'hotel_room_type_code' => $result->getRoomType()->getId(),
            'hotel_rate_plan_code' => $result->getTariff()->getId(),
            'final_price_at_booking' => $finalPriceData['atBooking'],
            'final_price_at_checkout' => $finalPriceData['atCheckOut'],
            'line_items' => $this->getLineItems($resultPrice, $currency, $config->getPaymentType()),
            'payment_policy' => $config->getPaymentPolicy(),
            'rooms_remaining' => $result->getRoomsCount(),
        ];
        $firstAdultsChildrenCounts = current($this->orderHandler->getAdultsChildrenCountByCombinations(
            $adultChildrenCombinations[0], $result->getTariff()));
        $hotelRoomRate['url'] = $this->getSearchUrl($result->getRoomType()->getId(), $result->getBegin(), $result->getEnd(),
            $firstAdultsChildrenCounts['adultsCount'], $firstAdultsChildrenCounts['childrenCount'],
            $requestedLanguage);
        if ($isFull) {
            $hotelRoomRates['partner_data'] = [
                'pricesByDate' => $priceData['pricesByDate'],
                'roomTypeId' => $result->getRoomType()->getId(),
                'tariffId' => $result->getTariff()->getId(),
                'hotelId' => $result->getRoomType()->getHotel()->getId(),
                'language' => $requestedLanguage
            ];
        }

        return $hotelRoomRates;
    }

    private function getPriceDataByAdultsChildrenCombinations($adultsChildrenCombinations, SearchResult $result)
    {
        $roomType = $result->getRoomType();
        $tariff = $result->getTariff();
        $adultsChildrenCounts = $this->orderHandler->getAdultsChildrenCountByCombinations($adultsChildrenCombinations,
            $tariff);
        //Все ли кобминации количеств детей и взрослых имеют цену?
        $isAllHavenPrice = true;
        $resultPriceData = ['price' => 0, 'roomCount' => 0];
        foreach ($adultsChildrenCounts as $estimatedAdultsChildrenCount) {
            $dividedAdultsChildrenCombinations =
                $this->orderHandler->getDividedAdultsChildrenCombinations(
                    $estimatedAdultsChildrenCount['adultsCount'], $estimatedAdultsChildrenCount['childrenCount'],
                    $roomType->getTotalPlaces());
            foreach ($dividedAdultsChildrenCombinations as $combination) {
                $adultsChildrenCounts = $roomType->getAdultsChildrenCombination(
                    $combination['adults'], isset($combination['children']) ? $combination['children'] : 0);
                $adultsCount = $adultsChildrenCounts['adults'];
                $childrenCount = $adultsChildrenCounts['children'];
                $price = $result->getPrice($adultsCount, $childrenCount);
                if ($price) {
                    $resultPriceData['price'] += $price;
                    $resultPriceData['pricesByDate'][$adultsCount . '_' . $childrenCount] =
                        $result->getPricesByDate($adultsCount, $childrenCount);
                    $resultPriceData['roomCount']++;
                } else {
                    $isAllHavenPrice = false;
                    break 2;
                }
            }
        }

        if ($isAllHavenPrice && $result->getRoomsCount() >= count($adultsChildrenCounts)) {
            return $resultPriceData;
        }

        return false;
    }

    private function getRoomTypeData(RoomType $roomType, $locale, $withPhotos)
    {
        if ($this->isRequestedLanguageLocal($locale)) {
            $roomTypeName = $roomType->getName();
        } else {
            $roomTypeName = $roomType->getInternationalTitle();
        }
        $roomTypeResponseData = [
            'name' => $roomTypeName,
            'description' => $roomType->getDescription()
                ? $roomType->getDescription() : $roomTypeName,
            'room_amenities' => $this->getRoomAmenities($roomType),
            'max_occupancy' => [
                'number_of_adults' => $roomType->getTotalPlaces(),
                'number_of_children' => $roomType->getTotalPlaces() - 1
            ],
            'bed_configurations' => [$this->getBedConfiguration($roomType)],
            'extra_bed_configurations' => [$this->getBedConfiguration($roomType)],
            'room_smoking_policy' => $roomType->getIsSmoking() ? 'smoking' : 'non_smoking',
            'room_view_type' => $this->getRoomViewTypes($roomType)
        ];
        if ($withPhotos) {
            $roomTypeResponseData['photos'] = $this->getRoomTypePhotoData($roomType);
        }

        if ($roomType->getRoomSpace()) {
            $roomTypeResponseData['room_size_value'] = (int)$roomType->getRoomSpace();
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
            $roomTypeImageData['url'] = 'https://' . $this->domainName . '/' . $image->getPath();
            if ($image->getWidth()) {
                $roomTypeImageData['width'] = (int)$image->getWidth();
            }
            if ($image->getHeight()) {
                $roomTypeImageData['height'] = (int)$image->getHeight();
            }
            $imagesData[] = $roomTypeImageData;
        }

        return $imagesData;
    }

    private function getRoomAmenities(RoomType $roomType)
    {
        return $this->getSortedAmenities($roomType->getFacilities(), self::ROOM_AMENITIES);
    }

    private function getHotelPhotoData(Hotel $hotel)
    {
        $imagesData = [];
        foreach ($hotel->getImages() as $image) {
            $roomTypeImageData = [];
            /** @var Image $image */
            $roomTypeImageData['url'] = 'https://' . $this->domainName . $this->uploaderHelper->asset($image, 'imageFile',
                    Image::class);
            if ($image->getWidth()) {
                $roomTypeImageData['width'] = (int)$image->getWidth();
            }
            if ($image->getHeight()) {
                $roomTypeImageData['height'] = (int)$image->getHeight();
            }
            $imagesData[] = $roomTypeImageData;
        }

        return $imagesData;
    }

    private function getTranslatableTitle(TranslatableInterface $entity)
    {
        $entity->setTranslatableLocale('en_EN');
        $this->dm->refresh($entity);

        return $entity->getTitle();
    }

    private function getHotelAmenities(Hotel $hotel)
    {
        return $this->getSortedAmenities($hotel->getFacilities(), self::ROOM_AMENITIES);
    }

    private function getSortedAmenities($specifiedAmenities, $standardAmenities)
    {
        $amenities = [
            'standard' => [],
            'custom' => []
        ];

        foreach ($specifiedAmenities as $facility) {
            if (in_array($facility, array_keys($standardAmenities))) {
                if (!in_array($standardAmenities[$facility], $amenities['standard'])) {
                    $amenities['standard'][] = $standardAmenities[$facility];
                }
            } else {
                if (!in_array($facility, $amenities['custom'])) {
                    $amenities['custom'][] = $facility;
                }
            }
        }

        return $amenities;
    }

    private function isRequestedLanguageLocal($requestedLocale)
    {
        return $this->locale == substr($requestedLocale, strpos($requestedLocale, '_') + 1);
    }

    private function getBedConfiguration(RoomType $roomType)
    {
        $bedConfiguration = [];
        if (in_array('bed', $roomType->getFacilities())) {
            $bedConfiguration[] = [
                'type' => 'standard',
                'code' => 9,
                'count' => 1
            ];
        }
        if (in_array('double-bed', $roomType->getFacilities())) {
            $bedConfiguration[] = [
                'type' => 'standard',
                'code' => 1,
                'count' => 1
            ];
        }

        return $bedConfiguration;
    }

    private function getRoomViewTypes(RoomType $roomType)
    {
        $viewTypes = ['standard' => [], 'custom' => []];
        foreach ($roomType->getRoomViewsTypes() as $roomViewType) {
            if ($roomViewType->getOpenTravelCode()) {
                $viewTypes['standard'][] = $roomViewType->getOpenTravelCode();
            } else {
                $viewTypes['custom'][] = $roomViewType->getCodeName();
            }
        }

        return $viewTypes;
    }

    private function getRateAmenities(Tariff $tariff)
    {
        $defaultServiceNames = [];
        foreach ($tariff->getDefaultServices() as $defaultService) {
            $serviceCode = $defaultService->getService()->getCode();
            if (!in_array($serviceCode, array_keys(self::RATE_MEAL_TYPES))) {
                $defaultServiceNames[] = $defaultService->getService()->getCode();
            }
        }

        return $this->getSortedAmenities($defaultServiceNames, self::RATE_AMENITIES);
    }

    private function getRateMealPlanes(Tariff $tariff)
    {
        $rateMealPlanes = ['custom' => [], 'standard' => []];
        foreach ($tariff->getDefaultServices() as $service) {
            /** @var TariffService $service */
            $serviceCode = $service->getService()->getCode();

            if (in_array($serviceCode, array_keys(self::RATE_MEAL_TYPES))) {
                $rateMealPlanes['standard'][] = self::RATE_MEAL_TYPES[$serviceCode];
            }
        }

        return $rateMealPlanes;
    }

    private function getLineItems($price, $currency, $paymentType)
    {
        $lineItems = [];
        switch ($paymentType) {
            case 'in_hotel':
                $lineItems[] = [
                    "price" => $this->getPriceObject($price, $currency),
                    'type' => 'rate',
                    'paid_at_checkout' => true,
                    'description' => 'Base rate'
                ];
                break;
            case 'online_full':
                $lineItems[] = $lineItems[] = [
                    "price" => $this->getPriceObject($price, $currency),
                    'type' => 'rate',
                    'paid_at_checkout' => false,
                    'description' => 'Base rate'
                ];
                break;
            case 'online_half':
                $lineItems[] = [
                    "price" => $this->getPriceObject($price / 2, $currency),
                    'type' => 'rate',
                    'paid_at_checkout' => true,
                    'description' => 'Base rate'
                ];
                $lineItems[] = [
                    "price" => $this->getPriceObject($price / 2, $currency),
                    'type' => 'rate',
                    'paid_at_checkout' => false,
                    'description' => 'Base rate'
                ];
                break;
        }

        return $lineItems;
    }

    private function getAcceptedCardTypes(Hotel $hotel)
    {
        $acceptedCardTypeCodes = [];
        foreach ($hotel->getAcceptedCardTypes() as $acceptedCardType) {
            /** @var CardType $acceptedCardType */
            $cardCode = $acceptedCardType->getCardCode();
            if (in_array(ucfirst(strtolower($cardCode)), self::TRIP_ADVISOR_AVAILABLE_CARD_TYPES)
                && !in_array($cardCode, $acceptedCardTypeCodes)
            ) {
                $acceptedCardTypeCodes[] = $acceptedCardType->getCardCode();
            }
        }

        return $acceptedCardTypeCodes;
    }
}