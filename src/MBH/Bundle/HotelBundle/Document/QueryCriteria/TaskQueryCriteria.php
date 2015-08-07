<?php

namespace MBH\Bundle\HotelBundle\Document\QueryCriteria;

use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;
/**
 * Class TaskQueryCriteria
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class TaskQueryCriteria extends AbstractQueryCriteria
{
    /**
     * @var array
     */
    public $sort = [];
    /**
     * @var int
     */
    public $offset = 0;
    /**
     * @var int
     */
    public $limit;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $priority;

    /**
     * @todo DateRange Object
     * @var \DateTime|null
     */
    public $begin;
    /**
     * @var \DateTime|null
     */
    public $end;
    /**
     * @var bool
     */
    public $onlyOwned;
    /**
     * @var string
     */
    public $performerId;
    /**
     * @var string[]
     */
    public $roles = [];
}