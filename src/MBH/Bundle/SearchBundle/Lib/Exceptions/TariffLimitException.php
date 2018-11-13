<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;

class TariffLimitException extends SearchException
{
    public const TYPE = ErrorResultFilter::TARIFF_LIMIT;
}