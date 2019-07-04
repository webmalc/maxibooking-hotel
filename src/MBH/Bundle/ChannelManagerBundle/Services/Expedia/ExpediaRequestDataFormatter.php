<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\BaseBundle\Service\Utils;
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
    const MAX_NUMBER_OF_UPDATES = 5000;
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
     * @param $roomType
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return array
     * @throws \Exception
     */
    public function formatPriceRequestData(
        $begin,
        $end,
        $roomType,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    )
    {
        $pricesRequestData = [];
        $requestDataArray = $this->getPriceData($begin, $end, $roomType, $serviceTariffs, $config);
        $xmlElements = [];
        $priceCalculator = $this->container->get('mbh.calculation');
        $periodsCompiler = $this->container->get('mbh.periods_compiler');
        $localCurrency = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()->getCurrency();
        $compareArraysCallback = static function ($first, $second) {
            if ($first === null && $second === null) {
                return true;
            }

            if (!is_array($first) || !is_array($second) || count($first) !== count($second)) {
                return false;
            }

            foreach ($first as $combination => $data) {
                if (!isset($second[$combination]) || $data['total'] !== $second[$combination]['total']) {
                    return false;
                }
            }

            return true;
        };

        $numberOfUpdates = 0;
        foreach ($requestDataArray as $roomTypeId => $pricesByTariffs) {
            foreach ($pricesByTariffs as $tariffId => $pricesByDates) {
                $tariffData = $serviceTariffs[$tariffId];
                if (isset($tariffData['derivationRules']['rateDerivationRules'])
                    && !empty($tariffData['derivationRules']['rateDerivationRules'])) {
                    continue;
                }

                $calculatedPricesByDates = [];
                /** @var PriceCache $price */
                foreach ($pricesByDates as $date => $price) {
                    $calculatedPricesByDates[$date] = (($price === null) || $price->getPrice() === (float)0)
                        ? null
                        : $priceCalculator->calcPrices(
                            $price->getRoomType(),
                            $price->getTariff(),
                            $price->getDate(),
                            $price->getDate(),
                            0,
                            0,
                            null,
                            false,
                            null,
                            true,
                            false
                        );
                }

                $periodsData = $periodsCompiler->getPeriodsByCallback($begin, $end, $calculatedPricesByDates, $compareArraysCallback, 'Y-m-d');
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

                    $hasPriceList = false;
                    $priceList = [];
                    $priceData = $periodData['data'];
                    if ($priceData !== null) {
                        $hasPriceList = is_array($priceData) && count($priceData) > 0;
                        $priceList = $priceData;
                    }

                    $ratePlanElement = $roomTypeElement->addChild('RatePlan');
                    $ratePlanElement->addAttribute('id', $tariffId);
                    $ratePlanElement->addAttribute('closed', $hasPriceList ? 'false' : 'true');

                    $rateElement = $ratePlanElement->addChild('Rate');
                    $rateElement->addAttribute('currency', strtoupper($localCurrency));
                    if ($hasPriceList) {
                        foreach ($priceList as $price) {
                            if (!isset($price['children']) || $price['children'] == 0) {
                                $perOccupancyElement = $rateElement->addChild('PerOccupancy');
                                $perOccupancyElement->addAttribute('rate', $price['total']);
                                $perOccupancyElement->addAttribute('occupancy', $price['adults']);
                            }
                        }
                    }

                    $currentNumberOfUpdates = $this->calcNumberOfUpdates($hasPriceList ? count($priceList) + 1 : 1, $periodBegin, $periodEnd);
                    if ($this->isNumberOfUpdatesExceeded($numberOfUpdates, $currentNumberOfUpdates)) {
                        $pricesRequestData[] = $this->formatTemplateRequest($xmlElements, $config,
                            'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
                        $numberOfUpdates = 0;
                        $xmlElements = [];
                    }
                    $xmlElements[] = $xmlRoomTypeData;
                    $numberOfUpdates += $currentNumberOfUpdates;
                }
            }
        }

        if ($numberOfUpdates > 0) {
            $pricesRequestData[] = $this->formatTemplateRequest($xmlElements, $config,
                'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
        }

        return $pricesRequestData;
    }

    /**
     * Форматирование данных в формате xml, отправляемых в запросе обновления квот на комнаты
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     * @throws \Exception
     */
    public function formatRoomRequestData($begin, $end, $roomTypes, ChannelManagerConfigInterface $config)
    {
        $roomsRequestData = [];
        $xmlElements = [];
        $numberOfUpdates = 0;
        $requestDataArray = $this->getRoomData($begin, $end, $roomTypes, $config);
        $periodsCompiler = $this->container->get('mbh.periods_compiler');
        foreach ($requestDataArray as $roomTypeId => $roomQuotasByDates) {
            $periodsData =
                $periodsCompiler->getPeriodsByFieldNames($begin, $end, $roomQuotasByDates, ['getLeftRooms'], self::EXPEDIA_DEFAULT_DATE_FORMAT_STRING);
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
                $roomCache = $periodData['data'];
                $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                $roomTypeElement->addAttribute('id', $roomTypeId);
                $roomTypeElement->addAttribute('closed', $roomCache ? "false" : "true");

                $inventoryElement = $roomTypeElement->addChild('Inventory');
                $inventoryElement->addAttribute('totalInventoryAvailable',
                    $roomCache && $roomCache->getLeftRooms() > 0 ? $roomCache->getLeftRooms() : 0);

                $currentNumberOfUpdates = $this->calcNumberOfUpdates(1, $periodBegin, $periodEnd);
                if ($this->isNumberOfUpdatesExceeded($numberOfUpdates, $currentNumberOfUpdates)) {
                    $roomsRequestData[] = $this->formatTemplateRequest($xmlElements, $config,
                        'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
                    $xmlElements = [];
                    $numberOfUpdates = 0;
                }

                $numberOfUpdates += $currentNumberOfUpdates;
                $xmlElements[] = $xmlRoomTypeData;
            }
        }

        if ($numberOfUpdates > 0) {
            $roomsRequestData[] = $this->formatTemplateRequest($xmlElements, $config,
                'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
        }

        return $roomsRequestData;
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
     * @throws \Exception
     */
    public function formatRestrictionRequestData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    )
    {
        $restrictionRequestData = [];
        $xmlElements = [];
        $numberOfUpdates = 0;
        $comparePropertyMethods = ['getMinStay', 'getMaxStay', 'getClosedOnArrival', 'getClosedOnDeparture', 'getClosed'];
        $requestDataArray = $this->getRestrictionData($begin, $end, $roomTypes, $serviceTariffs, $config);
        $periodsCompiler = $this->container->get('mbh.periods_compiler');
        foreach ($requestDataArray as $roomTypeId => $restrictionsByTariffs) {
            foreach ($restrictionsByTariffs as $tariffId => $restrictionsByDates) {
                $tariffData = $serviceTariffs[$tariffId];
                $hasDerivationRules = isset($tariffData['derivationRules']);
                if ($hasDerivationRules
                    && $tariffData['derivationRules']['deriveLengthOfStayRestriction']
                    && $tariffData['derivationRules']['deriveClosedToArrival']
                    && $tariffData['derivationRules']['deriveClosedToDeparture']
                ) {
                    continue;
                }

                $periodsData = $periodsCompiler->getPeriodsByFieldNames($begin, $end, $restrictionsByDates, $comparePropertyMethods, 'Y-m-d');
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
                    $restriction = $periodData['data'];
                    $restrictionData = $this->extractRestrictionData($restriction, $serviceTariffs, $tariffId);
                    $ratePlanElement->addAttribute('closed', $restrictionData['isClosed']);

                    $restrictionsElement = $ratePlanElement->addChild('Restrictions');
                    if (!$hasDerivationRules || $tariffData['derivationRules']['deriveClosedToArrival'] === false) {
                        $restrictionsElement->addAttribute('closedToArrival', $restrictionData['isClosedToArrival']);
                    }
                    if (!$hasDerivationRules || $tariffData['derivationRules']['deriveClosedToDeparture'] === false) {
                        $restrictionsElement->addAttribute('closedToDeparture', $restrictionData['isClosedToDeparture']);
                    }
                    if (!$hasDerivationRules || $tariffData['derivationRules']['deriveLengthOfStayRestriction'] === false) {
                        $restrictionsElement->addAttribute('minLOS', $restrictionData['minStay']);
                        $restrictionsElement->addAttribute('maxLOS', $restrictionData['maxStay']);
                    }

                    $currentNumberOfUpdates = $this->calcNumberOfUpdates(2, $periodBegin, $periodEnd);
                    if ($this->isNumberOfUpdatesExceeded($numberOfUpdates, $currentNumberOfUpdates)) {
                        $restrictionRequestData[] = $this->formatTemplateRequest($xmlElements, $config,
                            'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
                        $numberOfUpdates = 0;
                        $xmlElements = [];
                    }

                    $numberOfUpdates += $currentNumberOfUpdates;
                    $xmlElements[] = $xmlRoomTypeData;
                }
            }
        }

        if ($numberOfUpdates > 0) {
            $restrictionRequestData[] = $this->formatTemplateRequest($xmlElements, $config,
                'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
        }

        return $restrictionRequestData;
    }

    /**
     * @param $numberOfUpdates
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return float|int
     */
    private function calcNumberOfUpdates($numberOfUpdates, \DateTime $begin, \DateTime $end)
    {
        $differenceInDays = Utils::getDifferenceInDaysWithSign($begin, $end) + 1;

        return $numberOfUpdates * $differenceInDays;
    }

    /**
     * @param $totalNumberOfUpdates
     * @param $currentNumberOfUpdate
     * @return bool
     */
    private function isNumberOfUpdatesExceeded($totalNumberOfUpdates, $currentNumberOfUpdate)
    {
        return ($totalNumberOfUpdates + $currentNumberOfUpdate) >= self::MAX_NUMBER_OF_UPDATES;
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
            $endDate = new \DateTime('+1 years');
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
     * @return string
     */
    private function formatTemplateRequest($elementsArray, ChannelManagerConfigInterface $config, $rootNodeName, $xmlns)
    {
        /** @var ExpediaConfig $config */
        $rootNode = new \SimpleXMLElement('<' . $rootNodeName . '/>');

        $rootNode->addAttribute('xmlns', $xmlns);

        $authNode = $rootNode->addChild('Authentication');
        $authData = $this->container->getParameter('mbh.channelmanager.services')['expedia'];
        $authNode->addAttribute('username', $authData['username']);
        $authNode->addAttribute('password', $authData['password']);

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
     * @return string
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
