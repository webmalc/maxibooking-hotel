<?php

namespace MBH\Bundle\ChannelManagerBundle\Model\Airbnb;

use MBH\Bundle\HotelBundle\Document\RoomType;

class PricePeriod
{
    /** @var RoomType  $listing*/
    private $listing;
    /** @var  float $price */
    private $price;
    /** @var  \DateTime $startDate */
    private $startDate;
    /** @var \DateTime $endDate */
    private $endDate;

    public function __construct(RoomType $listing, $price, \DateTime $startDate, \DateTime $endDate)
    {
        $this->listing = $listing;
        $this->price = $price;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function increaseEndDate()
    {
        $this->endDate->add(new \DateInterval('P1D'));
    }

    /**
     * @return RoomType
     */
    public function getListing(): RoomType {
        return $this->listing;
    }

    /**
     * @return float
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime {
        return $this->startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime {
        return $this->endDate;
    }

}