<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\ChannelManagerBundle\Model\PricePeriod;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;

/**
 * Формирует данные о ценах, ограничениях и квотах, возвращая массивы объектов типа PricePeriod и тд
 * Class ResponseDataFormatter
 * @package MBH\Bundle\ChannelManagerBundle\Services
 */
abstract class AbstractRequestDataFormatter
{
    /** @var ContainerInterface $container */
    private $container;
    private $dm;
    private $roomManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->roomManager = $container->get('mbh.hotel.room_type_manager');
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
    }

    abstract protected function formatPriceData(PriceCache $priceCache, $serviceRoomTypeId, $serviceTariffId, &$resultArray, \DateTime $day);
    abstract protected function formatRestrictionData(Restriction $restriction, $serviceRoomTypeId, $serviceTariffId, &$resultArray, $isPriceSet, \DateTime $day);
    abstract protected function formatRoomData(RoomCache $roomCache, $serviceRoomTypeId, &$resultArray, \DateTime $day);

    public function getPriceData($begin, $end, RoomType $roomType, $serviceTariffs, ChannelManagerConfigInterface $config)
    {
        $resultData = [];
        $roomTypes = $this->getRoomTypes($config);
        $tariffs = $this->getTariffs($config);
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
                        $this->formatPriceData($priceCache, $roomTypeInfo['syncId'], $tariff['syncId'], $resultData, $day);
                    } else {
                        $this->formatPriceData(null, $roomTypeInfo['syncId'], $tariff['syncId'], $resultData, $day);
                    }
                }
            }
        }

        return $resultData;
    }



    public function getRestrictionData($begin, $end, RoomType $roomType, $serviceTariffs, ChannelManagerConfigInterface $config)
    {
        $resultData = [];

        $roomTypes = $this->getRoomTypes($config);
        $tariffs = $this->getTariffs($config);
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
            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                /** @var \DateTime $day */
                foreach ($tariffs as $tariffId => $tariff) {

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

                    if (isset($restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                        $restriction = $restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')];
                        $this->formatRestrictionData($restriction, $roomTypeInfo['syncId'], $tariff['syncId'], $resultData, $isPriceSet, $day);
                    } else {
                        $this->formatRestrictionData(null, $roomTypeInfo['syncId'], $tariff['syncId'], $resultData, $isPriceSet, $day);
                    }
                }
            }
        }

        return $resultData;
    }

    public function getRoomData($begin, $end, RoomType $roomType, ChannelManagerConfigInterface $config)
    {
        $resultData = [];

            $roomTypes = $this->getRoomTypes($config);
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
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        $roomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $this->formatRoomData($roomCache, $roomTypeInfo['syncId'], $resultData, $day);
                    } else {
                        $this->formatRoomData(null, $roomTypeInfo['syncId'], $resultData, $day);
                    }
                }
            }

        return $resultData;
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

    /**
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    private function getRoomTypes(ChannelManagerConfigInterface $config, $byService = false)
    {
        $result = [];

        foreach ($config->getRooms() as $room) {
            /** @var Room $room */
            $roomType = $room->getRoomType();
            if (empty($room->getRoomId()) || !$roomType->getIsEnabled() || !empty($roomType->getDeletedAt())) {
                continue;
            }

            if ($byService) {
                $result[$room->getRoomId()] = [
                    'syncId' => $room->getRoomId(),
                    'doc' => $roomType
                ];
            } else {
                $result[$roomType->getId()] = [
                    'syncId' => $room->getRoomId(),
                    'doc' => $roomType
                ];
            }
        }
        return $result;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    private function getTariffs(ChannelManagerConfigInterface $config, $byService = false)
    {
        $result = [];

        foreach ($config->getTariffs() as $configTariff) {
            /** @var Tariff $configTariff */
            $tariff = $configTariff->getTariff();

            if ($configTariff->getTariffId() === null || !$tariff->getIsEnabled() || !empty($tariff->getDeletedAt())) {
                continue;
            }

            if ($byService) {
                $result[$configTariff->getTariffId()] = [
                    'syncId' => $configTariff->getTariffId(),
                    'doc' => $tariff
                ];
            } else {
                $result[$tariff->getId()] = [
                    'syncId' => $configTariff->getTariffId(),
                    'doc' => $tariff
                ];
            }
        }

        return $result;
    }


}