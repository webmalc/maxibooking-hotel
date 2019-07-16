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
use MBH\Bundle\PriceBundle\Services\PriceCacheRepositoryFilter;
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

    /**
     * @var PriceCacheRepositoryFilter
     */
    protected $priceCacheFilter;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->roomManager = $container->get('mbh.hotel.room_type_manager');
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->priceCacheFilter = $container->get('mbh.price_cache_repository_filter');
    }

    /**
     * Форматирование данных, отправляемых в запросе обновления цен сервиса
     * @param $begin
     * @param $end
     * @param $roomType
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @param array $restrictionsMap
     * @return mixed
     */
    abstract public function formatPriceRequestData(
        $begin,
        $end,
        $roomType,
        $serviceTariffs,
        ChannelManagerConfigInterface $config,
        array $restrictionsMap
    );

    /**
     * Форматирование данных, отправляемых в запросе обновления квот на комнаты
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    abstract public function formatRoomRequestData($begin, $end, $roomTypes, ChannelManagerConfigInterface $config);

    /**
     * Форматирование данных, отправляемых в запросе обновления ограничений
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @param array $restrictionsMap
     * @return mixed
     */
    abstract public function formatRestrictionRequestData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config,
        array $restrictionsMap
    );

    /**
     * Форматирование данных, отправляемых в запросе закрытия продаж
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    abstract public function formatCloseForConfigData(ChannelManagerConfigInterface $config);

    /**
     * Форматирование данных, отправляемых в запросе получения броней
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    abstract public function formatGetBookingsData(ChannelManagerConfigInterface $config);

    /**
     * Возвращает массив данных, отправляемых в запросе обновления цен
     * @param $begin
     * @param $end
     * @param $roomType
     * @param $serviceTariffs Массив актуальных данных о тарифах, полученный с сервиса
     * @param ChannelManagerConfigInterface $config
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function getPriceData($begin, $end, $roomType, $serviceTariffs, ChannelManagerConfigInterface $config)
    {
        $resultData = [];

        $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
        $roomTypeSyncData = $channelManagerHelper->getRoomTypesSyncData($config);
        $tariffs = $channelManagerHelper->getTariffsSyncData($config, true);

        $priceCaches =  $this->priceCacheFilter->filterFetch(
            $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $this->getRoomTypeArray($roomType),
                [],
                true,
                $this->roomManager->useCategories
            )
        );

        foreach ($roomTypeSyncData as $roomTypeId => $roomTypeInfo) {
            foreach ($tariffs as $serviceTariffId => $tariffInfo) {
                if (!$this->checkTariff($serviceTariffs, $tariffInfo['syncId'], $roomTypeInfo['syncId'])) {
                    continue;
                }
                /** @var Tariff $tariff */
                $tariff = $tariffInfo['doc'];
                $tariffId = $tariff->getId();

                foreach (new \DatePeriod($begin, new \DateInterval('P1D'), (clone $end)->modify('+ 1 day')) as $day) {
                    /** @var PriceCache $priceCache */
                    $priceCache = null;

                    /** @var \DateTime $day */
                    if (isset($priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')])
                    && $priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')]->getPrice()) {
                        /** @var PriceCache $priceCache */
                        $priceCache = $priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')];
                    }

                    $this->formatPriceData($priceCache, $roomTypeInfo['doc'], $tariffInfo['doc'],
                        $roomTypeInfo['syncId'], $tariffInfo['syncId'], $resultData, $day);
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
    protected function formatPriceData(
        $priceCache,
        RoomType $roomType,
        Tariff $tariff,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        \DateTime $day
    ) {
        $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][$serviceTariffId][] = $priceCache;
    }

    /**
     * Возвращает массив данных, отправляемых в запросе обновления ограничений
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @param array $restrictionsMap
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function getRestrictionData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config,
        array $restrictionsMap
    ) {
        $resultData = [];

        $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
        $roomTypesSyncData = $channelManagerHelper->getRoomTypesSyncData($config);
        $tariffs = $channelManagerHelper->getTariffsSyncData($config, true);

        $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $begin,
            $end,
            $config->getHotel(),
            $this->getRoomTypeArray($roomTypes),
            [],
            true
        );
        $priceCaches = $this->priceCacheFilter->filterFetch(
            $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $this->getRoomTypeArray($roomTypes),
                [],
                true
            )
        );

        foreach ($roomTypesSyncData as $roomTypeId => $roomTypeInfo) {
            foreach ($tariffs as $serviceTariffId => $tariffInfo) {
                if (!$this->checkTariff($serviceTariffs, $serviceTariffId, $roomTypeInfo['syncId'])) {
                    continue;
                }
                /** @var Tariff $tariff */
                $tariff = $tariffInfo['doc'];
                $tariffId = $tariff->getId();

                foreach (new \DatePeriod($begin, new \DateInterval('P1D'), (clone $end)->modify('+ 1 day')) as $day) {
                    /** @var \DateTime $day */

                    $isClosed = $restrictionsMap[$config->getHotel()->getId()][$tariffId][$roomTypeId][$day->format('d.m.Y')];

                    $restriction = null;
                    if (isset($restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                        $restriction = $restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')];
                    }

                    $this->formatRestrictionData($restriction, $roomTypeInfo['doc'], $tariff,
                        $roomTypeInfo['syncId'], $serviceTariffId, $resultData, $isClosed, $day, $serviceTariffs);
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
    protected function formatRestrictionData(
        ?Restriction $restriction,
        RoomType $roomType,
        Tariff $tariff,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        $isPriceSet,
        \DateTime $day,
        $serviceTariffs
    ) {
        if ($restriction) {
            $restriction->setClosed($restriction->getClosed() || (!$isPriceSet ? true : false));
        }
        $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][$serviceTariffId] = $restriction;
    }

    /**
     * Возвращает массив данных, отправляемых в запросе обновления количества свободных комнат
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    protected function getRoomData($begin, $end, $roomTypes, ChannelManagerConfigInterface $config)
    {
        $resultData = [];

        $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
        $roomTypesSyncData = $channelManagerHelper->getRoomTypesSyncData($config);
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $begin,
            $end,
            $config->getHotel(),
            $this->getRoomTypeArray($roomTypes),
            false,
            true
        );

        foreach ($roomTypesSyncData as $roomTypeId => $roomTypeInfo) {
            foreach (new \DatePeriod($begin, new \DateInterval('P1D'), (clone $end)->modify('+ 1 day')) as $day) {
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
    protected function formatRoomData($roomCache, $serviceRoomTypeId, &$resultArray, \DateTime $day)
    {
        $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')] = $roomCache;
    }

    /**
     * Метод проверки актуальности текущего тарифа.
     * Для добавления дополнительных проверок можно переопределить.
     * @param $serviceTariffs Массив, содержащий актуальные данные о тарифах, полученных с сервиса
     * @param int $serviceTariffId id текущего тарифа, полученного с сервиса
     * @param int $serviceRoomTypeId id текущего типа комнаты, полученного с сервиса
     * @return bool
     */
    protected function checkTariff($serviceTariffs, $serviceTariffId, $serviceRoomTypeId)
    {
        if (!isset($serviceTariffs[$serviceTariffId])
            || (isset($serviceTariffs[$serviceTariffId]['readonly']) && $serviceTariffs[$serviceTariffId]['readonly'])
            || (isset($serviceTariffs[$serviceTariffId]['is_child_rate']) && $serviceTariffs[$serviceTariffId]['is_child_rate'])
        ) {
            return false;
        }

        if (!empty($serviceTariffs[$serviceTariffId]['rooms'])
            && !in_array($serviceRoomTypeId, $serviceTariffs[$serviceTariffId]['rooms'])
        ) {
            return false;
        }

        return true;
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
