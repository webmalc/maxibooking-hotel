<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters;


use MBH\Bundle\SearchBundle\Lib\Exceptions\FilterResultException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;

interface ErrorResultFilterInterface
{
    /**
     * @throws FilterResultException
     * @param Result $result
     * @param int $level
     */
    public function filter(Result $result, int $level): void;
}