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
 * @ODM\Document(collection="Hotels")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="fullTitle", message="Такой отель уже существует")
 */
class Hotel extends Base
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
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="prefix")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткий префикс",
     *      max=100,
     *      maxMessage="Слишком длинный префикс"
     * )
     */
    protected $prefix;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Collection(name="food")
     * @Assert\Type(type="array")
     */
    protected $food = array();

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="saleDays")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Количество дней продажи не может быть меньше нуля"
     * )
     */
    protected $saleDays = 0;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isDefault")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isDefault = false;
    
    /** @ODM\ReferenceMany(targetDocument="RoomType", mappedBy="hotel") */
    protected $roomTypes;
    
    /** @ODM\ReferenceMany(targetDocument="Room", mappedBy="hotel") */
    protected $rooms;
    
    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff", mappedBy="hotel") */
    protected $tariffs;
    
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
     * Set prefix
     *
     * @param string $prefix
     * @return self
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Get prefix
     *
     * @return string $prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set food
     *
     * @param collection $food
     * @return self
     */
    public function setFood(array $food)
    {
        $this->food = $food;
        return $this;
    }

    /**
     * Get food
     *
     * @return collection $food
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * Set saleDays
     *
     * @param collection $saleDays
     * @return self
     */
    public function setSaleDays($saleDays)
    {
        $this->saleDays = (int) $saleDays;
        
        if($this->saleDays < 0) {
            $this->saleDays = 0;
        }
        
        return $this;
    }

    /**
     * Get saleDays
     *
     * @return collection $saleDays
     */
    public function getSaleDays()
    {
        return $this->saleDays;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        if (!empty($this->title)) {
            return $this->title;
        }
        return $this->fullTitle;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return self
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function __construct()
    {
        $this->roomTypes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tariffs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add roomType
     *
     * @param MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function addRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
    {
        $this->roomTypes[] = $roomType;
    }

    /**
     * Remove roomType
     *
     * @param MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function removeRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
    {
        $this->roomTypes->removeElement($roomType);
    }

    /**
     * Get roomTypes
     *
     * @return Doctrine\Common\Collections\Collection $roomTypes
     */
    public function getRoomTypes()
    {
        return $this->roomTypes;
    }

    /**
     * Add room
     *
     * @param MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function addRoom(\MBH\Bundle\HotelBundle\Document\Room $room)
    {
        $this->rooms[] = $room;
    }

    /**
     * Remove room
     *
     * @param MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function removeRoom(\MBH\Bundle\HotelBundle\Document\Room $room)
    {
        $this->rooms->removeElement($room);
    }

    /**
     * Get rooms
     *
     * @return Doctrine\Common\Collections\Collection $rooms
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * Add tariff
     *
     * @param MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function addTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariffs[] = $tariff;
    }

    /**
     * Remove tariff
     *
     * @param MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function removeTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariffs->removeElement($tariff);
    }

    /**
     * Get tariffs
     *
     * @return Doctrine\Common\Collections\Collection $tariffs
     */
    public function getTariffs()
    {
        return $this->tariffs;
    }
}
