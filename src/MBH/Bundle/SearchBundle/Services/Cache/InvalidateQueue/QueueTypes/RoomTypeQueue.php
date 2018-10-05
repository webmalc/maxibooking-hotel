<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

class RoomTypeQueue implements InvalidateQueryCreatorInterface
{
    /** @var RoomType $data
     * @return InvalidateQuery
     */
    public function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        $query
            ->setObject($data)
            ->setType(InvalidateQuery::ROOM_TYPE);

        return $query;
    }

}