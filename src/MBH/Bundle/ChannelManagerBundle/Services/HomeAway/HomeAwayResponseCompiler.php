<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\Currency;
use MBH\Bundle\CashBundle\Document\CardType;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom;
use MBH\Bundle\ChannelManagerBundle\Services\ChannelManagerHelper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\Routing\Router;

class HomeAwayResponseCompiler
{
    const HOME_AWAY_DATE_FORMAT = 'Y-m-d';
    const HOME_AWAY_DATE_TIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    /** @var  ChannelManagerHelper $channelManagerHelper */
    private $channelManagerHelper;
    /** @var  HomeAwayDataFormatter $dataFormatter */
    private $dataFormatter;
    private $localCurrency;
    /** @var  Currency $currencyHandler */
    private $currencyHandler;
    /** @var  Router $router */
    private $router;
    private $assignedId;
    private $domainName;
    /** @var  DocumentManager $dm */
    private $dm;

    public function __construct(
        HomeAwayDataFormatter $dataFormatter,
        $localCurrency,
        Currency $currencyHandler,
        ChannelManagerHelper $channelManagerHelper,
        Router $router,
        $assignedId,
        $domainName,
        DocumentManager $dm
    ) {
        $this->dataFormatter = $dataFormatter;
        $this->localCurrency = $localCurrency;
        $this->currencyHandler = $currencyHandler;
        $this->channelManagerHelper = $channelManagerHelper;
        $this->router = $router;
        $this->assignedId = $assignedId;
        $this->domainName = $domainName;
        $this->dm = $dm;
    }

    /**
     * @param HomeAwayConfig $config
     * @return array
     */
    public function formatConfigData(HomeAwayConfig $config)
    {
        $roomsData = [];
        /** @var HomeAwayRoom $room */
        foreach ($config->getRooms() as $room) {
            $roomType = $room->getRoomType();
            $roomsData[$roomType->getId()] = [
                'roomId' => $roomType->getId(),
                'isActive' => $roomType->getIsEnabled(),
                'updatedDate' => $roomType->getUpdatedAt()->format(self::HOME_AWAY_DATE_TIME_FORMAT),
                'availabilityUrl' => $this->getListingDataUrl('homeaway_availability', $roomType, $config),
                'ratesUrl' => $this->getListingDataUrl('homeaway_rates', $roomType, $config),
                'listingUrl' => $this->getListingDataUrl('homeaway_listing', $roomType, $config)
            ];
        }

        $configData = [
            'assignedId' => $config->getAssignedId(),
            'isEnabled' => $config->getIsEnabled(),
            'hotelId' => $config->getHotel()->getId(),
            'roomsData' => $roomsData
        ];

        return $configData;
    }

