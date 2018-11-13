<?php


namespace MBH\Bundle\SearchBundle\Lib\Exceptions;


use Throwable;

class SearchException extends \Exception
{
    /** @var string */
    protected $type;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, string $type = '')
    {
        $this->type = $type;
        parent::__construct($message, $code, $previous);
    }


}