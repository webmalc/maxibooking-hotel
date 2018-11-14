<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;
use Throwable;

class SearchException extends \Exception
{
    public const TYPE = ErrorResultFilter::DISABLE;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


    public function getType(): int
    {
        return static::TYPE;
    }

}