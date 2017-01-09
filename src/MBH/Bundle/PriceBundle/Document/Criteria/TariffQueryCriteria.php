<?php


namespace MBH\Bundle\PriceBundle\Document\Criteria;

/**
 * Class TariffQueryCriteria
 * @package MBH\Bundle\PriceBundle\Document\Criteria

 */
class TariffQueryCriteria
{
    const ON = true;
    const OFF = false;

    /**
     * @var \DateTime
     */
    public $begin;

    /**
     * @var \DateTime
     */
    public $end;

    /**
     * @var boolean
     */
    public $isOnline;

    /**
     * @var boolean
     */
    public $isEnabled;

    /**
     * @var string
     */
    public $search;
}