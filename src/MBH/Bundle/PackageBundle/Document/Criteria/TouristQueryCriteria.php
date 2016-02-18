<?php


namespace MBH\Bundle\PackageBundle\Document\Criteria;

/**
 * Class TouristQueryCriteria
 * @package MBH\Bundle\PackageBundle\Document\Criteria

 */
class TouristQueryCriteria
{
    const CITIZENSHIP_FOREIGN = 'foreign';
    const CITIZENSHIP_NATIVE = 'native';

    /**
     * @var \DateTime
     */
    public $begin;

    /**
     * @var \DateTime
     */
    public $end;

    /**
     * @var string
     */
    public $citizenship;

    /**
     * @var string
     */
    public $search;
}