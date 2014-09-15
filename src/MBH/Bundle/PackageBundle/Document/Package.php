<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\PackageBundle\Validator\Constraints as MBHValidator;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Packages", repositoryClass="MBH\Bundle\PackageBundle\Document\PackageRepository")
 * @MBHValidator\Package
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class Package extends Base
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
    
    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument", mappedBy="package") */
    protected $cashDocuments;
    
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull(message="Не выбран тариф")
     */
    protected $tariff;
    
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull(message="Не выбран тип номера")
     */
    protected $roomType;
    
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     */
    protected $accommodation;
    
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Tourist", inversedBy="mainPackages")
     */
    protected $mainTourist;
    
    /** 
     * @ODM\ReferenceMany(targetDocument="Tourist", inversedBy="packages")
     */
    protected $tourists;
    
    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     */
    protected $number;
    
    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\String(name="numberWithPrefix")
     */
    protected $numberWithPrefix;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="adults")
     * @Assert\NotNull(message="Количество взрослых не указано")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Количество взрослых не может быть меньше нуля"
     * )
     */
    protected $adults;
    
    
    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="children")
     * @Assert\NotNull(message="Количество детей не указано")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Количество детей не может быть меньше нуля"
     * )
     */
    protected $children;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="begin")
     * @Assert\NotNull(message="Начало не указано")
     * @Assert\Date()
     */
    protected $begin;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="end")
     * @Assert\NotNull(message="Конец не указан")
     * @Assert\Date()
     */
    protected $end;
    
    /**
     * @var string
     * @ODM\String(name="food")
     * @Assert\NotNull(message="Не выбран тип питания")
     */
    protected $food;
    
    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="price")
     * @Assert\NotNull(message="Цена не указана")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $price;
    
    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="paid")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Оплачено не может быть меньше нуля"
     * )
     */
    protected $paid;
    
    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $isPaid;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="status")
     * @Assert\Choice(
     *      choices = {"offline", "online", "channel_manager"}, 
     *      message = "Неверный статус."
     * )
     */
    protected $status;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="note")
     */
    protected $note;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="purposeOfArrival")
     * @Assert\Choice(
     *      choices = {"tourism", "work", "study", "residence", "other"}, 
     *      message = "Неверная цель приезда."
     * )
     */
    protected $purposeOfArrival;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="channelManagerType")
     * @Assert\Choice(
     *      choices = {"vashotel"},
     *      message = "Неверный тип channel manager`а."
     * )
     */
    protected $channelManagerType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="channelManagerId")
     */
    protected $channelManagerId;
    
    /**
     * Set tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return self
     */
    public function setTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * Get tariff
     *
     * @return MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function getTariff()
    {
        return $this->tariff;
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
     * @return MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * Set accommodation
     *
     * @param \MBH\Bundle\HotelBundle\Document\Room $accommodation
     * @return self
     */
    public function setAccommodation(\MBH\Bundle\HotelBundle\Document\Room $accommodation)
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * Get accommodation
     *
     * @return MBH\Bundle\HotelBundle\Document\Room $accommodation
     */
    public function getAccommodation()
    {
        return $this->accommodation;
    }

    /**
     * Remove accommodation
     * @return $this
     */
    public function removeAccommodation()
    {
        $this->accommodation = null;

        return $this;
    }

    /**
     * Set number
     *
     * @param int $number
     * @return self
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * Get number
     *
     * @return increment $number
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set numberWithPrefix
     *
     * @param string $numberWithPrefix
     * @return self
     */
    public function setNumberWithPrefix($numberWithPrefix)
    {
        $this->numberWithPrefix = $numberWithPrefix;
        return $this;
    }

    /**
     * Get numberWithPrefix
     *
     * @return string $numberWithPrefix
     */
    public function getNumberWithPrefix()
    {
        return $this->numberWithPrefix;
    }

    /**
     * Set adults
     *
     * @param int $adults
     * @return self
     */
    public function setAdults($adults)
    {
        $this->adults = $adults;
        return $this;
    }

    /**
     * Get adults
     *
     * @return int $adults
     */
    public function getAdults()
    {
        return $this->adults;
    }

    /**
     * Set children
     *
     * @param int $children
     * @return self
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Get children
     *
     * @return int $children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set begin
     *
     * @param /DateTime $begin
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
     * @param /DateTime $end
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
     * @return /DateTime $end
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set food
     *
     * @param string $food
     * @return self
     */
    public function setFood($food)
    {
        $this->food = $food;
        return $this;
    }

    /**
     * Get food
     *
     * @return string $food
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * Set price
     *
     * @param int $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return int $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set paid
     *
     * @param int $paid
     * @return self
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
        return $this;
    }

    /**
     * Get paid
     *
     * @return int $paid
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return self
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Get note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set purposeOfArrival
     *
     * @param string $purposeOfArrival
     * @return self
     */
    public function setPurposeOfArrival($purposeOfArrival)
    {
        $this->purposeOfArrival = $purposeOfArrival;
        return $this;
    }

    /**
     * Get purposeOfArrival
     *
     * @return string $purposeOfArrival
     */
    public function getPurposeOfArrival()
    {
        return $this->purposeOfArrival;
    }
   
    public function __construct()
    {
        $this->tourists = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set mainTourist
     *
     * @param \MBH\Bundle\PackageBundle\Document\Tourist $mainTourist
     * @return self
     */
    public function setMainTourist(\MBH\Bundle\PackageBundle\Document\Tourist $mainTourist)
    {
        $this->mainTourist = $mainTourist;
        return $this;
    }
    
    /**
     * @return \MBH\Bundle\PackageBundle\Document\Package
     */
    public function removeMainTourist()
    {
        $this->mainTourist = null;
        
        return $this;
    }

    /**
     * Get mainTourist
     *
     * @return MBH\Bundle\PackageBundle\Document\Tourist $mainTourist
     */
    public function getMainTourist()
    {
        return $this->mainTourist;
    }

    /**
     * Add tourist
     *
     * @param \MBH\Bundle\PackageBundle\Document\Tourist $tourist
     */
    public function addTourist(\MBH\Bundle\PackageBundle\Document\Tourist $tourist)
    {
        $this->tourists[] = $tourist;
    }

    /**
     * Remove tourist
     *
     * @param \MBH\Bundle\PackageBundle\Document\Tourist $tourist
     */
    public function removeTourist(\MBH\Bundle\PackageBundle\Document\Tourist $tourist)
    {
        $this->tourists->removeElement($tourist);
    }

    /**
     * Get tourists
     *
     * @return Doctrine\Common\Collections\Collection $tourists
     */
    public function getTourists()
    {
        return $this->tourists;
    }
    /**
     * Add cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function addCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments[] = $cashDocument;
    }

    /**
     * Remove cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function removeCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments->removeElement($cashDocument);
    }

    /**
     * Get cashDocuments
     *
     * @return Doctrine\Common\Collections\Collection $cashDocuments
     */
    public function getCashDocuments()
    {
        return $this->cashDocuments;
    }

    /**
     * Set isPaid
     *
     * @param boolean $isPaid
     * @return self
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;
        return $this;
    }

    /**
     * Get isPaid
     *
     * @return boolean $isPaid
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }
    
    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->checkPaid();
    }
    
    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->checkPaid();
    }
    
    public function checkPaid()
    {
        if ($this->getPaid() >= $this->getPrice()) {
            $this->setIsPaid(true);
        } else {
            $this->setIsPaid(false);
        }
    }
    
    /**
     * @return int
     */
    public function getDays()
    {
        return $this->getNights() + 1;
    }

    /**
     * @return int
     */
    public function getNights()
    {
        return $this->end->diff($this->begin)->format("%a");
    }

    /**
     * Set channelManagerType
     *
     * @param string $channelManagerType
     * @return self
     */
    public function setChannelManagerType($channelManagerType)
    {
        $this->channelManagerType = $channelManagerType;
        return $this;
    }

    /**
     * Get channelManagerType
     *
     * @return string $channelManagerType
     */
    public function getChannelManagerType()
    {
        return $this->channelManagerType;
    }

    /**
     * Set channelManagerId
     *
     * @param string $channelManagerId
     * @return self
     */
    public function setChannelManagerId($channelManagerId)
    {
        $this->channelManagerId = $channelManagerId;
        return $this;
    }

    /**
     * Get channelManagerId
     *
     * @return string $channelManagerId
     */
    public function getChannelManagerId()
    {
        return $this->channelManagerId;
    }
}
