<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Room", repositoryClass="MBH\Bundle\HotelBundle\Document\RoomRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle", "roomType"}, message="Такой номер уже существует")
 */
class Room extends Base
{

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;

    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Hotel", inversedBy="rooms")
     * @Assert\NotNull(message="validator.document.room.hotel_not_selected")
     */
    protected $hotel;
    
    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="RoomType", inversedBy="rooms")
     * @Assert\NotNull(message="validator.document.room.room_type_not_selected")
     */
    protected $roomType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=1,
     *      minMessage="validator.document.room.min_name",
     *      max=100,
     *      maxMessage="validator.document.room.max_name"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=1,
     *      minMessage="validator.document.room.min_name",
     *      max=100,
     *      maxMessage="validator.document.room.max_name"
     * )
     */
    protected $title;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Housing")
     */
    protected $housing;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="floor")
     * @Assert\Length(
     *      min=1,
     *      minMessage="validator.document.room.min_floor",
     *      max=10,
     *      maxMessage="validator.document.room.max_floor"
     * )
     */
    protected $floor;

    /**
     * @var RoomStatus
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomStatus")
     */
    protected $status;

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
     * Set fullTitle
     *
     * @param string $fullTitle
     * @return self
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;
        return $this;
    }

    /**
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param boolean $hotel
     * @param boolean $roomType
     * @return string
     *
     */
    public function getName($roomType = false, $hotel = false)
    {
        (!empty($this->title)) ?  $name = $this->title : $name = $this->fullTitle;

        if ($roomType) {
            $name = $this->getRoomType()->getName() . ' - ' . $name;
        }
        if ($hotel) {
            $name = $this->getHotel()->getName() . ' - ' . $name;
        }

        return $name;
    }

    /**
     * Set housing
     *
     * @param Housing $housing
     * @return self
     */
    public function setHousing(Housing $housing = null)
    {
        $this->housing = $housing;
        return $this;
    }

    /**
     * Get housing
     *
     * @return Housing $housing
     */
    public function getHousing()
    {
        return $this->housing;
    }

    /**
     * Set floor
     *
     * @param string $floor
     * @return self
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;
        return $this;
    }

    /**
     * Get floor
     *
     * @return string $floor
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @return RoomStatus|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param RoomStatus $status
     */
    public function setStatus($status = null)
    {
        $this->status = $status;
    }
}
