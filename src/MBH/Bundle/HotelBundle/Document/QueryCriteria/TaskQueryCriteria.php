<?php

namespace MBH\Bundle\HotelBundle\Document\QueryCriteria;

use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class TaskQueryCriteria
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
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
     * Performer id
     * @var string
     */
    public $performer;

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
     * @var string[]
     */
    public $roles = [];

    /**
     * @var bool
     */
    public $deleted = false;

    /**
     * @var Hotel|null
     */
    public $hotel;
}