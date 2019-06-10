<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;

class AsyncDataFetcherException extends SearchException
{
    public const TYPE = ErrorResultFilter::ASYNC_DATA_FETCHER;
}