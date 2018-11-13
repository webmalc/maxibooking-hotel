<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;

class WindowsCheckLimitException extends SearchException
{
    public const TYPE = ErrorResultFilter::WINDOWS;
}