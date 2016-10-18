<?php

namespace MBH\Bundle\ChannelManagerBundle\Model\Airbnb;


use MBH\Bundle\HotelBundle\Document\RoomType;

class ClosedPeriod
{

    /** @var RoomType  $listing*/
    private $listing;
    /** @var  bool $availability */
    private $availability;
    /** @var  \DateTime $startDate */
    private $startDate;
    /** @var \DateTime $endDate */
    private $endDate;

    public function __construct(RoomType $listing, $availability, \DateTime $startDate, \DateTime $endDate)
    {
        $this->listing = $listing;
        $this->availability = $availability;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return boolean
     */
    public function isAvailable() {
        return $this->availability;
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