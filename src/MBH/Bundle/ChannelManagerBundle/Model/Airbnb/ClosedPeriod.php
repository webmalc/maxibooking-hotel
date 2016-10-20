<?php

namespace MBH\Bundle\ChannelManagerBundle\Model\Airbnb;

use MBH\Bundle\HotelBundle\Document\RoomType;

class ClosedPeriod
{
    /** @var RoomType  $roomType*/
    private $roomType;
    /** @var  bool $availability */
    private $isClosed;
    /** @var  \DateTime $startDate */
    private $startDate;
    /** @var \DateTime $endDate */
    private $endDate;
    /**
     * id airbnb listing
     * @var int $listingId
     */
    private $listingId;

    public function __construct(RoomType $roomType, $isClosed, \DateTime $date, $listingId)
    {
        $this->roomType = $roomType;
        $this->isClosed = $isClosed;
        $this->startDate = $date;
        $this->endDate = clone $date;
        $this->listingId = $listingId;
    }

    /**
     * @return boolean
     */
    public function getIsClosed() {
        return $this->isClosed;
    }

    public function increaseEndDate()
    {
        $this->endDate->add(new \DateInterval('P1D'));
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): RoomType
    {
        return $this->roomType;
    }

    /**
     * @return int
     */
    public function getListingId(): int
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