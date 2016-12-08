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
    ) {
        $dateDifferenceInYears = date_diff(new \DateTime(), $day);
        //В Expedia невозможно установить данные более чем на 2 года вперед
        if ($dateDifferenceInYears->y < 2) {
            if ($priceCache) {
                $priceCalculationService = $this->container->get('mbh.calculation');
                $pricesByOccupantsCount = $priceCalculationService->calcPrices($roomType, $tariff, $day, $day);
            } else {
                $pricesByOccupantsCount = [];
            }

            $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][$serviceTariffId]['prices'] = $pricesByOccupantsCount;
        }
    }

    /**
     * Форматирование данных в формате xml, отправляемых в запросе обновления цен сервиса
     * @param $begin
     * @param ChannelManagerConfigInterface $end
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
    ) {
        $requestDataArray = $this->getPriceData($begin, $end, $roomTypes, $serviceTariffs, $config);
        $xmlElements = [];
        foreach ($requestDataArray as $roomTypeId => $pricesByDates) {

            foreach ($pricesByDates as $dateString => $pricesByTariffs) {
                $xmlRoomTypeData = new \SimpleXMLElement('<AvailRateUpdate/>');

                $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
                $dateRangeElement->addAttribute('from', $dateString);
                $dateRangeElement->addAttribute('to', $dateString);

                $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                $roomTypeElement->addAttribute('id', $roomTypeId);

                foreach ($pricesByTariffs as $tariffId => $tariffPricesInfo) {
                    $hasPriceList = isset($tariffPricesInfo['prices']) && count($tariffPricesInfo['prices']) > 0;

                    $ratePlanElement = $roomTypeElement->addChild('RatePlan');
                    $ratePlanElement->addAttribute('id', $tariffId);
                    $ratePlanElement->addAttribute('closed', $hasPriceList ? 'false' : 'true');

                    $rateElement = $ratePlanElement->addChild('Rate');
                    $rateElement->addAttribute('currency',
                        strtoupper($this->container->getParameter('locale.currency')));

                    foreach ($tariffPricesInfo['prices'] as $price) {
                        $perOccupancyElement = $rateElement->addChild('PerOccupancy');
                        $perOccupancyElement->addAttribute('rate', $price['total']);
                        $perOccupancyElement->addAttribute('occupancy', $price['adults']);
                    }
                }
                $xmlElements[] = $xmlRoomTypeData;
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

        foreach ($requestDataArray as $roomTypeId => $roomQuotasByDates) {
            $xmlRoomTypeData = new \SimpleXMLElement('<AvailRateUpdate/>');

            foreach ($roomQuotasByDates as $dateString => $roomCache) {

                /** @var RoomCache $roomCache */
                $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
                $dateRangeElement->addAttribute('from', $dateString);
                $dateRangeElement->addAttribute('to', $dateString);

                $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                $roomTypeElement->addAttribute('id', $roomTypeId);
                $roomTypeElement->addAttribute('closed', $roomCache ? "false" : "true");

                $inventoryElement = $roomTypeElement->addChild('Inventory');
                $inventoryElement->addAttribute('totalInventoryAvailable', $roomCache ? $roomCache->getLeftRooms() : 0);
            }

            $xmlElements[] = $xmlRoomTypeData;
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
     */
    protected function formatRestrictionData(
        $restriction,
        RoomType $roomType,
        Tariff $tariff,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        $isPriceSet,
        \DateTime $day
    ) {
        $dateDifferenceInYears = date_diff(new \DateTime(), $day);
        //В Expedia невозможно установить данные более чем на 2 года вперед
        if ($dateDifferenceInYears->y < 2) {
            $isClosed = $restriction ? ($restriction->getClosed() || !$isPriceSet) : !$isPriceSet;

            $restrictionData = ['restriction' => $restriction, 'isClosed' => $isClosed ? 'true' : 'false'];

            $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][$serviceTariffId] = $restrictionData;
        }
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
    ) {
        $xmlElements = [];

        $requestDataArray = $this->getRestrictionData($begin, $end, $roomTypes, $serviceTariffs, $config);

        foreach ($requestDataArray as $roomTypeId => $restrictionsByDates) {

            foreach ($restrictionsByDates as $dateString => $restrictionsByTariffs) {
                $xmlRoomTypeData = new \SimpleXMLElement('<AvailRateUpdate/>');

                $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
                $dateRangeElement->addAttribute('from', $dateString);
                $dateRangeElement->addAttribute('to', $dateString);

                $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                $roomTypeElement->addAttribute('id', $roomTypeId);

                foreach ($restrictionsByTariffs as $tariffId => $restrictionData) {

                    $ratePlanElement = $roomTypeElement->addChild('RatePlan');
                    $ratePlanElement->addAttribute('id', $tariffId);
                    $ratePlanElement->addAttribute('closed', $restrictionData['isClosed']);

                    /** @var Restriction $restriction */
                    $restriction = $restrictionData['restriction'];
                    $isClosedToArrival = $restriction && !$restriction->getClosedOnArrival() ? 'false' : 'true';
                    $isClosedToDeparture = $restriction && !$restriction->getClosedOnDeparture() ? 'false' : 'true';
                    $minStay = $restriction && $restriction->getMinStay() ? $restriction->getMinStay() : self::EXPEDIA_MIN_STAY;
                    $maxStay = $restriction && $restriction->getMaxStay() ? $restriction->getMaxStay() : self::EXPEDIA_MAX_STAY;

                    $restrictionsElement = $ratePlanElement->addChild('Restrictions');
                    //TODO: Как обрабатывать ситуации, если устанавливается значение больше 28? По дефолту будет приходить ошибка
                    $restrictionsElement->addAttribute('closedToArrival', $isClosedToArrival);
                    $restrictionsElement->addAttribute('closedToDeparture', $isClosedToDeparture);
                    $restrictionsElement->addAttribute('minLOS', $minStay);
                    $restrictionsElement->addAttribute('maxLOS', $maxStay);
                }
                $xmlElements[] = $xmlRoomTypeData;
            }
        }

        return $this->formatTemplateRequest($xmlElements, $config,
            'AvailRateUpdateRQ', self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
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

            $startDate = \DateTime::createFromFormat('Y-m-d', new \DateTime());
            $endDate = \DateTime::createFromFormat('Y-m-d', new \DateTime('+2 years'));
            $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
            $dateRangeElement->addAttribute('from', $startDate);
            $dateRangeElement->addAttribute('to', $endDate);

            $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
            $roomTypeElement->addAttribute('id', $roomTypeData['id']);
            $roomTypeElement->addAttribute('closed', 'true');

            $requestData[] = $xmlRoomTypeData;
        }

        return $this->formatTemplateRequest($requestData, $config, 'AvailRateUpdateRQ',
            self::AVAILABILITY_AND_RATES_REQUEST_NAMESPACE);
    }

    public function formatNotifyServiceData(AbstractOrderInfo $orderInfo, $config)
    {
        /** @var ExpediaOrderInfo $orderInfo */
        $confirmNumbersElement = new \SimpleXMLElement('BookingConfirmNumbers');
        $confirmNumberElement = $confirmNumbersElement->addChild('BookingConfirmNumber');
        $confirmNumberElement->addAttribute('bookingID', $orderInfo->getChannelManagerOrderId());
        $confirmNumberElement->addAttribute('bookingType', $orderInfo->getOrderStatusType());
        $confirmNumberElement->addAttribute('confirmNumber', $orderInfo->getConfirmNumber());
        $confirmNumberElement->addAttribute('confirmTime', new \DateTime('now', new DateTimeZone("UTC")));

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
}