<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\QueueTypes;


use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\Traits\SharedGeneratorInvalidateQuery;

class RestrictionGeneratorQueue implements InvalidateQueryCreatorInterface
{
    protected const TYPE = InvalidateQuery::RESTRICTION_GENERATOR;

    use SharedGeneratorInvalidateQuery;
}