<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;

class SearchLimitCheckerException extends SearchException
{
    public const TYPE = ErrorResultFilter::ROOM_CACHE;
}