<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue;


use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class PriceCacheGeneratorQueue extends AbstractInvalidateQueueCreator
{

    protected function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        $query
            ->setType(InvalidateQuery::PRICE_GENERATOR)
            ->setBegin($data['begin'])
            ->setEnd($data['end'])
            ->setRoomTypeIds($data['roomTypeIds'])
            ->setTariffIds($data['tariffIds'])
        ;

        return $query;
    }

}