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
    private $dm;
    private $translator;
    private $periodsCompiler;

    private $emptyPriceCachePeriods;
    private $isEmptyPriceCachePeriodsInit = false;
    private $emptyRoomCachePeriods;
    private $isEmptyRoomCachePeriodsInit = false;

    public function __construct(DocumentManager $dm, TranslatorInterface $translator, PeriodsCompiler $periodsCompiler) {
        $this->dm = $dm;
        $this->translator = $translator;
        $this->periodsCompiler = $periodsCompiler;
    }

    /**
     * @param int $periodLengthInDays
     * @param string $className
     * @param string $comparedField
     * @return array
     * @throws \Exception
     */
    public function getPeriodsWithEmptyCaches(int $periodLengthInDays, string $className, string $comparedField)
    {
        $cachesSortedByHotelRoomTypeAndTariff = $this->dm
            ->getRepository($className)
            ->findForDashboard($periodLengthInDays);

        $periodBegin = new \DateTime('midnight');
        $periodsEnd = new \DateTime('midnight + ' . $periodLengthInDays . ' days');

        $periodsWithoutPrice = [];
        foreach ($cachesSortedByHotelRoomTypeAndTariff as $hotelId => $cachesByRoomTypeAndTariff) {
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

                            $periodsWithoutPrice[$hotelId][$roomTypeId][$tariffId][] =
                                new EmptyCachePeriod($cachePeriodData['begin'], $cachePeriodData['end'], $roomType, $tariff);
                        }
                    }
                }
            }
        }

        return $periodsWithoutPrice;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getEmptyPriceCachePeriods()
    {
        if (!$this->isEmptyPriceCachePeriodsInit) {
            $this->emptyPriceCachePeriods
                = $this->getPeriodsWithEmptyCaches(360, PriceCache::class, 'price');
            $this->isEmptyPriceCachePeriodsInit = true;
        }

        return $this->emptyPriceCachePeriods;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getEmptyRoomCachePeriods()
    {
        if (!$this->isEmptyRoomCachePeriodsInit) {
            $this->emptyRoomCachePeriods =
                $this->getPeriodsWithEmptyCaches(360, RoomCache::class, 'totalRooms');
            $this->isEmptyRoomCachePeriodsInit = true;
        }

        return $this->emptyRoomCachePeriods;
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
                    $periods = array_map(function(EmptyCachePeriod $period) {
                        return '"' . $period->getPeriodAsString() . '"';
                    }, $emptyPeriodsForTariff);

                    $firstPeriod = current($emptyPeriodsForTariff);
                    $warningMessages[] = $this->translator->trans($warningMessageId, [
                        '%roomTypeName%' => $firstPeriod->getRoomType()->getName(),
                        '%tariffName%' => $firstPeriod->getTariff() ? $firstPeriod->getTariff()->getName() : '',
                        '%periods%' => join(', ', $periods)
                    ]);
                }
            }
        }

        return $warningMessages;
    }
}