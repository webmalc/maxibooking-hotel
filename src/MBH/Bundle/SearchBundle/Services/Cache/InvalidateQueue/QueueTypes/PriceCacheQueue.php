<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class PriceCacheQueue implements InvalidateQueryCreatorInterface
{

    /**
     * @param PriceCache $data
     * @return InvalidateQuery
     */
    public function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        $query
            ->setObject($data)
            ->setType(InvalidateQuery::PRICE_CACHE)
        ;

        return $query;
    }

}