<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\EmptyCachePeriod;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\Translation\TranslatorInterface;

class WarningsCompiler
{
    const CACHE_PERIOD_LENGTH_IN_DAYS = 1000;
    private $dm;
    private $translator;
    private $periodsCompiler;

    private $cachesForPeriod;

    public function __construct(DocumentManager $dm, TranslatorInterface $translator, PeriodsCompiler $periodsCompiler)
    {
        $this->dm = $dm;
        $this->translator = $translator;
        $this->periodsCompiler = $periodsCompiler;
    }

    /**
     * @param int $periodLengthInDays
     * @param string $className
     * @param string $comparedField
     * @param Hotel|null $hotel
     * @return array
     * @throws \Exception
     */
    public function getPeriodsWithEmptyCaches(
        int $periodLengthInDays,
        string $className,
        string $comparedField,
        Hotel $hotel = null
    ) {
        $cachesSortedByHotelRoomTypeAndTariff = $this->getCachesForPeriod($periodLengthInDays, $className);

        $periodBegin = new \DateTime('midnight');
        $periodsEnd = new \DateTime('midnight + ' . $periodLengthInDays . ' days');

        $periodsWithoutPrice = [];
        foreach ($cachesSortedByHotelRoomTypeAndTariff as $hotelId => $cachesByRoomTypeAndTariff) {
            if (!is_null($hotel) && $hotel->getId() !== $hotelId) {
                continue;
            }

            foreach ($cachesByRoomTypeAndTariff as $roomTypeId => $cachesByTariff) {
                foreach ($cachesByTariff as $tariffId => $caches) {
                    $cachePeriods = $this->periodsCompiler
                        ->getPeriodsByFieldNames($periodBegin, $periodsEnd, $caches, [$comparedField], 'd.m.Y', true);
                    foreach ($cachePeriods as $periodNumber => $cachePeriodData) {
                        if ((is_null($cachePeriodData['data']) || $cachePeriodData['data'][$comparedField] === 0)
                            && $periodNumber !== (count($cachePeriods) - 1)) {
                            if (!isset($periodsWithoutPrice[$hotelId][$roomTypeId][$tariffId])) {
                                $periodsWithoutPrice[$hotelId][$roomTypeId][$tariffId] = [];
                            }

                            $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
                            $tariff = $className === RoomCache::class ? null : $this->dm->find('MBHPriceBundle:Tariff', $tariffId);
                            if (!is_null($roomType) && !is_null($tariff)) {
                                $emptyPeriod = new EmptyCachePeriod($cachePeriodData['begin'], $cachePeriodData['end'], $roomType, $tariff);
                                $periodsWithoutPrice[$hotelId][$roomTypeId][$tariffId][] = $emptyPeriod;
                            }
                        }
                    }
                }
            }
        }

        return $periodsWithoutPrice;
    }

    /**
     * @param Hotel|null $hotel
     * @return array
     * @throws \Exception
     */
    public function getEmptyPriceCachePeriods(Hotel $hotel = null)
    {
        return $this->getPeriodsWithEmptyCaches(self::CACHE_PERIOD_LENGTH_IN_DAYS, PriceCache::class, 'price', $hotel);
    }

    /**
     * @param Hotel|null $hotel
     * @return array
     * @throws \Exception
     */
    public function getEmptyRoomCachePeriods(Hotel $hotel = null)
    {
        return $this->getPeriodsWithEmptyCaches(self::CACHE_PERIOD_LENGTH_IN_DAYS, RoomCache::class, 'totalRooms', $hotel);
    }

    /**
     * @param string $cacheClass
     * @param array|null $roomTypeIds
     * @param array|null $tariffIds
     * @return array
     */
    public function getLastCacheByRoomTypesAndTariffs(string $cacheClass, array $roomTypeIds = null, array $tariffIds = null)
    {
        $result = [];
        foreach ($this->getCachesForPeriod(self::CACHE_PERIOD_LENGTH_IN_DAYS, $cacheClass) as $cachesByRoomTypesAndTariffs) {
            foreach ($cachesByRoomTypesAndTariffs as $roomTypeId => $cachesByTariffs) {
                if (is_null($roomTypeIds) || in_array($roomTypeId, $roomTypeIds)) {
                    foreach ($cachesByTariffs as $tariffId => $caches) {
                        if ($cacheClass === RoomCache::class || is_null($tariffIds) || in_array($tariffId, $tariffIds)) {
                            $result[$roomTypeId][$tariffId] = end($caches);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param Hotel $hotel
     * @param string $cacheType
     * @return array
     * @throws \Exception
     */
    public function getEmptyCacheWarningsAsStrings(Hotel $hotel, string $cacheType)
    {
        $warningMessages = [];
        switch ($cacheType) {
            case 'price':
                $emptyPeriods = $this->getEmptyPriceCachePeriods();
                $warningMessageId = 'site_manager.empty_price_caches_warning';
                break;
            case 'room':
                $emptyPeriods = $this->getEmptyRoomCachePeriods();
                $warningMessageId = 'site_manager.empty_room_caches_warning';
                break;
            default:
                throw new \InvalidArgumentException('Incorrect type of cache: ' . $cacheType);
        }

        if (isset($emptyPeriods[$hotel->getId()])) {
            $hotelWarnings = $emptyPeriods[$hotel->getId()];

            $warningMessages = [];
            foreach ($hotelWarnings as $emptyPriceCacheWarningsByTariffs) {
                /** @var EmptyCachePeriod[] $emptyPeriodsForTariff */
                foreach ($emptyPriceCacheWarningsByTariffs as $emptyPeriodsForTariff) {
                    $periods = array_map(function (EmptyCachePeriod $period) {
                        return '"' . $period->getPeriodAsString() . '"';
                    }, $emptyPeriodsForTariff);

                    $firstPeriod = current($emptyPeriodsForTariff);
                    if ($firstPeriod) {
                        $warningMessages[] = $this->translator->trans(
                            $warningMessageId,
                            [
                                '%roomTypeName%' => $firstPeriod->getRoomType()->getName(),
                                '%tariffName%' => $firstPeriod->getTariff() ? $firstPeriod->getTariff()->getName() : '',
                                '%periods%' => join(', ', $periods)
                            ]
                        );
                    }
                }
            }
        }

        return $warningMessages;
    }

    /**
     * @param int $periodLengthInDays
     * @param string $className
     * @return mixed
     */
    public function getCachesForPeriod(int $periodLengthInDays, string $className)
    {
        if (!isset($this->cachesForPeriod[$className])) {
            $begin = new \DateTime('midnight');
            $end = new \DateTime('midnight +' . $periodLengthInDays . 'days');
            $this->cachesForPeriod[$className] = $this->dm
                ->getRepository($className)
                ->getRawByRoomTypesAndTariffs($begin, $end);
        }

        return $this->cachesForPeriod[$className];
    }
}