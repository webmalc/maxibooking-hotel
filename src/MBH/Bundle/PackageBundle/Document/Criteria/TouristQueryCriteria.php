<?php


namespace MBH\Bundle\PackageBundle\Document\Criteria;

/**
 * Class TouristQueryCriteria
 * @package MBH\Bundle\PackageBundle\Document\Criteria
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TouristQueryCriteria
{
    /**
     * @var \DateTime
     */
    public $begin;

    /**
     * @var \DateTime
     */
    public $end;

    /**
     * Foreign tourist only
     * @var bool
     */
    public $foreign = false;

    /**
     * @var string
     */
    public $search;
}