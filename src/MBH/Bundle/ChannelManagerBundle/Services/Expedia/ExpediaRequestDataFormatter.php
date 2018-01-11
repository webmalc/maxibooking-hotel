<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Tariff;

class ExpediaRequestDataFormatter extends AbstractRequestDataFormatter
{
    const EXPEDIA_MIN_STAY = 1;
    const EXPEDIA_MAX_STAY = 28;
    const AVAILABILITY_AND_RATES_REQUEST_NAMESPACE = 'http://www.expediaconnect.com/EQC/AR/2011/06';
    const BOOKING_RETRIEVAL_REQUEST_NAMESPACE = 'http://www.expediaconnect.com/EQC/BR/2014/01';
    const CONFIRM_REQUEST_NAMESPACE = 'http://www.expediaconnect.com/EQC/BC/2007/09';
    const CONFIRMATION_DATE_FORMAT_STRING = 'Y-m-d\TH:i:s\Z';
    const EXPEDIA_DEFAULT_DATE_FORMAT_STRING = 'Y-m-d';

    /**
     * Переопределенный метод форматирования результирующего массива данных о ценах, полученных из Maxibooking
     * @param PriceCache|null $priceCache
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param $serviceRoomTypeId
     * @param $serviceTariffId
     * @param $resultArray
     * @param \DateTime $day
     */
    protected function formatPriceData(
        $priceCache,
        RoomType $roomType,
        Tariff $tariff,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        \DateTime $day
    )
    {
        $resultArray[$serviceRoomTypeId][$serviceTariffId][$day->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING)] = $priceCache;
    }

    /**
     * Форматирование данных в формате xml, отправляемых в запросе обновления цен сервиса
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatPriceRequestData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    )
    {
        $requestDataArray = $this->getPriceData($begin, $end, $roomTypes, $serviceTariffs, $config);
        $xmlElements = [];
        $priceCalculator = $this->container->get('mbh.calculation');
        foreach ($requestDataArray as $roomTypeId => $pricesByTariffs) {
            foreach ($pricesByTariffs as $tariffId => $pricesByDates) {
                $cmHelper = $this->container->get('mbh.channelmanager.helper');
                $comparePropertyMethods = ['getPrice', 'getIsPersonPrice', 'getAdditionalPrice', 'getAdditionalChildrenPrice', 'getSinglePrice', 'getChildPrice'];
                $periodsData = $cmHelper->getPeriodsFromDayEntities($begin, $end, $pricesByDates, $comparePropertyMethods, 'Y-m-d');
                foreach ($periodsData as $periodData) {
                    $xmlRoomTypeData = new \SimpleXMLElement('<AvailRateUpdate/>');
                    /** @var PriceCache $priceCache */
                    $priceCache = $periodData['entity'];
                    $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
                    /** @var \DateTime $periodBegin */
                    $periodBegin = $periodData['begin'];
                    $dateRangeElement->addAttribute('from', $periodBegin->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING));

                    /** @var \DateTime $periodEnd */
                    $periodEnd = $periodData['end'];
                    $dateRangeElement->addAttribute('to', $periodEnd->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING));

                    $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                    $roomTypeElement->addAttribute('id', $roomTypeId);

                    $hasPriceList = false;
                    $priceList = [];
                    if (!is_null($priceCache)) {
                        $priceList = $priceCalculator->calcPrices($priceCache->getRoomType(), $priceCache->getTariff(), $periodBegin, $periodBegin);
                        $hasPriceList = count($priceList) > 0;
                    }

                    $ratePlanElement = $roomTypeElement->addChild('RatePlan');
                    $ratePlanElement->addAttribute('id', $tariffId);
                    $ratePlanElement->addAttribute('closed', $hasPriceList ? 'false' : 'true');

                    $rateElement = $ratePlanElement->addChild('Rate');
                    $rateElement->addAttribute('currency',
                        strtoupper($this->container->getParameter('locale.currency')));
                    if ($hasPriceList) {
                        foreach ($priceList as $price) {
                            if (!isset($price['children']) || $price['children'] == 0) {
                                $perOccupancyElement = $rateElement->addChild('PerOccupancy');
                                $perOccupancyElement->addAttribute('rate', $price['total']);
                                $perOccupancyElement->addAttribute('occupancy', $price['adults']);
                            }
                        }
                    }
                    $xmlElements[] = $xmlRoomTypeData;
                }
            }
        }

        return $this->formatTemplateRequest($xmlElements, $config,
            'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
    }

    /**
     * Форматирование данных в формате xml, отправляемых в запросе обновления квот на комнаты
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRoomRequestData($begin, $end, $roomTypes, ChannelManagerConfigInterface $config)
    {
        $xmlElements = [];
        $requestDataArray = $this->getRoomData($begin, $end, $roomTypes, $config);
        $cmHelper = $this->container->get('mbh.channelmanager.helper');
        foreach ($requestDataArray as $roomTypeId => $roomQuotasByDates) {
            $periodsData = $cmHelper->getPeriodsFromDayEntities($begin, $end, $roomQuotasByDates, ['getLeftRooms'], self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING);
            foreach ($periodsData as $periodData) {
                $xmlRoomTypeData = new \SimpleXMLElement('<AvailRateUpdate/>');
                $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');

                /** @var \DateTime $periodBegin */
                $periodBegin = $periodData['begin'];
                $dateRangeElement->addAttribute('from', $periodBegin->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING));

                /** @var \DateTime $periodEnd */
                $periodEnd = $periodData['end'];
                $dateRangeElement->addAttribute('to', $periodEnd->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING));

                /** @var RoomCache $roomCache */
                $roomCache = $periodData['entity'];
                $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                $roomTypeElement->addAttribute('id', $roomTypeId);
                $roomTypeElement->addAttribute('closed', $roomCache ? "false" : "true");

                $inventoryElement = $roomTypeElement->addChild('Inventory');
                $inventoryElement->addAttribute('totalInventoryAvailable',
                    $roomCache && $roomCache->getLeftRooms() > 0 ? $roomCache->getLeftRooms() : 0);
                $xmlElements[] = $xmlRoomTypeData;
            }
        }

        return $this->formatTemplateRequest($xmlElements, $config,
            'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
    }

    /**
     * Переопределенный метод форматирования результирующего массива данных об ограничениях, полученных из Maxibooking
     * @param Restriction $restriction
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param $serviceRoomTypeId
     * @param $serviceTariffId
     * @param $resultArray
     * @param $isPriceSet
     * @param \DateTime $day
     * @param $serviceTariffs
     */
    protected function formatRestrictionData(
        $restriction,
        RoomType $roomType,
        Tariff $tariff,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        $isPriceSet,
        \DateTime $day,
        $serviceTariffs
    )
    {
        if (!is_null($restriction) && !$isPriceSet) {
            $restriction->setClosed(true);
        }
        $resultArray[$serviceRoomTypeId][$serviceTariffId][$day->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING)] = $restriction;
    }

    /**
     * Форматирование данных в формате xml, отправляемых в запросе обновления ограничений
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRestrictionRequestData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    )
    {
        $xmlElements = [];
        $comparePropertyMethods = ['getMinStay', 'getMaxStay', 'getClosedOnArrival', 'getClosedOnDeparture', 'getClosed'];
        $requestDataArray = $this->getRestrictionData($begin, $end, $roomTypes, $serviceTariffs, $config);
        $cmHelper = $this->container->get('mbh.channelmanager.helper');
        foreach ($requestDataArray as $roomTypeId => $restrictionsByTariffs) {

            foreach ($restrictionsByTariffs as $tariffId => $restrictionsByDates) {
                $periodsData = $cmHelper->getPeriodsFromDayEntities($begin, $end, $restrictionsByDates, $comparePropertyMethods, 'Y-m-d');
                foreach ($periodsData as $periodData) {
                    $xmlRoomTypeData = new \SimpleXMLElement('<AvailRateUpdate/>');

                    $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
                    /** @var \DateTime $periodBegin */
                    $periodBegin = $periodData['begin'];
                    $dateRangeElement->addAttribute('from', $periodBegin->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING));
                    /** @var \DateTime $periodEnd */
                    $periodEnd = $periodData['end'];
                    $dateRangeElement->addAttribute('to', $periodEnd->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING));

                    $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                    $roomTypeElement->addAttribute('id', $roomTypeId);
                    $ratePlanElement = $roomTypeElement->addChild('RatePlan');
                    $ratePlanElement->addAttribute('id', $tariffId);

                    /** @var Restriction $restriction */
                    $restriction = $periodData['entity'];
                    $restrictionData = $this->extractRestrictionData($restriction, $serviceTariffs, $tariffId);
                    $ratePlanElement->addAttribute('closed', $restrictionData['isClosed']);

                    $restrictionsElement = $ratePlanElement->addChild('Restrictions');
                    $restrictionsElement->addAttribute('closedToArrival', $restrictionData['isClosedToArrival']);
                    $restrictionsElement->addAttribute('closedToDeparture', $restrictionData['isClosedToDeparture']);
                    $restrictionsElement->addAttribute('minLOS', $restrictionData['minStay']);
                    $restrictionsElement->addAttribute('maxLOS', $restrictionData['maxStay']);
                    $xmlElements[] = $xmlRoomTypeData;
                }
            }
        }

        return $this->formatTemplateRequest($xmlElements, $config,
            'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
    }

    /**
     * @param Restriction|null $restriction
     * @param $serviceTariffs
     * @param $serviceTariffId
     * @return array
     */
    private function extractRestrictionData(?Restriction $restriction, $serviceTariffs, $serviceTariffId)
    {
        $isClosed = !is_null($restriction) && $restriction->getClosed();
        $isClosedToArrival = $isClosed || (!is_null($restriction) && $restriction->getClosedOnArrival()) ? 'true' : 'false';
        $isClosedToDeparture = $isClosed || (!is_null($restriction) && $restriction->getClosedOnDeparture()) ? 'true' : 'false';

        $minLOSDefault = $serviceTariffs[$serviceTariffId]['minLOSDefault'];
        $maxLOSDefault = $serviceTariffs[$serviceTariffId]['maxLOSDefault'];

        if (!is_null($restriction) && $restriction->getMaxStay()) {
            if ($restriction->getMaxStay() > $maxLOSDefault) {
                $flashBag = $this->container->get('session')->getFlashBag();
                $flashBag->clear();
                $flashBag->add('danger', $this->container->get('translator')
                    ->trans('expedia_request_data_formatter.max_stay.error', ['%count%' => $maxLOSDefault]));
                $maxStay = $maxLOSDefault;
            } else {
                $maxStay = $restriction->getMaxStay();
            }
        } else {
            $maxStay = $maxLOSDefault;
        }

        if (!is_null($restriction) && $restriction->getMinStay()) {
            if ($restriction->getMinStay() < $minLOSDefault) {
                $flashBag = $this->container->get('session')->getFlashBag();
                $flashBag->clear();
                $flashBag->add('danger', $this->container->get('translator')
                    ->trans('expedia_request_data_formatter.min_stay.error', ['%count%' => $minLOSDefault]));
                $minStay = $minLOSDefault;
            } else {
                $minStay = $restriction->getMinStay();
            }
        } else {
            $minStay = $minLOSDefault;
        }

        return [
            'isClosed' => $isClosed ? 'true' : 'false',
            'isClosedToArrival' => $isClosedToArrival,
            'isClosedToDeparture' => $isClosedToDeparture,
            'maxStay' => $maxStay,
            'minStay' => $minStay
        ];
    }

    public function formatGetBookingsData(ChannelManagerConfigInterface $config)
    {
        return $this->formatTemplateRequest([], $config, 'BookingRetrievalRQ',
            self::BOOKING_RETRIEVAL_REQUEST_NAMESPACE);
    }

    public function formatCloseForConfigData(ChannelManagerConfigInterface $config)
    {
        $roomTypesData = $this->container->get('mbh.channelmanager.helper')->getRoomTypesSyncData($config);

        $requestData = [];
        foreach ($roomTypesData as $roomTypeData) {
            $xmlRoomTypeData = new \SimpleXMLElement('<AvailRateUpdate/>');

            $startDate = new \DateTime();
            $endDate = new \DateTime('+2 years');
            $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
            $dateRangeElement->addAttribute('from', $startDate->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING));
            $dateRangeElement->addAttribute('to', $endDate->format(self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING));

            $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
            $roomTypeElement->addAttribute('id', $roomTypeData['syncId']);
            $roomTypeElement->addAttribute('closed', 'true');

            $requestData[] = $xmlRoomTypeData;
        }

        return $this->formatTemplateRequest($requestData, $config, 'AvailRateUpdateRQ',
            self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
    }

    public function formatNotifyServiceData(AbstractOrderInfo $orderInfo, $config)
    {
        /** @var ExpediaOrderInfo $orderInfo */
        $confirmNumbersElement = new \SimpleXMLElement('<BookingConfirmNumbers/>');
        $confirmNumberElement = $confirmNumbersElement->addChild('BookingConfirmNumber');
        $confirmNumberElement->addAttribute('bookingID', $orderInfo->getChannelManagerOrderId());
        $confirmNumberElement->addAttribute('bookingType', $orderInfo->getOrderStatusType());

        $confirmTime = new \DateTime('now', new \DateTimeZone("UTC"));
        $confirmNumberElement->addAttribute('confirmTime', $confirmTime->format(self::CONFIRMATION_DATE_FORMAT_STRING));

        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }
        $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(['channelManagerId' => $orderInfo->getChannelManagerOrderId()]);
        $confirmNumberElement->addAttribute('confirmNumber', $order->getId());
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        return $this->formatTemplateRequest([$confirmNumbersElement], $config,
            'BookingConfirmRQ', self::CONFIRM_REQUEST_NAMESPACE);
    }

    /**
     * Форматирование шаблона в формате xml
     * @param $elementsArray
     * @param ChannelManagerConfigInterface $config
     * @param $rootNodeName
     * @param $xmlns
     * @return \SimpleXMLElement Массив объектов, добавляемых в тело xml-запроса $elementsArray
     */
    private function formatTemplateRequest($elementsArray, ChannelManagerConfigInterface $config, $rootNodeName, $xmlns)
    {
        /** @var ExpediaConfig $config */
        $rootNode = new \SimpleXMLElement('<' . $rootNodeName . '/>');

        $rootNode->addAttribute('xmlns', $xmlns);

        $authNode = $rootNode->addChild('Authentication');
        $authNode->addAttribute('username', $config->getUsername());
        $authNode->addAttribute('password', $config->getPassword());

        $hotelNode = $rootNode->addChild('Hotel');
        $hotelNode->addAttribute('id', $config->getHotelId());

        //Добавляем в шаблон данные, преобразуя при этом SimpleXmlElement-ы в DomDocument-ы
        $rootNodeDomDocument = dom_import_simplexml($rootNode);
        foreach ($elementsArray as $element) {
            $elementDomDocument = dom_import_simplexml($element);
            $rootNodeDomDocument->appendChild($rootNodeDomDocument->ownerDocument->importNode($elementDomDocument,
                true));
        }

        return $rootNode->asXML();
    }

    /**
     * @param ExpediaConfig $config
     * @param $status
     * @return \SimpleXMLElement
     */
    public function formatGetAllBookingsData(ExpediaConfig $config, $status)
    {
        $paramsNode = new \SimpleXMLElement('<ParamSet/>');
        $hotelNode = $paramsNode->addChild('Status');
        $hotelNode->addAttribute('value', $status);

        return $this->formatTemplateRequest([$paramsNode], $config, 'BookingRetrievalRQ',
            self::BOOKING_RETRIEVAL_REQUEST_NAMESPACE);
    }
}