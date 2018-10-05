<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class RoomCacheGeneratorQueue implements InvalidateQueryCreatorInterface
{
    public function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        ['begin' => $begin, 'end' => $end, 'roomTypeIds' => $roomTypeIds] = $data;
        $query
            ->setType(InvalidateQuery::ROOM_CACHE_GENERATOR)
            ->setBegin($begin)
            ->setEnd($end)
            ->setRoomTypeIds($roomTypeIds);

        return $query;
    }

}