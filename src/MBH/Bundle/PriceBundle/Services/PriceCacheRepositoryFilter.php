<?php

namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\PriceCache;

/**
 * Class PriceCacheRepositoryFilter
 * @package MBH\Bundle\PriceBundle\Services
 */
class PriceCacheRepositoryFilter
{

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var array
     */
    private $roomTypeMap;

    /**
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;

        $this->roomTypeMap = $this->getRoomTypeMap();
    }


    /**
     * @param PriceCache $cache
     * @return PriceCache|null
     */
    public function filterGetWithMinPrice(PriceCache $cache)// getWithMinPrice
    {
        return $this->filterPriceCache($cache, $this->roomTypeMap);
    }


    /**
     * @param array $result
     * @return array
     */
    public function filterFetch(array $result) //fetch, fetchWithCancelDate
    {
        foreach ($result as $roomTypeId => $roomTypeArr) {
            foreach ($roomTypeArr as $tariffId => $tariffArr) {
                foreach ($tariffArr as $date => $cache) {
                    $result[$roomTypeId][$tariffId][$date] = $this->filterPriceCache($cache, $this->roomTypeMap);
                }
            }
        }

        return $result;
    }

    /**
     * @param PriceCache|null $cache
     * @param array $roomTypeMap
     * @return PriceCache|null
     */
    private function filterPriceCache(PriceCache $cache, array $roomTypeMap)
    {
        if (($cache == null) || ($roomTypeMap == [])) {
            return $cache;
        }

        if (!$roomTypeMap[$cache->getRoomType()->getId()]['isIndividualAdditionalPrices']) {
            $cache->setAdditionalPrices([]);
        }
        if (!$roomTypeMap[$cache->getRoomType()->getId()]['isSinglePlacement']) {
            $cache->setSinglePrice(null);
        }
        if (!$roomTypeMap[$cache->getRoomType()->getId()]['isChildPrices']) {
            $cache->setChildPrice(null);
        }

        return $cache;
    }

    /**
     * @return array
     */
    private function getRoomTypeMap(): array
    {
        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findAll();

        $roomTypeMap = [];

        foreach ($roomTypes as $roomType) {
            $roomTypeMap[$roomType->getId()] = [
                'isIndividualAdditionalPrices' => $roomType->getIsIndividualAdditionalPrices(),
                'isSinglePlacement' => $roomType->getIsSinglePlacement(),
                'isChildPrices' => $roomType->getIsChildPrices(),
            ];
        }

        return $roomTypeMap;
    }
}