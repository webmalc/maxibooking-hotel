<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class Unwelcome
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class Unwelcome implements \JsonSerializable
{
    /**
     * @var bool
     */
    protected $isAggressor;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var bool
     */
    protected $isMy;

    /**
     * @var Hotel
     */
    protected $hotel;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $arrivalTime;

    /**
     * @var \DateTime
     */
    protected $departureTime;

    /**
     * @return boolean
     */
    public function getIsAggressor()
    {
        return $this->isAggressor;
    }

    /**
     * @param boolean $aggressor
     * @return self
     */
    public function setIsAggressor($aggressor)
    {
        $this->isAggressor = $aggressor;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsMy()
    {
        return $this->isMy;
    }

    /**
     * @param boolean $isMy
     * @return self
     */
    public function setIsMy($isMy)
    {
        $this->isMy = $isMy;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     */
    public function setHotel(Hotel $hotel = null)
    {
        $this->hotel = $hotel;
    }

    /**
     * @param \DateTime $arrivalTime
     * @return self
     */
    public function setArrivalTime(\DateTime $arrivalTime = null)
    {
        $this->arrivalTime = $arrivalTime;
        return $this;
    }

    /**
     * @return \DateTime $arrivalTime
     */
    public function getArrivalTime()
    {
        return $this->arrivalTime;
    }

    /**
     * @param \DateTime $departureTime
     * @return self
     */
    public function setDepartureTime(\DateTime $departureTime = null)
    {
        $this->departureTime = $departureTime;
        return $this;
    }

    /**
     * @return \DateTime $departureTime
     */
    public function getDepartureTime()
    {
        return $this->departureTime;
    }

    public function jsonSerialize()
    {
        return [
            'comment' => $this->getComment(),
            'isAggressor' => $this->getIsAggressor(),
            'arrivalTime' => $this->getArrivalTime() ? $this->getArrivalTime()->format('d.m.Y') : null,
            'departureTime' => $this->getDepartureTime() ? $this->getDepartureTime()->format('d.m.Y') : null,
        ];
    }
}