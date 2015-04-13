<?php

namespace MBH\Bundle\PriceBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @ODM\Document(collection="RoomCache", repositoryClass="MBH\Bundle\PriceBundle\Document\RoomCacheRepository")
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @MongoDBUnique(fields={"roomType", "date"}, message="RoomCache already exist.")
 */
class RoomCache extends Base
{
    /**
     * @var \MBH\Bundle\HotelBundle\Document\Hotel
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     * @Assert\NotNull()
     */
    protected $hotel;

    /**
     * @var \MBH\Bundle\HotelBundle\Document\RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull()
     */
    protected $roomType;

    /**
     * @var \DateTime
     * @ODM\Date()
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $date;

    /**
     * @var int
     * @ODM\Int()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $totalRooms;

    /**
     * @var int
     * @ODM\Int()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $packagesCount = 0;

    /**
     * @var int
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     */
    protected $leftRooms;

    /**
     * Set hotel
     *
     * @param \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     * @return self
     */
    public function setHotel(\MBH\Bundle\HotelBundle\Document\Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * Set roomType
     *
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @return self
     */
    public function setRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
    {
        $this->roomType = $roomType;
        return $this;
    }

    /**
     * Get roomType
     *
     * @return \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set totalRooms
     *
     * @param int $totalRooms
     * @return self
     */
    public function setTotalRooms($totalRooms)
    {
        $this->totalRooms = (int) $totalRooms;
        if ($this->totalRooms < 0) {
            $this->totalRooms = 0;
        }
        return $this;
    }

    /**
     * Get totalRooms
     *
     * @return int $totalRooms
     */
    public function getTotalRooms()
    {
        return $this->totalRooms;
    }

    /**
     * Set packagesCount
     *
     * @param int $packagesCount
     * @return self
     */
    public function setPackagesCount($packagesCount)
    {
        $this->packagesCount = (int) $packagesCount;
        if ($this->packagesCount < 0) {
            $this->packagesCount = 0;
        }
        return $this;
    }

    /**
     * Get packagesCount
     *
     * @return int $packagesCount
     */
    public function getPackagesCount()
    {
        return $this->packagesCount;
    }

    /**
     * Set leftRooms
     *
     * @param int $leftRooms
     * @return self
     */
    public function setLeftRooms($leftRooms)
    {
        $this->leftRooms = (int) $leftRooms;
        return $this;
    }

    /**
     * Get leftRooms
     *
     * @return int $leftRooms
     */
    public function getLeftRooms()
    {
        return $this->leftRooms;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->calcLeftRooms();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->calcLeftRooms();
    }

    /**
     * @return $this
     */
    public function calcLeftRooms()
    {
        $this->setLeftRooms($this->getTotalRooms() - $this->getPackagesCount());

        return $this;
    }
}
