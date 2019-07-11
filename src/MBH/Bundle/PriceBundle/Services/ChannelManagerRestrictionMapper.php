<?php

namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class ChannelManagerRestrictionMapper
{

    /** @var DocumentManager */
    private $dm;

    /** @var Helper */
    private $helper;

    /** @var ?array */
    private $data;

    /** @var \DateTime */
    private $beginDate;

    /** @var \DateTime */
    private $endDate;

    /**
     * PriceCacheRestriction constructor.
     * @param DocumentManager $dm
     * @param Helper $helper
     */
    public function __construct(DocumentManager $dm, Helper $helper)
    {
        $this->dm = $dm;
        $this->helper = $helper;
    }

    /** returns array<bool> of restriction by hotelIs, tariffId, roomTypeId, date
     * room calls closed if there is no priceCache, priceCache's price is zero or if restriction is set
     * format - array[hotelId][tariffId][roomTypeId][$dateTimeFormat]
     *
     * @param array $channelManagerConfigs
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param string $dateTimeFormat
     * @return array<array<array<array<bool>>>>
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getMap(
        array $channelManagerConfigs,
        \DateTime $begin,
        \DateTime $end,
        string $dateTimeFormat = 'Y-m-d'
    ): array
    {
//        if ($this->data !== null && $this->beginDate === $begin && $this->endDate === $end) {
//            return $this->data;
//        }
//
//        $this->beginDate = $begin;
//        $this->endDate = $end;

        //TODO: save calculated data into $this->data if dates and config params are equal

        $idsInfoArray = $this->getIdsInfoArray($channelManagerConfigs);
        $dataCaches = $this->getPriceCachesArray($idsInfoArray, $begin, $end);
        $dataRest = $this->getRestrictionsArray($idsInfoArray, $begin, $end);
        $formatterHotelsData = $this->getHotelsData($channelManagerConfigs);

        return $this->mergeByPeriod(
            $this->format($dataCaches, true, $dateTimeFormat, $this->getTimeZone()),
            $this->format($dataRest, false, $dateTimeFormat, $this->getTimeZone()),
            $begin,
            $end,
            $formatterHotelsData,
            $dateTimeFormat
        );
    }

    /** @return string */
    protected function getTimeZone(): string
    {
        return $this->helper->getTimeZone();
    }

    /**
     * @param array $formattedPriceCaches
     * @param array $formattedRestrictions
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $formatterHotelsData
     * @param string $dateTimeFormat
     * @return array
     */
    protected function mergeByPeriod(
        array $formattedPriceCaches,
        array $formattedRestrictions,
        \DateTime $begin,
        \DateTime $end,
        array $formatterHotelsData,
        string $dateTimeFormat
    ): array
    {
        $period = new \DatePeriod(
            $begin,
            \DateInterval::createFromDateString('1 day'),
            (clone $end)->modify('+1 day')
        );

        foreach ($formatterHotelsData as $hotelId => $hotelsData) {
            foreach ($hotelsData['tariffs'] as $tariffId) {
                foreach ($hotelsData['roomTypes'] as $roomTypeId) {
                    /** @var \DateTime $day */
                    foreach ($period as $day) {
                        $isPriceCache = $formattedPriceCaches[$hotelId][$tariffId][$roomTypeId][$day->format($dateTimeFormat)] ?? false;
                        $isRestriction = $formattedRestrictions[$hotelId][$tariffId][$roomTypeId][$day->format($dateTimeFormat)] ?? true;

                        $result[$hotelId][$tariffId][$roomTypeId][$day->format($dateTimeFormat)] = !($isPriceCache && $isRestriction);
                    }
                }
            }
        }

        return $result ?? [];
    }

    /**
     * @param array $channelManagerConfigs
     * @return array
     */
    protected function getIdsInfoArray(array $channelManagerConfigs): array
    {
        $roomTypes = [];
        $hotels = [];
        $tariffs = [];

        /** @var ChannelManagerConfigInterface $config */
        foreach ($channelManagerConfigs as $config) {
            /** @var Room $room */
            foreach ($config->getRooms() as $room) {
                $roomTypes[] = $room->getRoomType()->getId();
            }
            /** @var Tariff $tariff */
            foreach ($config->getTariffs() as $tariff) {
                $tariffs[] = $tariff->getTariff()->getId();
            }
            $hotels[] = $config->getHotel()->getId();
        }

        return [
            'roomTypes' => $roomTypes,
            'hotels' => $hotels,
            'tariffs' => $tariffs
        ];
    }

    /**
     * @param array $channelManagerConfigs
     * @return array
     */
    protected function getHotelsData(array $channelManagerConfigs): array
    {
        $data = [];
        /** @var ChannelManagerConfigInterface $config */
        foreach ($channelManagerConfigs as $config) {
            /** @var Room $room */
            foreach ($config->getRooms() as $room) {
                $data[$config->getHotel()->getId()]['roomTypes'][] = $room->getRoomType()->getId();
            }
            /** @var Tariff $tariff */
            foreach ($config->getTariffs() as $tariff) {
                $data[$config->getHotel()->getId()]['tariffs'][] = $tariff->getTariff()->getId();
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function getPriceCachesArray(array $data, \DateTime $begin, \DateTime $end): array
    {
        $isDisableableChanged = false;
        if (!$this->dm->getFilterCollection()->isEnabled('disableable')) {
            $this->dm->getFilterCollection()->enable('disableable');
            $isDisableableChanged = !$isDisableableChanged;
        }

        $qb = $this->dm->getRepository(\MBH\Bundle\PriceBundle\Document\PriceCache::class)
            ->createQueryBuilder();

        $caches = $qb
            ->select('date', 'isEnabled', 'price', 'hotel.id', 'roomType.id', 'tariff.id')
            ->field('roomType.id')->in($data['roomTypes'])
            ->field('hotel.id')->in($data['hotels'])
            ->field('tariff.id')->in($data['tariffs'])
            ->field('date')->gte($begin)
            ->field('date')->lte($end)
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        if ($isDisableableChanged) {
            $this->dm->getFilterCollection()->disable('disableable');
        }

        return $caches;
    }

    /**
     * @param array $data
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function getRestrictionsArray(array $data, \DateTime $begin, \DateTime $end): array
    {
        $isDisableableChanged = false;
        if (!$this->dm->getFilterCollection()->isEnabled('disableable')) {
            $this->dm->getFilterCollection()->enable('disableable');
            $isDisableableChanged = !$isDisableableChanged;
        }

        $qb = $this->dm->getRepository(\MBH\Bundle\PriceBundle\Document\Restriction::class)
            ->createQueryBuilder();

        $restrictions = $qb
            ->select('date', 'hotel.id', 'roomType.id', 'tariff.id', 'closed', 'isEnabled')
            ->field('roomType.id')->in($data['roomTypes'])
            ->field('hotel.id')->in($data['hotels'])
            ->field('tariff.id')->in($data['tariffs'])
            ->field('date')->gte($begin)
            ->field('date')->lte($end)
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        if ($isDisableableChanged) {
            $this->dm->getFilterCollection()->disable('disableable');
        }

        return $restrictions;
    }

    /**
     * @param array $data
     * @param bool $isPriceCaches
     * @param string $dateTimeFormat
     * @param string $timeZone
     * @return array<array<array<array<bool>>>>
     */
    protected function format(array $data, bool $isPriceCaches, string $dateTimeFormat, string $timeZone): array
    {
        foreach ($data as $item) {
            $hotelId = (string)$item['hotel']['$id'];
            $tariffId = (string)$item['tariff']['$id'];
            $roomId = (string)$item['roomType']['$id'];
            $date = $item['date']->toDateTime()->setTimeZone(new \DateTimeZone($timeZone))->format($dateTimeFormat);

            $formatted[$hotelId][$tariffId][$roomId][$date] = $isPriceCaches
                ? (int)$item['price'] && true
                : !$item['closed'] && true;
        }

        return $formatted ?? [];
    }
}
