<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;

interface InvalidateQueryCreatorInterface
{
    public function createInvalidateQuery($data): InvalidateQuery;
}