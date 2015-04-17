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

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Order", inversedBy="packages")
     * @Assert\NotNull(message= "validator.document.package.order_not_selected")
    **/
    protected $order;

    /** @ODM\ReferenceMany(targetDocument="PackageService", mappedBy="package") */
    protected $services;

    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull(message= "validator.document.package.tariff_not_selected")
     */
    protected $tariff;
    
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull(message= "validator.document.package.room_type_not_selected")
     */
    protected $roomType;
    
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     */
    protected $accommodation;
    
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
     *      minMessage= "validator.document.package.adults_amount_less_zero"
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
     *      minMessage= "validator.document.package.children_amount_less_zero"
     * )
     */
    protected $children;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="begin")
     * @Assert\NotNull(message= "validator.document.package.begin_not_specified")
     * @Assert\Date()
     */
    protected $begin;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="end")
     * @Assert\NotNull(message= "validator.document.package.end_not_specified")
     * @Assert\Date()
     */
    protected $end;
    
    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="price")
     * @Assert\NotNull(message= "validator.document.package.price_not_specified")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.price_less_zero"
     * )
     */
    protected $price;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Hash()
     * @Assert\Type(type="array")
     */
    protected $pricesByDate;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="servicesPrice")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.service_price_less_zero"
     * )
     */
    protected $servicesPrice;
    
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
     *      message = "validator.document.package.wrong_arrival_purpose"
     * )
     */
    protected $purposeOfArrival;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="channelManagerType")
     * @Assert\Choice(
     *      choices = {"vashotel"},
     *      message = "validator.document.package.wrong_channel_manager_type"
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
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.check_in_time_less_zero",
     *      max=23,
     *      maxMessage= "validator.document.package.check_in_time_greater_23",
     * )
     */
    protected $arrivalTime;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.check_out_time_less_zero",
     *      max=23,
     *      maxMessage= "validator.document.package.check_out_time_greater_23",
     * )
     */
    protected $departureTime;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage= "validator.document.package.discount_less_1",
     *      max=100,
     *      maxMessage= "validator.document.package.discount_greater_100",
     * )
     */
    protected $discount;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $isCheckIn = false;

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
     * @return \MBH\Bundle\PriceBundle\Document\Tariff $tariff
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
     * @return \MBH\Bundle\HotelBundle\Document\RoomType $roomType
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
     * @return \MBH\Bundle\HotelBundle\Document\Room $accommodation
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
        $this->setIsCheckIn(false);

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
     * @return \DateTime $end
     */
    public function getEnd()
    {
        return $this->end;
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
        return $this->price - $this->price * $this->getDiscount(false) + $this->getServicesPrice();
    }

    /**
     * Get price
     *
     * @param boolean $discount
     * @return int $price
     */
    public function getPackagePrice($discount = false)
    {
        if ($discount) {
            return $this->price - $this->price * $this->getDiscount(false);
        }

        return $this->price;
    }

    /**
     * Set price
     *
     * @param int $price
     * @return self
     */
    public function setPackagePrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->getOrder()->getStatus();
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
     * @return \Doctrine\Common\Collections\Collection $tourists
     */
    public function getTourists()
    {
        return $this->tourists;
    }

    /**
     * Get isPaid
     *
     * @return boolean $isPaid
     */
    public function getIsPaid()
    {
        return $this->getOrder()->getIsPaid();
    }

    /**
     * Get order paid
     *
     * @return int
     */
    public function getPaid()
    {
        return $this->getOrder()->getPaid();
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

    public function __toString()
    {
        return $this->getNumberWithPrefix();
    }

    /**
     * Set arrivalTime
     *
     * @param int $arrivalTime
     * @return self
     */
    public function setArrivalTime($arrivalTime)
    {
        $this->arrivalTime = $arrivalTime;
        return $this;
    }

    /**
     * Get arrivalTime
     *
     * @return int $arrivalTime
     */
    public function getArrivalTime()
    {
        return $this->arrivalTime;
    }

    /**
     * Set departureTime
     *
     * @param int $departureTime
     * @return self
     */
    public function setDepartureTime($departureTime)
    {
        $this->departureTime = $departureTime;
        return $this;
    }

    /**
     * Get departureTime
     *
     * @return int $departureTime
     */
    public function getDepartureTime()
    {
        return $this->departureTime;
    }

    /**
     * Set discount
     *
     * @param int $discount
     * @return self
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * Get discount
     *
     * @return int $discount
     */
    public function getDiscount($percent = true)
    {
        return ($percent) ? $this->discount : $this->discount/100;
    }

    /**
     * Add service
     *
     * @param \MBH\Bundle\PackageBundle\Document\PackageService $service
     */
    public function addService(\MBH\Bundle\PackageBundle\Document\PackageService $service)
    {
        $this->services[] = $service;
    }

    /**
     * Remove service
     *
     * @param \MBH\Bundle\PackageBundle\Document\PackageService $service
     */
    public function removeService(\MBH\Bundle\PackageBundle\Document\PackageService $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection $services
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Set servicesPrice
     *
     * @param int $servicesPrice
     * @return self
     */
    public function setServicesPrice($servicesPrice)
    {
        $this->servicesPrice = $servicesPrice;
        return $this;
    }

    /**
     * Get servicesPrice
     *
     * @return int $servicesPrice
     */
    public function getServicesPrice()
    {
        return $this->servicesPrice;
    }

    public function getNumberWithPayer()
    {
        $result = $this->getNumberWithPrefix();
        if (!empty($this->getMainTourist())) {
            $result .= ' (' . $this->getMainTourist()->getFullName() . ')';
        }

        return $result;
    }

    /**
     * Get source
     *
     * @return \MBH\Bundle\PackageBundle\Document\PackageSource $source
     */
    public function getSource()
    {
        return $this->getOrder()->getSource();
    }

    /**
     * Get confirmed
     *
     * @return boolean $confirmed
     */
    public function getConfirmed()
    {
        return $this->getOrder()->getConfirmed();
    }

    /**
     * Set order
     *
     * @param \MBH\Bundle\PackageBundle\Document\Order $order
     * @return self
     */
    public function setOrder(\MBH\Bundle\PackageBundle\Document\Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return \MBH\Bundle\PackageBundle\Document\Order $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set isCheckIn
     *
     * @param boolean $isCheckIn
     * @return self
     */
    public function setIsCheckIn($isCheckIn)
    {
        $this->isCheckIn = $isCheckIn;
        return $this;
    }

    /**
     * Get isCheckIn
     *
     * @return boolean $isCheckIn
     */
    public function getIsCheckIn()
    {
        return $this->isCheckIn;
    }

    /**
     * Returns the average price per night.
     * @return float
     */
    public function getOneDayPrice()
    {
        return round($this->getPackagePrice()/$this->getNights());
    }

    public function isEarlyCheckIn()
    {
        foreach($this->getServices() as $service) {
            if ($service->getService()->getCode() == 'Early check-in') {
                return true;
            }
        }

        return false;
    }

    public function isLateCheckOut()
    {
        foreach($this->getServices() as $service) {
            if ($service->getService()->getCode() == 'Late check-out') {
                return true;
            }
        }

        return false;
    }

    /**
     * Set pricesByDate
     *
     * @param array $pricesByDate
     * @return self
     */
    public function setPricesByDate($pricesByDate)
    {
        $this->pricesByDate = $pricesByDate;
        return $this;
    }

    /**
     * Get pricesByDate
     *
     * @return array $pricesByDate
     */
    public function getPricesByDate()
    {
        return $this->pricesByDate;
    }
}
