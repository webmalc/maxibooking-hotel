<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Room", repositoryClass="MBH\Bundle\HotelBundle\Document\RoomRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle", "roomType"}, message="Такой номер уже существует")
 * @ODM\Index(name="search_enabled_roomType", keys={"isEnabled"="asc","roomType"="asc"})
 * @ODM\Index(name="search_deletedAt", keys={"deletedAt"="asc"})
 * @ODM\Index(name="search_enabled_deletedAt", keys={"isEnabled"="asc","deletedAt"="asc"})
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
     * @ODM\Index()
     */
    protected $hotel;
    
    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull(message="validator.document.room.room_type_not_selected")
     * @ODM\Index()
     */
    protected $roomType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=1,
     *      minMessage="validator.document.room.min_name",
     *      max=100,
     *      maxMessage="validator.document.room.max_name"
     * )
     * @ODM\Index()
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=1,
     *      minMessage="validator.document.room.min_name",
     *      max=100,
     *      maxMessage="validator.document.room.max_name"
     * )
     * @ODM\Index()
     */
    protected $title;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Housing")
     * @ODM\Index()
     */
    protected $housing;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="floor")
     * @Assert\Length(
     *      min=1,
     *      minMessage="validator.document.room.min_floor",
     *      max=10,
     *      maxMessage="validator.document.room.max_floor"
     * )
     * @ODM\Index()
     */
    protected $floor;

    /**
     * @var RoomStatus
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\RoomStatus")
     */
    protected $status;

    /**
     * @var array
     * @ODM\Collection()
     */
    protected $facilities;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isSmoking = false;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\RoomViewType")
     */
    protected $roomViewsTypes;

    /**
     * Room constructor.
     */
    public function __construct()
    {
        $this->status = new ArrayCollection();
        $this->roomViewsTypes = new ArrayCollection();
    }

    /**
     * Set hotel
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
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
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
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
     * Get roomType
     *
     * @return \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function getRoomType()
    {
        return $this->roomType;
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
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

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
     * Get housing
     *
     * @return Housing $housing
     */
    public function getHousing()
    {
        return $this->housing;
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
     * Get floor
     *
     * @return string $floor
     */
    public function getFloor()
    {
        return $this->floor;
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
     * @return RoomStatus|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param RoomStatus $status
     * @return $this
     */
    public function addStatus(RoomStatus $status)
    {
        $this->status->add($status);

        return $this;
    }

//    public function setStatus(RoomStatus $status)
//    {
//        $this->status->set
//    }

    public function removeStatus(RoomStatus $status)
    {
        $this->status->removeElement($status);

        return $this;
    }

    /**
     * @return array
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * @return array
     */
    public function getAllFacilities()
    {
        return count($this->getFacilities()) > 0 ? $this->getFacilities() :
            ($this->getRoomType() ? $this->getRoomType()->getFacilities() : []);
    }

    /**
     * @param array $facilities
     */
    public function setFacilities($facilities)
    {
        $this->facilities = $facilities;
    }

    /**
     * @return bool
     */
    public function getIsSmoking(): ?bool
    {
        return $this->isSmoking;
    }

    /**
     * @param bool $isSmoking
     * @return Room
     */
    public function setIsSmoking(bool $isSmoking): Room
    {
        $this->isSmoking = $isSmoking;

        return $this;
    }

    public function getRoomViewsTypes()
    {
        return $this->roomViewsTypes;
    }

    /**
     * @param RoomViewType $roomViewType
     * @return Room
     */
    public function removeRoomViewType(RoomViewType $roomViewType): Room
    {
        $this->roomViewsTypes->remove($roomViewType);

        return $this;
    }

    /**
     * @param RoomViewType $roomViewType
     * @return Room
     */
    public function addRoomViewType(RoomViewType $roomViewType): Room
    {
        $this->roomViewsTypes->add($roomViewType);

        return $this;
    }
}
