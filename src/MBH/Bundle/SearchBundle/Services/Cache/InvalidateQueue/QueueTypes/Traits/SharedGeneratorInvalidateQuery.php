<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\Traits;


use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

trait SharedGeneratorInvalidateQuery
{
    public function createInvalidateQuery($data): InvalidateQuery
    {
        $query = new InvalidateQuery();
        ['begin' => $begin, 'end' => $end, 'roomTypeIds' => $roomTypeIds, 'tariffIds' => $tariffIds] = $data;
        $query
            ->setType(static::TYPE)
            ->setBegin($begin)
            ->setEnd($end)
            ->setRoomTypeIds($roomTypeIds)
            ->setTariffIds($tariffIds)
        ;

        return $query;
    }
}