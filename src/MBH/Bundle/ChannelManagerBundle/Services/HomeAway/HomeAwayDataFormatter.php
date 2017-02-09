<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Currency;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels\OrderInfo;
use MBH\Bundle\ChannelManagerBundle\Services\ChannelManagerHelper;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class HomeAwayDataFormatter
{
    /** @var  ChannelManagerHelper $channelManagerHelper */
    private $channelManagerHelper;
    private $localeCurrency;
    /** @var  Router $router */
    private $router;
    /** @var DocumentManager $dm */
    private $dm;
    /** @var RoomTypeManager $roomManager */
    private $roomManager;
    /** @var  SearchFactory $search */
    private $searchFactory;
    /** @var  Currency $currencyHandler */
    private $currencyHandler;
    private $locale;

    public function __construct(
        ChannelManagerHelper $channelManagerHelper,
        $localeCurrency,
        Router $router,
        DocumentManager $dm,
        RoomTypeManager $manager,
        SearchFactory $searchFactory,
        Currency $currencyHandler
    ) {
        $this->channelManagerHelper = $channelManagerHelper;
        $this->localeCurrency = $localeCurrency;
        $this->router = $router;
        $this->dm = $dm;
        $this->roomManager = $manager;
        $this->searchFactory = $searchFactory;
        $this->currencyHandler = $currencyHandler;
    }

    public function formatListingContentIndex(ChannelManagerConfigInterface $config, $dataType)
    {
        $rootElement = new \SimpleXMLElement('<listingContentIndex/>');
        $advertisersElement = $rootElement->addChild('advertisers');
        $advertiserElement = $advertisersElement->addChild('advertiser');
        if ($dataType == 'availability') {
            $urlName = 'homeaway_availability';
            $nodeName = 'unitAvailabilityUrl';
        } else {
            $urlName = 'homeaway_rates';
            $nodeName = 'unitRatesUrl';
        }
        //TODO: Получить значение
        $assignedId = '';
        $advertiserElement->addChild('assignedId', $assignedId);
        foreach ($config->getRooms() as $channelManagerRoomType) {
            /** @var Room $channelManagerRoomType */
            $roomType = $channelManagerRoomType->getRoomType();
            $listingEntry = $advertiserElement->addChild('listingContentIndexEntry');
            $listingEntry->addChild('listingExternalId', $roomType->getId());
            $listingEntry->addChild('listingHomeAwayId', $channelManagerRoomType->getRoomId());
            $listingEntry->addChild('unitExternalId', $roomType->getId());
            $listingEntry->addChild('active', $roomType->getIsEnabled());
            $listingEntry->addChild('lastUpdatedDate', $roomType->getUpdatedAt()->format('Y-m-d\TH:i:s') . 'Z');
            $listingEntry->addChild($nodeName,
                $this->router->generate($urlName, ['listingId' => $roomType->getId()]));
//            $listingEntry->addChild('')
        }

        return $rootElement;
    }

    public function formatRatePeriodsData($begin, $end, $serviceRoomTypeId, HomeAwayConfig $config)
    {
        $mbhRoomTypeId = $this->channelManagerHelper
            ->getMbhRoomTypeByServiceRoomTypeId($serviceRoomTypeId, $config)->getId();
        $tariffId = '';
        $priceCaches = $this->getPriceCaches($begin, $end, $config, $mbhRoomTypeId, $tariffId);
        $ratePeriods = $this->channelManagerHelper->getPeriodsFromDayEntities($begin, $end, $priceCaches, ['getPrice']);

        $ratesElement = new \SimpleXMLElement('<ratePeriods/>');
        $ratesElement->addChild('listingExternalId', $mbhRoomTypeId);
        $ratesElement->addChild('listingHomeAwayId', $serviceRoomTypeId);
        $ratesElement->addChild('unitExternalId', $mbhRoomTypeId);
        $ratesElement->addChild('unitHomeAwayId', $serviceRoomTypeId);

        $ratePeriodsElement = $ratesElement->addChild('ratePeriods');
        foreach ($ratePeriods as $ratePeriod) {
            /** @var \SimpleXMLElement $ratePeriodElement */
            $ratePeriodElement = $ratePeriodsElement->addChild('ratePeriod');
            $dateRangeElement = $ratePeriodElement->addChild('dateRange');
            $dateRangeElement->addChild('beginDate', $ratePeriod['begin']->format('Y-m-d'));
            $dateRangeElement->addChild('endDate', $ratePeriod['end']->format('Y-m-d'));

            $ratesElement = $ratePeriodElement->addChild('rates');
            $rateElement = $ratesElement->addChild('rate');
            $rateElement->addAttribute('rateType', 'EXTRA_NIGHT');
            $amountElement = $rateElement->addChild('amount', $ratePeriod['entity']->getPrice());
            $amountElement->addAttribute('currency', $this->localeCurrency);
            $ratePeriodElements[] = $ratePeriodElement;
        }

        return $ratesElement;
    }

    public function formatAvailabilityData($homeAwayRoomTypeId, HomeAwayConfig $config)
    {
        $config = $this->dm->getRepository('MBHChannelManagerBundle:HomeAwayConfig')
            ->findOneBy(['rooms.roomId' => $homeAwayRoomTypeId]);
        $mbhRoomTypeId = $this->channelManagerHelper
            ->getMbhRoomTypeByServiceRoomTypeId($homeAwayRoomTypeId, $config)->getId();
        $beginDate = $this->getBeginDate();
        $endDate = $this->getEndDate();

        $priceCaches = $this->getPriceCaches($beginDate, $endDate, $config, $mbhRoomTypeId, $config->getMainTariff());
        $restrictions = $this->getRestrictions($beginDate, $endDate, $config, $mbhRoomTypeId, $config->getMainTariff());
        $roomCaches = $this->getRoomCaches($beginDate, $endDate, $config, $mbhRoomTypeId, $config->getMainTariff());

        $availabilityElement = new \SimpleXMLElement('<unitAvailabilityEntities/>');
        $availabilityElement->addChild('listingExternalId', $mbhRoomTypeId);
        $availabilityElement->addChild('unitExternalId', $mbhRoomTypeId);
        $unitAvailabilityElement = $availabilityElement->addChild('unitAvailability');

        $dateRangeElement = $availabilityElement->addChild('dateRange');
        $dateRangeElement->addChild('beginDate', $beginDate->format('Y-m-d'));
        $dateRangeElement->addChild('endDate', $endDate->format('Y-m-d'));

        $unitAvailabilityElement->addChild('availabilityDefault', 'N');
        //TODO: Уточнить
        $unitAvailabilityElement->addChild('maxStayDefault', 28);
        $availabilityConfigElement = $unitAvailabilityElement->addChild('unitAvailabilityConfiguration');

        $availabilityData = $this->getAvailabilityData($beginDate, $endDate, $roomCaches, $restrictions, $priceCaches,
            $mbhRoomTypeId, $config->getMainTariff());
        $availabilityConfigElement->addChild('availability', $availabilityData['availability']);
        $availabilityConfigElement->addChild('maxStay', $availabilityData['maxStay']);
        $availabilityConfigElement->addChild('minStay', $availabilityData['minStay']);
    }

    public function getQuoteResponse(
        $roomTypeId,
        $adultCount,
        $childrenCount,
        $beginString,
        $endString,
        $documentVersion,
        HomeAwayConfig $config
    ) {
        $quoteResponse = new \SimpleXMLElement('<quoteResponse/>');
        $quoteResponse->addChild('documentVersion', $documentVersion);
        $responseDetailsElement = $quoteResponse->addChild('quoteResponseDetails');
        //TODO: Получить
        $locale = '';
        $responseDetailsElement->addChild('quoteResponseDetails', $locale);
        $orderListElement = $responseDetailsElement->addChild('orderList');
        $orderElement = $orderListElement->addChild('order');

        // В HomeAway валюта может быть одной из: USD, EUR, GBP;
        $upperLocaleCurrency = strtoupper($this->localeCurrency);
        $currency = $this->getAvailableCurrency($upperLocaleCurrency);
        $orderElement->addChild('currency', $currency);
        $orderItemListElement = $orderElement->addChild('orderItemList');

        $searchResults = $this->getSearchResults($roomTypeId, $adultCount, $childrenCount, $beginString, $endString,
            $config->getMainTariff());

        if (count($searchResults) > 0) {
            $roomTypeQuoteData = current($searchResults);
            //Поиск ведется только по 1 синхронизируемому тарифу для 1 типа номера
            $searchResult = current($roomTypeQuoteData['results']);
            /** @var SearchResult $searchResult */
            $orderItemElement = $orderItemListElement->addChild('orderItem');

            //TODO: Добавить везде данные
            $orderItemElement->addChild('description', 'Rent');
            $orderItemElement->addChild('feeType', 'RENTAL');
            $orderItemElement->addChild('name', 'Rent');

            $price = $searchResult->getPrice($adultCount, $childrenCount);
            $resultPrice = $this->isLocalCurrencyAvailable($upperLocaleCurrency)
                ? $price : $this->currencyHandler->convertFromRub($price, $currency);
            $preTaxAmountElement = $orderItemElement->addChild('preTaxAmount', $resultPrice);
            $preTaxAmountElement->addAttribute('currency', $currency);
            $totalAmountElement = $orderItemElement->addChild('totalAmount', $resultPrice);
            $totalAmountElement->addAttribute('currency', $currency);

            $paymentScheduleElement = $orderItemListElement->addChild('paymentSchedule');
            $paymentFormsElement = $paymentScheduleElement->addChild('acceptedPaymentForms');
            //TODO: Получить список используемых карт
            $cardList = [];
            foreach ($cardList as $cardName) {
                $cardDescriptorElement = $paymentFormsElement->addChild('paymentCardDescriptor');
                $cardDescriptorElement->addChild('paymentFormType', 'CARD');
                $cardDescriptorElement->addChild('cardCode', $cardName);
                //TODO: Также может быть DEBIT. Мб потребуется продублировать.
                $cardDescriptorElement->addChild('cardType', 'CREDIT');
            }
            $invoiceDescriptorElement = $paymentFormsElement->addChild('paymentInvoiceDescriptor');
            $invoiceDescriptorElement->addChild('paymentFormType', 'INVOICE');
            //TODO: Можно добавить данные о платеже через накладную
            $invoiceDescriptorElement->addChild('paymentNote', '');

            //TODO: Расписание платежей. Какую указывать дату?
            $paymentItemListElement = $paymentScheduleElement->addChild('paymentScheduleItemList');
            $paymentItemElement = $paymentItemListElement->addChild('paymentScheduleItem');
            $amountElement = $paymentItemElement->addChild('amount', $resultPrice);
            $amountElement->addAttribute('currency', $currency);
            //TODO: Заполнить, когда появятся соответствующие поля (НЕОБЯЗАТЕЛЬНЫЕ)
            $paymentItemElement->addChild('refundable');
            $paymentItemElement->addChild('refundDescription');
            $paymentItemElement->addChild('refundPercent');

            $cancellationPolicyElement = $orderItemListElement->addChild('reservationCancellationPolicy');
            //TODO: Заполнить обязательно, либо использовать другие поля. Рекомендуется использовать тестовое описание
            //Возможно заполнение URL, PDF или текстом описания
            $cancellationPolicyElement->addChild('description');
            //TODO: Также можно добавить amount,deadline, penaltyType, percentPenalty, но необязательно.
            //TODO: Можно добавить дополнительные комиссии. Поле stayFees. К примеру городские комиссии.
        }

        $rentalAgreementElement = $responseDetailsElement->addChild('rentalAgreement');
        //TODO: Заполнить данными о договоре аренды. Мб текстом или URL.
        $rentalAgreementElement->addChild('agreementText');
    }

    public function getBookingResponse($documentVersion, $bookingResult, $messages)
    {
        $bookingResponse = new \SimpleXMLElement('<bookingResponse/>');

        if ($bookingResult instanceof Order) {
            $responseDetailsNode = $bookingResponse->addChild('bookingResponseDetails');
            $responseDetailsNode->addChild('documentVersion', $documentVersion);
            $responseDetailsNode->addChild('externalId', $bookingResult->getPackages()[0]->getNumberWithPrefix());

            /** @var Tourist $payer */
            $payer = $bookingResult->getPayer();
            $responseDetailsNode->addChild('guestProfileExternalId', $payer->getId());
            //TODO: Установить локаль. Состоит из языка + _ + кода страны
            $responseDetailsNode->addChild('locale', $this->locale);
            $orderListNode = $responseDetailsNode->addChild('orderList');
            $orderNode = $orderListNode->addChild('order');

            $upperLocalCurrency = strtoupper($this->locale);
            $currency = $this->getAvailableCurrency($upperLocalCurrency);
            $orderNode->addChild('currency', $currency);
            $orderNode->addChild('externalId', $bookingResult->getId());
            //TODO: Заполнить
            $orderItemListNode = $orderNode->addChild('orderItemList');
            foreach ($bookingResult->getCashDocuments() as $cashDocument) {
                /** @var CashDocument $cashDocument */
                $orderItemNode = $orderItemListNode->addChild('orderItem');
                $orderItemNode->addChild('externalId', $cashDocument->getId());
                //TODO: Уточнить
                $feeType = $cashDocument->getOperation() == 'in' ? 'RENTAL' : 'DISCOUNT';
                $orderItemNode->addChild('feeType', $feeType);
                $orderItemNode->addChild('Name', $feeType);
                $price = $this->isLocalCurrencyAvailable($upperLocalCurrency)
                    ? $cashDocument->getTotal()
                    : $this->currencyHandler->convertFromRub($cashDocument->getTotal(), $currency);
                $orderItemNode->addChild('preTaxAmount', $cashDocument->getTotal());
            }
            //TODO: Заполнить, когда будут данные
            $orderNode->addChild('paymentSchedule');
            //TODO: Заполнить, когда будут данные
            $orderNode->addChild('reservationCancellationPolicy');
            //TODO: Можно добавить
            $orderNode->addChild('stayFees');

        }

        return $bookingResponse;
    }
    
    private function getAvailabilityData(
        $begin,
        $end,
        $roomCaches,
        $restrictions,
        $priceCaches,
        $mbhRoomTypeId,
        $tariffId
    ) {
        $availabilityString = '';
        $maxStayString = '';
        $minStayString = '';
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            /** @var \DateTime $day */
            $dayString = $day->format('d.m.Y');
            /** @var Restriction $restrictionData */
            $restrictionData = isset($restrictions[$mbhRoomTypeId][$tariffId][$dayString])
                ? $restrictions[$mbhRoomTypeId][$tariffId][$dayString] : null;
            /** @var RoomCache $roomData */
            $roomData = isset($roomCaches[$mbhRoomTypeId][$tariffId][$dayString])
                ? $roomCaches[$mbhRoomTypeId][$tariffId][$dayString] : null;
            $isAvailable = $roomData && !$roomData->getIsClosed() && $roomData->getLeftRooms() > 0
                && (!$restrictionData || !$restrictionData->getClosed())
                && isset($priceCaches[$dayString]);
            $availabilityString .= $isAvailable ? 'Y' : 'N';
            $maxStayString .= $restrictionData->getMaxStay() ? $restrictionData->getMaxStay() : 0;
            $minStayString .= $restrictionData->getMinStay() ? $restrictionData->getMinStay() : 0;
        }

        return [
            'availability' => $availabilityString,
            'minStay' => $minStayString,
            'maxStay' => $maxStayString
        ];
    }

    public function getSearchResults(
        $roomTypeId,
        $adultCount,
        $childrenCount,
        $beginString,
        $endString,
        Tariff $tariff
    ) {
        $query = new SearchQuery();

        $query->accommodations = true;
        $query->begin = Helper::getDateFromString($beginString, 'Y-m-d');
        $query->end = Helper::getDateFromString($endString, 'Y-m-d');
        $query->addRoomType($roomTypeId);
        $query->adults = $adultCount;
        $query->children = $childrenCount;
        $query->tariff = $tariff;

        return $this->searchFactory->setWithTariffs()->search($query);
    }

    public function getPriceCaches($beginDate, $endDate, HomeAwayConfig $config, $roomTypeId, $tariffId)
    {
        $requestedPriceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
            $beginDate,
            $endDate,
            $config->getHotel(),
            [$roomTypeId],
            [$tariffId],
            true,
            $this->roomManager->useCategories
        );

        return $requestedPriceCaches[$roomTypeId][$tariffId];
    }

    public function getRestrictions(
        $beginDate,
        $endDate,
        ChannelManagerConfigInterface $config,
        $roomTypeId,
        $tariffId
    ) {
        return $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $beginDate,
            $endDate,
            $config->getHotel(),
            [$roomTypeId],
            [$tariffId],
            true
        );
    }

    public function getRoomCaches($beginDate, $endDate, ChannelManagerConfigInterface $config, $roomTypeId, $tariffId)
    {
        return $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $beginDate,
            $endDate,
            $config->getHotel(),
            [$roomTypeId],
            [$tariffId],
            true
        );
    }

    private function isLocalCurrencyAvailable($upperLocalCurrency)
    {
        return in_array($upperLocalCurrency, ['USD', 'EUR', 'GBP']);
    }

    private function getAvailableCurrency($upperLocalCurrency)
    {
        return $this->isLocalCurrencyAvailable($upperLocalCurrency) ? $upperLocalCurrency: 'USD';
    }

    private function getBeginDate()
    {
        return new \DateTime();
    }

    private function getEndDate()
    {
        return new \DateTime('+2 year');
    }
}