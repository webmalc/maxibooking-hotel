<?php

namespace MBH\Bundle\ChannelManagerBundle\Model\Airbnb;

use MBH\Bundle\HotelBundle\Document\RoomType;

class PricePeriod
{
    /** @var RoomType  $roomType*/
    private $roomType;
    /** @var  float $price */
    private $price;
    /** @var  \DateTime $startDate */
    private $startDate;
    /** @var \DateTime $endDate */
    private $endDate;
    /**
     * @var  int $listingId
     * id airbnb listing
     */
    private $listingId;

    public function __construct(RoomType $roomType, $price, \DateTime $date, $listingId)
    {
        $this->roomType = $roomType;
        $this->price = $price;
        $this->startDate = $date;
        $this->endDate = clone $date;
        $this->listingId = $listingId;
    }

    public function increaseEndDate()
    {
        $this->endDate->add(new \DateInterval('P1D'));
    }

    /**
     * @return mixed
     */
    public function getListingId()
    {
        return $this->listingId;
    }

    /**
     * @return RoomType
     */
    public function getListing(): RoomType
    {
        return $this->roomType;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

}