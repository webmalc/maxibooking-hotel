<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\ChannelManagerHelper;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
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

    public function __construct(
        ChannelManagerHelper $channelManagerHelper,
        $localeCurrency,
        Router $router,
        DocumentManager $dm,
        RoomTypeManager $manager,
        SearchFactory $searchFactory
    ) {
        $this->channelManagerHelper = $channelManagerHelper;
        $this->localeCurrency = $localeCurrency;
        $this->router = $router;
        $this->dm = $dm;
        $this->roomManager = $manager;
        $this->searchFactory = $searchFactory;
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

    public function formatAvailabilityData($homeAwayRoomTypeId)
    {
        $config = $this->dm->getRepository('MBHChannelManagerBundle:HomeAwayConfig')
            ->findOneBy(['rooms.roomId' => $homeAwayRoomTypeId]);
        $mbhRoomTypeId = $this->channelManagerHelper
            ->getMbhRoomTypeByServiceRoomTypeId($homeAwayRoomTypeId, $config)->getId();
        $beginDate = $this->getBeginDate();
        $endDate = $this->getEndDate();

        //TODO: Изменить значение тарифа
        $tariff = '';
        $priceCaches = $this->getPriceCaches($beginDate, $endDate, $config, $mbhRoomTypeId, $tariff);
        $restrictions = $this->getRestrictions($beginDate, $endDate, $config, $mbhRoomTypeId, $tariff);
        $roomCaches = $this->getRoomCaches($beginDate, $endDate, $config, $mbhRoomTypeId, $tariff);

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
            $mbhRoomTypeId, $tariff);
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
        //TODO: Валюта может быть одной из: USD, EUR, GBP
        $currency = '';
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
            $preTaxAmountElement = $orderItemElement->addChild('preTaxAmount', $price);
            $preTaxAmountElement->addAttribute('currency', $currency);
            $totalAmountElement = $orderItemElement->addChild('totalAmount', $price);
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
            $amountElement = $paymentItemElement->addChild('amount', $price);
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

    public function getBookingResponse($documentVersion)
    {

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

    private function getSearchResults(
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

    private function getPriceCaches($beginDate, $endDate, HomeAwayConfig $config, $roomTypeId, $tariffId)
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

    private function getRestrictions(
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

    private function getRoomCaches($beginDate, $endDate, ChannelManagerConfigInterface $config, $roomTypeId, $tariffId)
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

    private function getBeginDate()
    {
        return new \DateTime();
    }

    private function getEndDate()
    {
        return new \DateTime('+2 year');
    }
}