<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;

class PriceCachesMergerException extends SearchException
{
    public const TYPE = ErrorResultFilter::PRICE_CACHE;
}