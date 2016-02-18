<?php

namespace MBH\Bundle\PackageBundle\Document\Criteria;

use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;

/**
 * Class PackageQueryCriteria

 */
class PackageQueryCriteria extends AbstractQueryCriteria
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
     * @var string
     */
    public $query;
    /**
     * @var \DateTime
     */
    public $liveBegin;
    /**
     * @var string
     */
    public $createdBy;
    /**
     * @var \DateTime
     */
    public $liveEnd;
    /**
     * @var string
     */
    public $filter;
    /**
     * @var bool|null
     */
    public $checkIn;
    /**
     * @var bool|null
     */
    public $checkOut;
    /**
     * @var string
     */
    public $order;
    /**
     * @var Hotel
     */
    public $hotel;
    /**
     * @var Order
     */
    public $packageOrder;
    /**
     * @var array
     */
    public $packageOrders;
    /**
     * @var RoomType
     */
    public $roomType;
    /**
     * @var bool
     */
    public $confirmed;
    /**
     * 'paid', 'part', 'not_paid'
     * @var string
     */
    public $paid;
    /**
     * @var string
     */
    public $status;
    /**
     * @var array
     */
    public $sort = ['createdAt' => 'desc'];
    /**
     * @var int
     */
    public $skip;
    /**
     * @var int
     */
    public $limit;
    /**
     * @var string
     */
    public $dateFilterBy = 'begin';
    /**
     * With deleted documents
     * @var bool
     */
    public $deleted = false;
}