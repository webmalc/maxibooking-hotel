<?php

namespace MBH\Bundle\PackageBundle\Document\Criteria;

use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Hotel;
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
     * @var array
     */
    private $roomTypes;
    /**
     * @var bool
     */
    public $isConfirmed;
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
    private $accommodations = [];
    private $hasAccommodations;
    private $sources;

    /**
     * @param string|\MongoId $roomTypeId
     * @return PackageQueryCriteria
     */
    public function addRoomType($roomTypeId)
    {
        $this->roomTypes[] = $roomTypeId;

        return $this;
    }

    /**
     * @param $accommodationId
     * @return PackageQueryCriteria
     */
    public function addAccommodation($accommodationId)
    {
        $this->accommodations[] = $accommodationId;

        return $this;
    }

    /**
     * @return array
     */
    public function getAccommodations()
    {
        return $this->accommodations;
    }

    public function getRoomTypeIds()
    {
        return $this->roomTypes;
    }

    public function setRoomTypes(array $roomTypes)
    {
        $this->roomTypes = $roomTypes;

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasAccommodations() : ?bool
    {
        return $this->hasAccommodations;
    }

    /**
     * @param boolean $hasAccommodations
     * @return PackageQueryCriteria
     */
    public function setHasAccommodations(bool $hasAccommodations) : PackageQueryCriteria
    {
        $this->hasAccommodations = $hasAccommodations;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * @param array $sources
     */
    public function setSources($sources)
    {
        $this->sources = $sources;
    }
}