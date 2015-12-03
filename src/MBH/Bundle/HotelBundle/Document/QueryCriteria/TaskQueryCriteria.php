<?php

namespace MBH\Bundle\HotelBundle\Document\QueryCriteria;

use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\Group;
use MBH\Bundle\UserBundle\Document\User;

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
     * @var User
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
     * @var Group[]
     */
    public $userGroups = [];

    /**
     * @var bool
     */
    public $deleted = false;

    /**
     * @var Hotel|null
     */
    public $hotel;
}