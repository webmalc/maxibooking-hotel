<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes\Traits\SharedGeneratorInvalidateQuery;

class PriceCacheGeneratorQueue implements InvalidateQueryCreatorInterface
{
    protected const TYPE = InvalidateQuery::PRICE_GENERATOR;

    use SharedGeneratorInvalidateQuery;
}