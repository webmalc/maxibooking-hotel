<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;

class RestrictionLimitException extends SearchException
{
    public const TYPE = ErrorResultFilter::RESTRICTION;
}