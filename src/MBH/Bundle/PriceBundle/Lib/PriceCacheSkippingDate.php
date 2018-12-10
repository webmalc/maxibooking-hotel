<?php
/**
 * Created by PhpStorm.
 * Date: 11.10.18
 */

namespace MBH\Bundle\PriceBundle\Lib;


class PriceCacheSkippingDate
{
    public const REASON_SAME = 'same';
    public const REASON_WEEKDAYS = 'weekdays';
    public const REASON_ERROR = 'error';

    /**
     * @var string
     */
    private $reasons;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * ReasonForSkippingHolder constructor.
     * @param string $reasons
     * @param \DateTime $date
     */
    public function __construct(string $reasons, \DateTime $date)
    {
        $this->reasons = $reasons;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getReasons(): string
    {
        return $this->reasons;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }
}