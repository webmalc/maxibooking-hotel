<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\ChannelManagerHelper;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class HomeAwayDataFormatter
{
    /** @var  ChannelManagerHelper $channelManagerHelper */
    private $channelManagerHelper;
    private $localeCurrency;
    /** @var  Router $router */
    private $router;
    private $dm;

    public function __construct(ChannelManagerHelper $channelManagerHelper, $localeCurrency, Router $router, DocumentManager $dm)
    {
        $this->channelManagerHelper = $channelManagerHelper;
        $this->localeCurrency = $localeCurrency;
        $this->router = $router;
        $this->dm = $dm;
    }

    /**
     * Форматирование данных, отправляемых в запросе обновления цен сервиса
     * @param $begin
     * @param $end
     * @param $serviceRoomTypeId
     * @param HomeAwayConfig $config
     * @return mixed
     * @internal param $roomTypes
     * @internal param $serviceTariffs
     */
    public function formatPriceRequestData(
        $begin,
        $end,
        $serviceRoomTypeId,
        HomeAwayConfig $config
    ) {
        $mbhRoomTypeId = $this->channelManagerHelper
            ->getMbhRoomTypeByServiceRoomTypeId($serviceRoomTypeId, $config)->getId();
        $tariffId = '';
        $priceCaches = $this->getPriceCaches($begin, $end, $config, $mbhRoomTypeId, $tariffId);
        $ratePeriods = $this->channelManagerHelper->getPeriodsFromDayEntities($begin, $end, $priceCaches, ['getPrice']);

        $ratesElement = new \SimpleXMLElement('<ratePeriods/>');
        $ratesElement->addChild('listingExternalId', $mbhRoomTypeId);
        $ratesElement->addChild('unitExternalId', $mbhRoomTypeId);
        $ratePeriodsElement = $ratesElement->addChild('ratePeriods');
        foreach ($ratePeriods as $ratePeriod) {
            $ratePeriodElement = $ratePeriodsElement->addChild('ratePeriod');
            $dateRangeElement = $ratePeriodElement->addChild('dateRange');
            $dateRangeElement->addChild('beginDate', $ratePeriod['begin']->format('Y-m-d'));
            $dateRangeElement->addChild('endDate', $ratePeriod['end']->format('Y-m-d'));

            $ratesElement = $dateRangeElement->addChild('rates');
            $rateElement = $ratesElement->addChild('rate');
            $rateElement->addAttribute('rateType', 'EXTRA_NIGHT');
            $amountElement = $rateElement->addChild('amount', $ratePeriod['entity']->getPrice());
            $amountElement->addAttribute('currency', $this->localeCurrency);
            $ratePeriodElements[] = $ratePeriodElement;
        }

        return $this->formatTemplateData('sdf');
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

    private function formatAvailabilityData($homeAwayRoomTypeId, ChannelManagerConfigInterface $config)
    {
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
        //TODO: Сменить на свои id
        $availabilityElement->addChild('listingExternalId', $mbhRoomTypeId);
        $availabilityElement->addChild('unitExternalId', $mbhRoomTypeId);
        $unitAvailabilityElement = $availabilityElement->addChild('unitAvailability');

        $dateRangeElement = $availabilityElement->addChild('dateRange');
        $dateRangeElement->addChild('beginDate', $beginDate->format('Y-m-d'));
        $dateRangeElement->addChild('endDate', $endDate->format('Y-m-d'));

        //TODO: Уточнить
        $unitAvailabilityElement->addChild('maxStayDefault', 28);
        $availabilityConfigElement = $unitAvailabilityElement->addChild('unitAvailabilityConfiguration');

        $availabilityData = $this->getAvailabilityData($beginDate, $endDate, $roomCaches, $restrictions, $priceCaches,
            $mbhRoomTypeId, $tariff);
        $availabilityConfigElement->addChild('availability', $availabilityData['availability']);
        $availabilityConfigElement->addChild('maxStay', $availabilityData['maxStay']);
        $availabilityConfigElement->addChild('minStay', $availabilityData['minStay']);
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

    private function getPriceCaches($beginDate, $endDate, ChannelManagerConfigInterface $config, $roomTypeId, $tariffId)
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

    private function formatTemplateData(array $xmlElements)
    {

    }
}