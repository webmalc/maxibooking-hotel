<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class RoomCacheQueue implements InvalidateQueryCreatorInterface
{
    /**
     * @param RoomCache $data
     * @return InvalidateQuery
     */
    public function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        $query
            ->setObject($data)
            ->setType(InvalidateQuery::ROOM_CACHE)
        ;

        return $query;
    }

}