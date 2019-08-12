<?php

namespace MBH\Bundle\PackageBundle\Document\Criteria;

use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;

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
    public $packageOrders = [];
    /**
     * @var array
     */
    private $roomTypes = [];
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
    private $accommodations = [];
    private $isWithoutAccommodation = false;
    private $sources;

    /**
     * @param $roomTypeCriteria
     * @return PackageQueryCriteria
     */
    public function addRoomTypeCriteria($roomTypeCriteria)
    {
        if ($roomTypeCriteria instanceof RoomType)
        {
            $this->roomTypes[] = $roomTypeCriteria->getId();
        } else {
            $this->roomTypes[] = $roomTypeCriteria;
        }

        return $this;
    }

    /**
     * @param $accommodation
     * @return PackageQueryCriteria
     */
    public function addAccommodation($accommodation)
    {
        if ($accommodation instanceof PackageAccommodation) {
            $this->accommodations[] = $accommodation->getAccommodation()->getId();
        } elseif (is_string($accommodation) || $accommodation instanceof \MongoId) {
            $this->accommodations[] = $accommodation;
        } else {
            throw new \InvalidArgumentException('Passed accommodation argument of invalid type');
        }

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

    /**
     * @return boolean
     */
    public function isWithoutAccommodation() : bool
    {
        return $this->isWithoutAccommodation;
    }

    /**
     * @param boolean $isWithoutAccommodation
     * @return PackageQueryCriteria
     */
    public function setIsWithoutAccommodation(bool $isWithoutAccommodation) : PackageQueryCriteria
    {
        $this->isWithoutAccommodation = $isWithoutAccommodation;

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