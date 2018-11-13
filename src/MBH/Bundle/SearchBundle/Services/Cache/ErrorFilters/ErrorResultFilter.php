<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters;


use MBH\Bundle\SearchBundle\Lib\Exceptions\FilterResultException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;

class ErrorResultFilter implements ErrorResultFilterInterface
{

    public const WINDOWS = 1;

    public const ROOM_CACHE = 2;

    public const PRICE_CACHE = 4;

    public const TARIFF_LIMIT = 8;

    public const RESTRICTION = 16;

    public const ROOM_POPULATION = 32;

    public const ALL = self::WINDOWS | self:: ROOM_CACHE | self::PRICE_CACHE | self::RESTRICTION | self::TARIFF_LIMIT | self::ROOM_POPULATION;

    /**
     * @param Result $result
     * @param int $level
     * @throws FilterResultException
     */
    public function filter(Result $result, int $level): void
    {
        if ($result->getStatus() === 'error') {
            $errorType = $result->getErrorType();
            $this->actualFilter($level, $errorType);
        }
    }

    /**
     * @param array $result
     * @param int $level
     * @throws FilterResultException
     */
    public function arrayFilter(array $result, int $level): void
    {
        if ($result['status'] === 'error') {
            $errorType = $result['errorType'];
            $this->actualFilter($level, $errorType);
        }
    }

    /**
     * @param int $level
     * @param int $errorType
     * @throws FilterResultException
     */
    private function actualFilter(int $level, int $errorType): void
    {
        if (!($level & $errorType)) {
            throw new FilterResultException('No debug this error');
        }
    }

}