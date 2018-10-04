<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue;


use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class PriceCacheQueue extends AbstractInvalidateQueueCreator
{

    /**
     * @param PriceCache $data
     * @return InvalidateQuery
     */
    protected function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        $query
            ->setObject($data)
            ->setType(InvalidateQuery::PRICE_CACHE)
        ;

        return $query;
    }

}