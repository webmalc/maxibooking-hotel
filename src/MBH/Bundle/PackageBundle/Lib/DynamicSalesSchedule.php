<?php

namespace MBH\Bundle\PackageBundle\Lib;

/**
 * Class DynamicSalesSchedule
 * @package MBH\Bundle\PackageBundle\Lib
 */
class DynamicSalesSchedule
{
    /**
     *  @var \DateTime
     */
    protected $day;

    /**
     * @var
     */
    protected $amount = 0;

    /**
     * @return mixed
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param mixed $day
     * @return DynamicSalesSchedule
     */
    public function setDay($day)
    {
        $this->day = $day;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     * @return DynamicSalesSchedule
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
}