<?php

namespace MBH\Bundle\HotelBundle\Document\QueryCriteria;

/**
 * Class TaskQueryCriteria
 * @package MBH\Bundle\HotelBundle\Document\QueryCriteria
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class TaskQueryCriteria extends \MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria
{
    public $sort = [];

    public $offset = 0;

    public $limit;

    public $status;

    /**
     * @todo DateRange Object
     * @var \DateTime|null
     */
    public $begin;

    /**
     * @var \DateTime|null
     */
    public $end;
}