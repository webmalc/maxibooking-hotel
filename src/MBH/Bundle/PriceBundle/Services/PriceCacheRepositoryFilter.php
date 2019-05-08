<?php

namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
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
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * decorates src/MBH/Bundle/PriceBundle/Document/PriceCacheRepository/getWithMinPrice()
     *
     * @param PriceCache|null $cache
     * @return PriceCache|null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function filterGetWithMinPrice(?PriceCache $cache)
    {
        return $this->filterPriceCache($cache, $this->getRoomTypeMap());
    }

    /**
     * decorates src/MBH/Bundle/PriceBundle/Document/PriceCacheRepository/fetch()
     * decorates src/MBH/Bundle/PriceBundle/Document/PriceCacheRepository/fetchWithCancelDate()
     *
     * @param array|null $result
     * @return array|null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function filterFetch(?array $result)
    {
        foreach ($result as $roomTypeId => $roomTypeArr) {
            foreach ($roomTypeArr as $tariffId => $tariffArr) {
                foreach ($tariffArr as $date => $cache) {
                    $result[$roomTypeId][$tariffId][$date] = $this->filterPriceCache($cache, $this->getRoomTypeMap());
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
    private function filterPriceCache(?PriceCache $cache, array $roomTypeMap)
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getRoomTypeMap(): array
    {
        $isSoftDeletable = true;
        $isDisableable = true;

        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
            $isSoftDeletable = !$isSoftDeletable;
        }
        if ($this->dm->getFilterCollection()->isEnabled('disableable')) {
            $this->dm->getFilterCollection()->disable('disableable');
            $isDisableable = !$isDisableable;
        }

        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder()
            ->select(['_id', 'isIndividualAdditionalPrices', 'isSinglePlacement', 'isChildPrices'])
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        if (!$isSoftDeletable) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }
        if (!$isDisableable) {
            $this->dm->getFilterCollection()->enable('disableable');
        }

        $roomTypeMap = [];

        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $roomTypeMap[(string)$roomType['_id']] = [
                'isIndividualAdditionalPrices' => $roomType['isIndividualAdditionalPrices'],
                'isSinglePlacement' => $roomType['isSinglePlacement'] ?? false,
                'isChildPrices' => $roomType['isChildPrices'],
            ];
        }

        return $roomTypeMap;
    }
}