    /**
     * @param RoomType $roomType
     * @param HomeAwayConfig $config
     * @return mixed
     */
    public function formatListingData(RoomType $roomType, HomeAwayConfig $config)
    {
        $rootElement = new \SimpleXMLElement('<listing/>');
        $rootElement->addChild('externalId', $roomType->getId());
        $rootElement->addAttribute('active', $roomType->getIsEnabled() ? 'true' : 'false');

        $haRoomType = $this->getHARoomByRoomType($config, $roomType);
        $hotel = $roomType->getHotel();
        $adContentNode = $rootElement->addChild('adContent');
        $adContentNode->addChild('description', $roomType->getDescription());
        $adContentNode->addChild('propertyName', $roomType->getName());
        $adContentNode->addChild('headline', $haRoomType->getHeadLine());

        $locationNode = $rootElement->addChild('location');
        $addressNode = $locationNode->addChild('address');
        $addressNode->addChild('address1', $hotel->getHouse().' '.$hotel->getInternationalStreetName());

        $city = $hotel->getCity();
        $city->setTranslatableLocale('en_EN');
        $this->dm->refresh($city);
        $addressNode->addChild('city', $city->getTitle());

        $country = $hotel->getCountry();
        $country->setTranslatableLocale('en_EN');
        $this->dm->refresh($country);
        $addressNode->addChild('country', $country->getTitle());

        $geoCodeNode = $locationNode->addChild('geoCode');
        $geoCodeNode->addChild('latitude', $hotel->getLatitude());
        $geoCodeNode->addChild('longitude', $hotel->getLongitude());;
        $locationNode->addChild('showExactLocation', 'true');

        $imagesNode = $rootElement->addChild('images');
        foreach ($roomType->getImages() as $image) {
            $imageNode = $imagesNode->addChild('image');
            $imageNode->addChild('externalId', $image->getId());
            $imageNode->addChild('uri', $this->domainName.'/'.$image->getPath());
        }

        $unitsNode = $rootElement->addChild('units');
        $unitNode = $unitsNode->addChild('unit');
        $unitNode->addChild('externalId', $roomType->getId());
        if ($roomType->getRoomSpace()) {
            $unitNode->addChild('area', $roomType->getRoomSpace());
            $unitNode->addChild('areaUnit', 'METERS_SQUARED');
        }

        $bathRoomsNode = $unitNode->addChild('bathrooms');
        $bathRoomNode = $bathRoomsNode->addChild('bathroom');
        $bathRoomNode->addChild('roomSubType', $haRoomType->getBathSubType());

        $bedroomsNode = $unitNode->addChild('bedrooms');
        $bedroomNode = $bedroomsNode->addChild('bedroom');
        $bedroomNode->addChild('roomSubType', $haRoomType->getBedRoomSubType());

        $unitNode->addChild('maxSleep', $roomType->getTotalPlaces());
        $unitNode->addChild('propertyType', $haRoomType->getListingType());

        $monetaryInfoNode = $unitNode->addChild('unitMonetaryInformation');
        $upperLocalCurrency = strtoupper($this->localCurrency);
        $currency = $this->getAvailableCurrency($upperLocalCurrency);
        $monetaryInfoNode->addChild('currency', $currency);

        return $rootElement->asXML();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param $roomTypeId
     * @param $priceCaches
     * @return mixed
     */
    public function formatRatePeriodsData(
        \DateTime $begin,
        \DateTime $end,
        $roomTypeId,
        $priceCaches
    ) {
        $ratePeriods = $this->channelManagerHelper->getPeriodsFromDayEntities($begin, $end, $priceCaches, ['getPrice']);

        $unitRatePeriodsNode = new \SimpleXMLElement('<unitRatePeriods/>');
        $unitRatePeriodsNode->addChild('listingExternalId', $roomTypeId);
        $unitRatePeriodsNode->addChild('unitExternalId', $roomTypeId);

        $ratePeriodsElement = $unitRatePeriodsNode->addChild('ratePeriods');
        foreach ($ratePeriods as $ratePeriod) {
            /** @var \SimpleXMLElement $ratePeriodElement */
            $ratePeriodElement = $ratePeriodsElement->addChild('ratePeriod');
            $dateRangeElement = $ratePeriodElement->addChild('dateRange');
            $dateRangeElement->addChild('beginDate', $ratePeriod['begin']->format(self::HOME_AWAY_DATE_FORMAT));
            $dateRangeElement->addChild('endDate', $ratePeriod['end']->format(self::HOME_AWAY_DATE_FORMAT));

            $ratesElement = $ratePeriodElement->addChild('rates');
            $rateElement = $ratesElement->addChild('rate');
            $rateElement->addAttribute('rateType', 'EXTRA_NIGHT');
            /** @var PriceCache $priceCache */
            $priceCache = $ratePeriod['entity'];
            $price = is_null($priceCache) ? 0 : $priceCache->getPrice();
            $amountElement = $rateElement->addChild('amount', $price);
            $amountElement->addAttribute('currency', $this->localCurrency);
        }

        return $unitRatePeriodsNode->asXML();
    }

    /**
     * @param $mbhRoomTypeId
     * @param $priceCaches
     * @param $restrictions
     * @param $roomCaches
     * @return mixed
     */
    public function formatAvailabilityData(
        $mbhRoomTypeId,
        $priceCaches,
        $restrictions,
        $roomCaches
    ) {
        $beginDate = $this->getBeginDate();
        $endDate = $this->getEndDate();

        $availabilityElement = new \SimpleXMLElement('<unitAvailabilityEntities/>');
        $availabilityElement->addChild('listingExternalId', $mbhRoomTypeId);
        $availabilityElement->addChild('unitExternalId', $mbhRoomTypeId);
        $unitAvailabilityElement = $availabilityElement->addChild('unitAvailability');

        $dateRangeElement = $availabilityElement->addChild('dateRange');
        $dateRangeElement->addChild('beginDate', $beginDate->format(self::HOME_AWAY_DATE_FORMAT));
        $dateRangeElement->addChild('endDate', $endDate->format(self::HOME_AWAY_DATE_FORMAT));

        $unitAvailabilityElement->addChild('availabilityDefault', 'N');
        $availabilityConfigElement = $unitAvailabilityElement->addChild('unitAvailabilityConfiguration');

        $availabilityData = $this->getAvailabilityData($beginDate, $endDate, $roomCaches, $restrictions, $priceCaches);
        $availabilityConfigElement->addChild('availability', $availabilityData['availability']);
        $availabilityConfigElement->addChild('maxStay', $availabilityData['maxStay']);
        $availabilityConfigElement->addChild('minStay', $availabilityData['minStay']);

        return $availabilityElement->asXML();
    }

    /**
     * @param HomeAwayRoom $homeAwayRoomType
     * @param $adultCount
     * @param $childrenCount
     * @param $documentVersion
     * @param HomeAwayConfig $config
     * @param $searchResults
     * @return mixed
     */
    public function getQuoteResponse(
        HomeAwayRoom $homeAwayRoomType,
        $adultCount,
        $childrenCount,
        $documentVersion,
        HomeAwayConfig $config,
        $searchResults
    ) {
        $quoteResponse = new \SimpleXMLElement('<quoteResponse/>');
        $quoteResponse->addChild('documentVersion', $documentVersion);
        $responseDetailsElement = $quoteResponse->addChild('quoteResponseDetails');
        $responseDetailsElement->addChild('locale', $config->getLocale());
        $orderListElement = $responseDetailsElement->addChild('orderList');
        $orderElement = $orderListElement->addChild('order');

        // В HomeAway валюта может быть одной из: USD, EUR, GBP;
        $upperLocaleCurrency = strtoupper($this->localCurrency);
        $isLocaleCurrencyAvailable = in_array($upperLocaleCurrency, ['USD', 'EUR', 'GBP']);
        $currency = $isLocaleCurrencyAvailable ? $upperLocaleCurrency : 'USD';
        $orderElement->addChild('currency', $currency);

        $orderItemListElement = $orderElement->addChild('orderItemList');

        if (count($searchResults) > 0) {
            $roomTypeQuoteData = current($searchResults);
            //Поиск ведется только по 1 синхронизируемому тарифу для 1 типа номера
            $searchResult = current($roomTypeQuoteData['results']);
            /** @var SearchResult $searchResult */
            $orderItemElement = $orderItemListElement->addChild('orderItem');
            $orderItemElement->addChild('feeType', 'RENTAL');
            $orderItemElement->addChild('name', 'Rent');

            $price = $searchResult->getPrice($adultCount, $childrenCount);
            $resultPrice = $isLocaleCurrencyAvailable
                ? $price : $this->currencyHandler->convertFromRub($price, $currency);
            $preTaxAmountElement = $orderItemElement->addChild('preTaxAmount', $resultPrice);
            $preTaxAmountElement->addAttribute('currency', $currency);
            $totalAmountElement = $orderItemElement->addChild('totalAmount', $resultPrice);
            $totalAmountElement->addAttribute('currency', $currency);

            $this->addPaymentScheduleNode($orderItemListElement, $searchResult->getBegin(), $config, $price, $currency);

            $cancellationPolicyElement = $orderItemListElement->addChild('reservationCancellationPolicy');
            $cancellationPolicyElement->addChild('description', $config->getCancellationPolicy());
        }

        $rentalAgreementElement = $responseDetailsElement->addChild('rentalAgreement');
        $rentalAgreementElement->addChild('agreementText', $homeAwayRoomType->getRentalAgreement());

        return $quoteResponse->asXML();
    }

    /**
     * @param $documentVersion
     * @param $bookingResult
     * @param $messages
     * @return mixed
     * @throws Exception
     */
    public function getBookingResponse($documentVersion, $bookingResult, $messages)
    {
        $bookingResponse = new \SimpleXMLElement('<bookingResponse/>');
        $bookingResponse->addChild('documentVersion', $documentVersion);

        if ($bookingResult instanceof Order) {
            $reservationData = $bookingResult->getPackages()[0];
            $hotel = $reservationData->getHotel();
            /** @var HomeAwayConfig $config */
            $config = $hotel->getHomeAwayConfig();

            $responseDetailsNode = $bookingResponse->addChild('bookingResponseDetails');
            $responseDetailsNode->addChild('externalId', $reservationData->getNumberWithPrefix());

            /** @var Tourist $payer */
            $payer = $bookingResult->getPayer();
            $responseDetailsNode->addChild('guestProfileExternalId', $payer->getId());
            $responseDetailsNode->addChild('locale', $config->getLocale());
            $orderListNode = $responseDetailsNode->addChild('orderList');
            $orderNode = $orderListNode->addChild('order');

            $upperLocalCurrency = strtoupper($this->localCurrency);
            $currency = $this->getAvailableCurrency($upperLocalCurrency);
            $orderNode->addChild('currency', $currency);
            $orderNode->addChild('externalId', $bookingResult->getId());

            $orderItemListNode = $orderNode->addChild('orderItemList');
            foreach ($bookingResult->getCashDocuments() as $cashDocument) {
                /** @var CashDocument $cashDocument */
                $orderItemNode = $orderItemListNode->addChild('orderItem');
                $orderItemNode->addChild('externalId', $cashDocument->getId());
                $feeType = $cashDocument->getOperation() == 'in' ? 'RENTAL' : 'DISCOUNT';
                $orderItemNode->addChild('feeType', $feeType);
                $orderItemNode->addChild('Name', $feeType);

                $price = $this->isLocalCurrencyAvailable($upperLocalCurrency)
                    ? $cashDocument->getTotal()
                    //TODO: Сменить на конвертирование из локальной валюты
                    : $this->currencyHandler->convertFromRub($cashDocument->getTotal(), $currency);

                //Если кешдокумент содержит информацию о расходе, то значение суммы должно быть отрицательным
                if ($feeType == 'DISCOUNT') {
                    $price = $price * (-1);
                }
                $preTaxAmountNode = $orderItemNode->addChild('preTaxAmount', $price);
                $preTaxAmountNode->addAttribute('currency', $currency);
                $orderItemNode->addChild('status', 'PENDING');
                $totalAmountNode = $orderItemNode->addChild('totalAmount', $price);
                $totalAmountNode->addAttribute('currency', $currency);
            }

            $this->addPaymentScheduleNode(
                $orderNode,
                $reservationData->getBegin(),
                $config,
                $bookingResult->getPrice(),
                $currency
            );
            $orderNode->addChild('reservationCancellationPolicy', $config->getCancellationPolicy());

            $currentHomeAwayRoom = null;
            foreach ($hotel->getHomeAwayConfig()->getRooms() as $homeAwayRoom) {
                /** @var HomeAwayRoom $homeAwayRoom */
                if ($homeAwayRoom->getRoomType()->getId() == $reservationData->getRoomType()->getId()) {
                    $currentHomeAwayRoom = $homeAwayRoom;
                }
            }
            if (is_null($currentHomeAwayRoom)) {
                //TODO: Изменить
                throw new Exception();
            }

            $responseDetailsNode->addChild('rentalAgreement', $currentHomeAwayRoom->getRentalAgreement());
            $responseDetailsNode->addChild('reservationPaymentStatus', $this->getPaymentStatus($bookingResult));

            $reservationNode = $responseDetailsNode->addChild('reservation');
            $reservationNode->addChild('numberOfAdults', $reservationData->getAdults());
            $reservationNode->addChild('numberOfChildren', $reservationData->getChildren());
            $reservationDatesNode = $reservationNode->addChild('reservationDates');
            $reservationDatesNode->addChild(
                'beginDate',
                $reservationData->getBegin()->format(self::HOME_AWAY_DATE_FORMAT)
            );
            $reservationDatesNode->addChild('endDate', $reservationData->getEnd()->format(self::HOME_AWAY_DATE_FORMAT));

            $responseDetailsNode->addChild(
                'reservationStatus',
                $bookingResult->getConfirmed() ? 'CONFIRMED' : 'UNCONFIRMED'
            );
        } else {
            $errorListNode = $bookingResponse->addChild('errorList');
            foreach ($messages as $message) {
                //TODO: Заполнить ошибками
                $errorNode = $errorListNode->addChild('error');
                $errorNode->addChild('');
            }
        }

        return $bookingResponse->asXML();
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getPaymentStatus(Order $order)
    {
        if (!$order->getPaid()) {
            return 'UNPAID';
        }
        $notPaidAmount = $order->getPrice() - $order->getPaid();
        if ($notPaidAmount > 0) {
            return 'PARTIAL_PAID';
        } elseif ($notPaidAmount == 0) {
            return 'PAID';
        }

        return 'OVERPAID';
    }

    /**
     * @param $begin
     * @param $end
     * @param $roomCaches
     * @param $restrictions
     * @param $priceCaches
     * @return array
     */
    private function getAvailabilityData(
        $begin,
        $end,
        $roomCaches,
        $restrictions,
        $priceCaches
    ) {
        $availabilityString = '';
        $maxStayData = [];
        $minStayData = [];
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            /** @var \DateTime $day */
            $dayString = $day->format('d.m.Y');
            /** @var Restriction $restrictionData */
            $restrictionData = isset($restrictions[$dayString])
                ? $restrictions[$dayString] : null;
            /** @var RoomCache $roomData */
            $roomData = isset($roomCaches[$dayString])
                ? $roomCaches[$dayString] : null;
            $isAvailable = $roomData && !$roomData->getIsClosed() && $roomData->getLeftRooms() > 0
                && (!$restrictionData || !$restrictionData->getClosed())
                && isset($priceCaches[$dayString]);
            $availabilityString .= $isAvailable ? 'Y' : 'N';
            $maxStayData[] = is_null($restrictionData) || !$restrictionData->getMaxStay(
            ) ? 0 : $restrictionData->getMaxStay();
            $minStayData[] = is_null($restrictionData) || !$restrictionData->getMinStay(
            ) ? 0 : $restrictionData->getMinStay();
        }
        $maxStayString = join(',', $maxStayData);
        $minStayString = join(',', $minStayData);

        return [
            'availability' => $availabilityString,
            'minStay' => $minStayString,
            'maxStay' => $maxStayString,
        ];
    }

    /**
     * @param \SimpleXMLElement $mainNode
     * @param \DateTime $beginDate
     * @param HomeAwayConfig $config
     * @param $price
     * @param $currency
     */
    private function addPaymentScheduleNode(
        \SimpleXMLElement $mainNode,
        \DateTime $beginDate,
        HomeAwayConfig $config,
        $price,
        $currency
    ) {
        $paymentScheduleNode = $mainNode->addChild('paymentSchedule');
        $paymentFormsElement = $paymentScheduleNode->addChild('acceptedPaymentForms');
        $cardList = $config->getHotel()->getAcceptedCardTypes();
        foreach ($cardList as $cardType) {
            /** @var CardType $cardType */
            $cardDescriptorElement = $paymentFormsElement->addChild('paymentCardDescriptor');
            $cardDescriptorElement->addChild('paymentFormType', 'CARD');
            $cardDescriptorElement->addChild('cardCode', $cardType->getCardCode());
            $cardDescriptorElement->addChild('cardType', $cardType->getCardCategory());
        }
        $invoiceDescriptorElement = $paymentFormsElement->addChild('paymentInvoiceDescriptor');
        $invoiceDescriptorElement->addChild('paymentFormType', 'INVOICE');

        $paymentItemListElement = $paymentScheduleNode->addChild('paymentScheduleItemList');
        $paymentScheduleData = $this->getPaymentScheduleData($config->getPaymentType(), $price, $beginDate);
        foreach ($paymentScheduleData as $paymentScheduleItemData) {
            $paymentItemElement = $paymentItemListElement->addChild('paymentScheduleItem');
            $amountElement = $paymentItemElement->addChild(
                'amount',
                $this->currencyHandler->convertFromRub($paymentScheduleItemData['amount'], $currency)
            );
            $amountElement->addAttribute('currency', $currency);
        }
    }

    /**
     * @param $paymentType
     * @param $price
     * @param \DateTime $beginDate
     * @return array
     */
    private function getPaymentScheduleData($paymentType, $price, \DateTime $beginDate)
    {
        $data = [];
        switch ($paymentType) {
            case 'in_hotel':
                $data[] = ['amount' => $price, 'date' => $beginDate->format(self::HOME_AWAY_DATE_FORMAT)];
                break;
            case 'online_full':
                $data[] = ['amount' => $price, 'date' => (new \DateTime())->format(self::HOME_AWAY_DATE_FORMAT)];
                break;
            case 'online_half':
                $data[] = ['amount' => $price / 2, 'date' => (new \DateTime())->format(self::HOME_AWAY_DATE_FORMAT)];
                $data[] = ['amount' => $price / 2, 'date' => $beginDate->format(self::HOME_AWAY_DATE_FORMAT)];
                break;
        }

        return $data;
    }

    /**
     * @param $upperLocalCurrency
     * @return bool
     */
    private function isLocalCurrencyAvailable($upperLocalCurrency)
    {
        return in_array($upperLocalCurrency, ['USD', 'EUR', 'GBP']);
    }

    /**
     * @param $upperLocalCurrency
     * @return string
     */
    private function getAvailableCurrency($upperLocalCurrency)
    {
        return $this->isLocalCurrencyAvailable($upperLocalCurrency) ? $upperLocalCurrency : 'USD';
    }

    /**
     * @return \DateTime
     */
    private function getBeginDate()
    {
        return new \DateTime();
    }

    /**
     * @param HomeAwayConfig $config
     * @param RoomType $roomType
     * @return HomeAwayRoom|mixed|null
     */
    private function getHARoomByRoomType(HomeAwayConfig $config, RoomType $roomType)
    {
        foreach ($config->getRooms() as $room) {
            /** @var HomeAwayRoom $room */
            if ($room->getRoomType()->getId() === $roomType->getId()) {
                return $room;
            }
        }

        return null;
    }

    private function getEndDate()
    {
        return new \DateTime('+2 year');
    }

    /**
     * @param $urlName
     * @param RoomType $roomType
     * @param HomeAwayConfig $config
     * @return string
     */
    private function getListingDataUrl($urlName, RoomType $roomType, HomeAwayConfig $config)
    {
        return $this->router->generate($urlName, ['roomTypeId' => $roomType->getId(), 'hotelId' => $config->getHotel()->getId()]);
    }
}