<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use Throwable;

class SearchException extends \Exception
{
    public const TYPE = 0;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


    public function getType(): int
    {
        return static::TYPE;
    }

}