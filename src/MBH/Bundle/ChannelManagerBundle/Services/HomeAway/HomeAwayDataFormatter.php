<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;

class HomeAwayDataFormatter extends AbstractRequestDataFormatter
{

    /**
     * Форматирование данных, отправляемых в запросе обновления цен сервиса
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
    ) {
        $requestDataArray = $this->getPriceData($begin, $end, $roomTypes, $serviceTariffs, $config);
        $currency = $this->container->getParameter('locale.currency');
        foreach ($requestDataArray as $roomTypeId => $pricesByDates) {
            $ratesElement = new \SimpleXMLElement('<ratePeriods/>');
            $ratesElement->addChild('listingExternalId', $roomTypeId);
            $ratesElement->addChild('unitExternalId', $roomTypeId);
            $ratePeriodsElement = $ratesElement->addChild('ratePeriods');
            foreach ($pricesByDates as $dateString => $priceByTariff) {
                /** @var PriceCache $priceCache */
                $priceCache = current($priceByTariff);
                $ratePeriodElement = $ratePeriodsElement->addChild('ratePeriod');
                $dateRangeElement = $ratePeriodElement->addChild('dateRange');
                $dateRangeElement->addChild('beginDate', $dateString);
                $dateRangeElement->addChild('endDate', $dateString);

                $ratesElement = $dateRangeElement->addChild('rates');
                $rateElement = $ratesElement->addChild('rate');
                $rateElement->addAttribute('rateType', 'EXTRA_NIGHT');
                $amountElement = $rateElement->addChild('amount', $priceCache->getPrice());
                $amountElement->addAttribute('currency', $currency);
                $ratePeriodElements[] = $ratePeriodElement;
            }
        }

        return $this->formatTemplateData('sdf');
    }

    /**
     * Форматирование данных, отправляемых в запросе обновления квот на комнаты
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRoomRequestData($begin, $end, $roomTypes, ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatRoomRequestData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе обновления ограничений
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
        // TODO: Implement formatRestrictionRequestData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе закрытия продаж
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatCloseForConfigData(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatCloseForConfigData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе получения броней
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatGetBookingsData(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatGetBookingsData() method.
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
                $this->container->get('router')->generate($urlName, ['listingId' => $roomType->getId()]));
//            $listingEntry->addChild('')
        }

        return $rootElement;
    }

    private function formatAvailabilityData(
        \DateTime $begin,
        \DateTime $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    ) {
        $restrictionData = $this->getRestrictionData($begin, $end, $roomTypes, $serviceTariffs, $config);
        $roomData = $this->getRoomData($begin, $end, $roomTypes, $config);

        foreach ($restrictionData as $roomTypeId => $restrictionsByDates) {
            $roomDataByDates = $roomData[$roomTypeId];
            $availabilityElement = new \SimpleXMLElement('unitAvailabilityEntities');
            //TODO: Сменить на свои id
            $availabilityElement->addChild('listingExternalId', $roomTypeId);
            $availabilityElement->addChild('unitExternalId', $roomTypeId);
            $unitAvailabilityElement = $availabilityElement->addChild('unitAvailability');

            $dateRangeElement = $availabilityElement->addChild('dateRange');
            $dateRangeElement->addChild('beginDate', $begin->format('Y-m-d'));
            $dateRangeElement->addChild('endDate', $end->format('Y-m-d'));

            //TODO: Уточнить
            $unitAvailabilityElement->addChild('maxStayDefault', 28);
            $availabilityConfigElement = $unitAvailabilityElement->addChild('unitAvailabilityConfiguration');

            $availabilityString = '';
            $maxStayString = '';
            $minStayString = '';
            foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
                /** @var \DateTime $day */
                $dayString = $day->format('Y-m-d');
                /** @var Restriction $restrictionData */
                $restrictionData = $restrictionsByDates[$dayString];
                /** @var RoomCache $roomData */
                $roomData = $roomDataByDates[$dayString];
                $isAvailable = $roomData && !$roomData->getIsClosed() && $roomData->getLeftRooms() > 0
                    && (!$restrictionData || !$restrictionData->getClosed());
                $availabilityString .= $isAvailable ? 'Y' : 'N';
                $maxStayString .= $restrictionData->getMaxStay() ? $restrictionData->getMaxStay() : 0;
                $minStayString .= $restrictionData->getMinStay() ? $restrictionData->getMinStay() : 0;
            }

            $availabilityConfigElement->addChild('availability', $availabilityString);
            $availabilityConfigElement->addChild('maxStay', $maxStayString);
            $availabilityConfigElement->addChild('minStay', $minStayString);
        }
    }

    private function formatTemplateData(array $xmlElements)
    {

    }
}