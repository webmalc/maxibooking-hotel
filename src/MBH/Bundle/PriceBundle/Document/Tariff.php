<?php

namespace MBH\Bundle\PriceBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\PriceBundle\Validator\Constraints as MBHValidator;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Tariffs", repositoryClass="MBH\Bundle\PriceBundle\Document\TariffRepository")
 * @Gedmo\Loggable
 * @MBHValidator\Tariff
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Tariff extends Base
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
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="tariffs")
     * @Assert\NotNull(message="Не выбран отель")
     */
    protected $hotel;
    
    /** 
     * @var FoodPrice[]
     * @ODM\EmbedMany(targetDocument="FoodPrice")
     */
    protected $foodPrices;
    
    /** 
     * @var RoomQuota[]
     * @ODM\EmbedMany(targetDocument="RoomQuota")
     */
    protected $roomQuotas;

    /** 
     * @var RoomPrice[]
     * @ODM\EmbedMany(targetDocument="RoomPrice")
     */
    protected $roomPrices;
    
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
     * @ODM\String(name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое описание",
     *      max=300,
     *      maxMessage="Слишком длинное описание"
     * )
     */
    protected $description;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="begin")
     * @Assert\NotNull()
     * @Assert\Date()
     */
    protected $begin;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="end")
     * @Assert\NotNull()
     * @Assert\Date()
     */
    protected $end;
    
    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isDefault")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isDefault = true;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="type")
     * @Assert\Choice(
     *      choices = {"rate", "price"}, 
     *      message = "Неверный тип тарифа."
     * )
     */
    protected $type;
    
    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isOnline")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isOnline = true;
    
    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="rate")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Скидка/наценка не может быть меньше нуля"
     * )
     */
    protected $rate = 0;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage="Максимальная продолжительность брони не может быть меньше 1"
     * )
     */
    protected $maxPackageDuration;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage="Минимальная продолжительность брони не может быть меньше 1"
     * )
     */
    protected $minPackageDuration;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isEnabled")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isEnabled = true;
   
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
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
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

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set isOnline
     *
     * @param boolean $isOnline
     * @return self
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;
        return $this;
    }

    /**
     * Get isOnline
     *
     * @return boolean $isOnline
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }
    
    /**
     * Set rate
     *
     * @param int $rate
     * @return self
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * Get rate
     *
     * @return int $rate
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set begin
     *
     * @param date $begin
     * @return self
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * Get begin
     *
     * @return date $begin
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Set end
     *
     * @param date $end
     * @return self
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Get end
     *
     * @return date $end
     */
    public function getEnd()
    {
        return $this->end;
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

    public function __construct()
    {
        $this->foodPrices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->roomQuotas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->roomPrices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param FoodPrice $foodPrice
     * @return $this
     */
    public function addFoodPrice(\MBH\Bundle\PriceBundle\Document\FoodPrice $foodPrice)
    {
        $this->foodPrices[] = $foodPrice;

        return $this;
    }

    /**
     * Remove foodPrice
     *
     * @param \MBH\Bundle\PriceBundle\Document\FoodPrice $foodPrice
     */
    public function removeFoodPrice(\MBH\Bundle\PriceBundle\Document\FoodPrice $foodPrice)
    {
        $this->foodPrices->removeElement($foodPrice);
    }
    
    public function removeAllFoodPrices()
    {
        $this->foodPrices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get foodPrices
     *
     * @return Doctrine\Common\Collections\Collection $foodPrices
     */
    public function getFoodPrices()
    {
        return $this->foodPrices;
    }

    /**
     * Add roomQuota
     *
     * @param \MBH\Bundle\PriceBundle\Document\RoomQuota $roomQuota
     */
    public function addRoomQuota(\MBH\Bundle\PriceBundle\Document\RoomQuota $roomQuota)
    {
        $this->roomQuotas[] = $roomQuota;
    }

    /**
     * Remove roomQuota
     *
     * @param \MBH\Bundle\PriceBundle\Document\RoomQuota $roomQuota
     */
    public function removeRoomQuota(\MBH\Bundle\PriceBundle\Document\RoomQuota $roomQuota)
    {
        $this->roomQuotas->removeElement($roomQuota);
    }

    /**
     * Get roomQuotas
     *
     * @return Doctrine\Common\Collections\Collection $roomQuotas
     */
    public function getRoomQuotas()
    {
        return $this->roomQuotas;
    }
    
    public function removeAllRoomQuotas()
    {
        $this->roomQuotas = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setAllRoomQuotas($roomQuotas)
    {
        $this->roomQuotas = $roomQuotas;

        return $this;
    }

    /**
     * Add roomPrice
     *
     * @param \MBH\Bundle\PriceBundle\Document\RoomPrice $roomPrice
     */
    public function addRoomPrice(\MBH\Bundle\PriceBundle\Document\RoomPrice $roomPrice)
    {
        $this->roomPrices[] = $roomPrice;
    }

    /**
     * Remove roomPrice
     *
     * @param \MBH\Bundle\PriceBundle\Document\RoomPrice $roomPrice
     */
    public function removeRoomPrice(\MBH\Bundle\PriceBundle\Document\RoomPrice $roomPrice)
    {
        $this->roomPrices->removeElement($roomPrice);
    }

    /**
     * Get roomPrices
     *
     * @return Doctrine\Common\Collections\Collection $roomPrices
     */
    public function getRoomPrices()
    {
        return $this->roomPrices;
    }
    
    public function removeAllRoomPrices()
    {
        $this->roomPrices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set maxPackageDuration
     *
     * @param int $maxPackageDuration
     * @return self
     */
    public function setMaxPackageDuration($maxPackageDuration)
    {
        $this->maxPackageDuration = $maxPackageDuration;
        return $this;
    }

    /**
     * @param bool $hotel
     * @return int
     */
    public function getMaxPackageDuration($hotel = false)
    {
        if ($hotel && empty($this->maxPackageDuration)) {
            return $this->getHotel()->getMaxPackageDuration();
        }
        return $this->maxPackageDuration;
    }

    /**
     * Set minPackageDuration
     *
     * @param int $minPackageDuration
     * @return self
     */
    public function setMinPackageDuration($minPackageDuration)
    {
        $this->minPackageDuration = $minPackageDuration;
        return $this;
    }

    /**
     * @param bool $hotel
     * @return int
     */
    public function getMinPackageDuration($hotel = false)
    {
        if ($hotel && empty($this->minPackageDuration)) {
            return $this->getHotel()->getMinPackageDuration();
        }
        return $this->minPackageDuration;
    }


    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return self
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return boolean $isEnabled
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }
}
