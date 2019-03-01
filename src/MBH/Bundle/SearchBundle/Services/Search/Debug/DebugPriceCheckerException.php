<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Debug;


use Throwable;

/**
 * Class DebugPriceCheckerException
 * @package MBH\Bundle\SearchBundle\Services\Search\Debug
 */
class DebugPriceCheckerException extends \Exception
{

    /**
     * @var int
     */
    private $legacyPrice;

    /**
     * DebugPriceCheckerException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param int|null $legacyPrice
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, int $legacyPrice = null)
    {
        $this->legacyPrice = $legacyPrice;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getLegacyPrice(): int
    {
        return $this->legacyPrice;
    }

}