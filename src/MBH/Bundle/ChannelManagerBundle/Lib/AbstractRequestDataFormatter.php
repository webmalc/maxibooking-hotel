<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\ChannelManagerBundle\Model\PricePeriod;
use MBH\Bundle\ChannelManagerBundle\Model\RestrictionData;
use MBH\Bundle\ChannelManagerBundle\Model\RoomData;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Формирует данные о ценах, ограничениях и квотах, возвращая массивы объектов типа PricePeriod и тд
 * Class ResponseDataFormatter
 * @package MBH\Bundle\ChannelManagerBundle\Services
 */
abstract class AbstractRequestDataFormatter
{
    /** @var ContainerInterface $container */
    protected $container;
    protected $dm;
    protected $roomManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->roomManager = $container->get('mbh.hotel.room_type_manager');
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
    }

    abstract public function formatPriceRequestData($requestData, ChannelManagerConfigInterface $config);
    abstract public function formatRoomRequestData($requestData, ChannelManagerConfigInterface $config);
    abstract public function formatRestrictionRequestData($requestData, ChannelManagerConfigInterface $config);
    abstract public function formatCloseForConfigData(ChannelManagerConfigInterface $config);
    abstract public function formatGetBookingsData(ChannelManagerConfigInterface $config);

    /**
     * Возвращает массив данных, отправляемых в запросе обновления цен
     * @param $begin
     * @param $end
     * @param RoomType $roomType
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function getPriceData($begin, $end, RoomType $roomType, $serviceTariffs, ChannelManagerConfigInterface $config)
    {
        $resultData = [];

        $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
        $roomTypes = $channelManagerHelper->getRoomTypesSyncData($config);
        $tariffs = $channelManagerHelper->getTariffsSyncData($config);

        $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
            $begin,
            $end,
            $config->getHotel(),
            $this->getRoomTypeArray($roomType),
            [],
            true,
            $this->roomManager->useCategories
        );

        foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
            foreach ($tariffs as $tariffId => $tariff) {
                /** @var PricePeriod $currentPricePeriod */
                $currentPricePeriod = null;
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    /** @var \DateTime $day */
                    if (!isset($serviceTariffs[$tariff['syncId']])
                        || (isset($serviceTariffs[$tariff['syncId']]['readonly']) && $serviceTariffs[$tariff['syncId']]['readonly'])
                        || (isset($serviceTariffs[$tariff['syncId']]['is_child_rate']) && $serviceTariffs[$tariff['syncId']]['is_child_rate'])
                    ) {
                        continue;
                    }

                    if (!empty($serviceTariffs[$tariff['syncId']]['rooms']) && !in_array($roomTypeInfo['syncId'],
                            $serviceTariffs[$tariff['syncId']]['rooms'])
                    ) {
                        continue;
                    }

                    if (isset($priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                        /** @var PriceCache $priceCache */
                        $priceCache = $priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')];
                        $this->formatPriceData($priceCache, $roomTypeInfo['doc'], $tariff['doc'],
                            $roomTypeInfo['syncId'], $tariff['syncId'], $resultData, $day);
                    } else {
                        $this->formatPriceData(null, $roomTypeInfo['doc'], $tariff['doc'],
                            $roomTypeInfo['syncId'], $tariff['syncId'], $resultData, $day);
                    }
                }
            }
        }

        return $resultData;
    }

    /**
     * Формирует массив, содержащий данные о ценах по типам комнат и тарифам.
     * Если требуется другой формат данных можно переопределить
     * @param PriceCache|null $priceCache
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param $serviceRoomTypeId
     * @param $serviceTariffId
     * @param $resultArray
     * @param \DateTime $day
     */
    protected function formatPriceData(PriceCache $priceCache,
        RoomType $roomType,
        Tariff $tariff,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        \DateTime $day)
    {
        $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][$serviceTariffId][] = $priceCache;
    }

    /**
     * Возвращает массив данных, отправляемых в запросе обновления ограничений
     * @param $begin
     * @param $end
     * @param RoomType $roomType
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function getRestrictionData($begin, $end, RoomType $roomType, $serviceTariffs, ChannelManagerConfigInterface $config)
    {
        $resultData = [];

        $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
        $roomTypes = $channelManagerHelper->getRoomTypesSyncData($config);
        $tariffs = $channelManagerHelper->getTariffsSyncData($config);

        $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $begin,
            $end,
            $config->getHotel(),
            $roomType ? [$roomType->getId()] : [],
            [],
            true
        );
        $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
            $begin,
            $end,
            $config->getHotel(),
            $roomType ? [$roomType->getId()] : [],
            [],
            true
        );

        foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
            foreach ($tariffs as $tariffId => $tariff) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    /** @var \DateTime $day */

                    if (!isset($serviceTariffs[$tariff['syncId']])
                        || (isset($serviceTariffs[$tariff['syncId']]['readonly']) && $serviceTariffs[$tariff['syncId']]['readonly'])
                        || (isset($serviceTariffs[$tariff['syncId']]['is_child_rate']) && $serviceTariffs[$tariff['syncId']]['is_child_rate'])
                    ) {
                        continue;
                    }

                    if (!empty($serviceTariffs[$tariff['syncId']]['rooms']) && !in_array($roomTypeInfo['syncId'],
                            $serviceTariffs[$tariff['syncId']]['rooms'])
                    ) {
                        continue;
                    }

                    $isPriceSet = false;
                    if (isset($priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                        $isPriceSet = true;
                    }

                    $restriction = null;
                    if (isset($restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                        $restriction = $restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')];
                    }

                    $this->formatRestrictionData($restriction, $roomTypeInfo['doc'], $tariff['doc'],
                        $roomTypeInfo['syncId'], $tariff['syncId'], $resultData, $isPriceSet, $day);
                }
            }
        }

        return $resultData;
    }

    /**
     * Формирует массив, содержащий данные об ограничениях по типам комнат и тарифов
     * Если требуется другой формат данных можно переопределить
     * @param Restriction|null $restriction
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param $serviceRoomTypeId
     * @param $serviceTariffId
     * @param $resultArray
     * @param $isPriceSet
     * @param \DateTime $day
     */
    protected function formatRestrictionData(Restriction $restriction, RoomType $roomType, Tariff $tariff,
        $serviceRoomTypeId, $serviceTariffId, &$resultArray, $isPriceSet, \DateTime $day)
    {
        //TODO: Стоит ли так делать?
        if ($restriction) {
            $restriction->setClosed($restriction->getClosed() || (!$isPriceSet ? true : false));
        }
        $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][$serviceTariffId][] = $restriction;
    }

    /**
     * Возвращает массив данных, отправляемых в запросе обновления количества свободных комнат
     * @param $begin
     * @param $end
     * @param RoomType $roomType
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function getRoomData($begin, $end, RoomType $roomType, ChannelManagerConfigInterface $config)
    {
        $resultData = [];

        $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
        $roomTypes = $channelManagerHelper->getRoomTypesSyncData($config);
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $begin,
            $end,
            $config->getHotel(),
            $roomType ? [$roomType->getId()] : [],
            null,
            true
        );

        foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                /** @var \DateTime $day */
                $roomCache = null;
                if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                    $roomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                }
                $this->formatRoomData($roomCache, $roomTypeInfo['syncId'], $resultData, $day);
            }
        }

        return $resultData;
    }

    /**
     * Формирует массив, содержащий данные о заполненности комнат по типам комнат
     * Если требуется другой формат данных можно переопределить
     * @param RoomCache $roomCache
     * @param $serviceRoomTypeId
     * @param $resultArray
     * @param \DateTime $day
     */
    protected function formatRoomData(RoomCache $roomCache, $serviceRoomTypeId, &$resultArray, \DateTime $day)
    {
        $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][] = $roomCache;
    }

    /**
     * @param RoomType $roomType
     * @return array
     */
    private function getRoomTypeArray(RoomType $roomType = null)
    {
        if (!$roomType) {
            return [];
        }
        if (!$this->roomManager->useCategories) {
            return [$roomType->getId()];
        } else {
            if (!$roomType->getCategory()) {
                return [0];
            }
            return [$roomType->getCategory()->getId()];
        }
    }

}