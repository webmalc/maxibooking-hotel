<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\PriceBundle\Document\Promotion;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Annotations as MBH;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\PackageBundle\Document\Partials\DeleteReasonTrait;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use MBH\Bundle\PackageBundle\Validator\Constraints as MBHValidator;
use MBH\Bundle\PriceBundle\Document\Special;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @ODM\Document(collection="Packages", repositoryClass="MBH\Bundle\PackageBundle\Document\PackageRepository")
 * @MBHValidator\Package
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 * @MongoDBUnique(fields="numberWithPrefix", message="mbhpackagebundle.document.package.takoy.nomer.broni.uzhe.sushchestvuyet")
 */
class Package extends Base implements \JsonSerializable
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;
    use DeleteReasonTrait;


    const ROOM_STATUS_OPEN = 'open';
    const ROOM_STATUS_WAIT = 'wait'; //Не заехал
    const ROOM_STATUS_WAIT_TODAY = 'wait_today'; // Заезжет сегодня
    const ROOM_STATUS_IN_TODAY = 'in_today'; // Заехал сегодня
    const ROOM_STATUS_WILL_OUT = 'will_out'; // Выезд
    const ROOM_STATUS_OUT_TODAY = 'out_today'; // Выезд сегодня
    const ROOM_STATUS_OUT_TOMORROW = 'out_tomorrow'; // Выезд завтра
    const ROOM_STATUS_NOT_OUT = 'not_out'; // Не выехал

    private $packagePricesWithDiscount = [];
    private $isPackagePricesWithDiscountInit = false;

    /**
     * @var Order
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Order", inversedBy="packages")
     * @Assert\NotNull(message= "validator.document.package.order_not_selected")
     * @ODM\Index()
     */
    protected $order;

    /** @ODM\ReferenceMany(targetDocument="PackageService", mappedBy="package") */
    protected $services;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull(message= "validator.document.package.tariff_not_selected")
     * @ODM\Index()
     */
    protected $tariff;
    
    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull(message= "validator.document.package.room_type_not_selected")
     * @ODM\Index()
     */
    protected $roomType;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     * @deprecated
     */
    protected $accommodation;

    /**
     * @ODM\ReferenceMany(targetDocument="PackageAccommodation", inversedBy="package", cascade={"persist"})
     * @ODM\Index()
     */
    protected $accommodations;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     */
    protected $virtualRoom;
    
    /**
     * @ODM\ReferenceMany(targetDocument="Tourist", inversedBy="packages")
     * @MBH\Versioned()
     */
    protected $tourists;

    /**

     * @ODM\ReferenceMany(targetDocument="RestarauntSeat", mappedBy="package")
     */
    protected $restarauntSeat;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @ODM\Index()
     */
    protected $number;
    

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="numberWithPrefix")
     * @ODM\Index()
     *
     */
    protected $numberWithPrefix;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer(name="adults")
     * @Assert\NotNull(message="mbhpackagebundle.document.package.kolichestvo.vzroslykh.ne.ukazano")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.adults_amount_less_zero"
     * )
     * @ODM\Index()
     */
    protected $adults;
    
    
    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer(name="children")
     * @Assert\NotNull(message="mbhpackagebundle.document.package.kolichestvo.detey.ne.ukazano")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.children_amount_less_zero"
     * )
     * @ODM\Index()
     */
    protected $children;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\NotNull(message= "validator.document.package.begin_not_specified")
     * @Assert\Date()
     * @ODM\Index()
     */
    protected $begin;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\NotNull(message= "validator.document.package.end_not_specified")
     * @Assert\Date()
     * @ODM\Index()
     */
    protected $end;

    /**
     * @var Promotion|null
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Promotion")
     */
    protected $promotion;

    /**
     * @var Special|null
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Special")
     */
    protected $special;

    /**
     * @var float
     * @Gedmo\Versioned
     * @deprecated
     * @ODM\Field(type="float")
     */
    protected $promotionTotal = 0;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Field(type="float", name="price")
     * @Assert\NotNull(message= "validator.document.package.price_not_specified")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.price_less_zero"
     * )
     * @ODM\Index()
     */
    protected $price;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.order.price_less_zero"
     * )
     * @ODM\Index()
     */
    protected $originalPrice;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.price_less_zero"
     * )
     * @ODM\Index()
     */
    protected $totalOverwrite;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Field(type="hash")
     * @Assert\Type(type="array")
     * @deprecated
     */
    protected $pricesByDate = [];

    /**
     * @var PackagePrice[]
     * @ODM\EmbedMany(targetDocument="PackagePrice")
     */
    protected $prices;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float", name="servicesPrice")
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
     * @ODM\Field(type="string", name="note")
     * @ODM\Index()
     */
    protected $note;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="purposeOfArrival")
     * @Assert\Choice(
     *      choices = {"service", "tourism", "business", "study", "work", "private", "residence", "humanitarian", "other"},
     *      message = "validator.document.package.wrong_arrival_purpose"
     * )
     */
    protected $purposeOfArrival;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="channelManagerType")
     * @Assert\Choice(
     *      choices = {"vashotel", "booking", "expedia", "hotels", "venere", "ostrovok", "oktogo", "myallocator", "101Hotels"},
     *      message = "validator.document.package.wrong_channel_manager_type"
     * )
     * @ODM\Index()
     */
    protected $channelManagerType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="channelManagerId")
     */
    protected $channelManagerId;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\DateTime()
     */
    protected $arrivalTime;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\DateTime()
     */
    protected $departureTime;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * Assert\Range(
     *      min=1,
     *      minMessage= "validator.document.package.discount_less_1",
     *      max=100,
     *      maxMessage= "validator.document.package.discount_greater_100",
     * )
     */
    protected $discount;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     */
    protected $isPercentDiscount = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $isCheckIn = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $isCheckOut = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $isSmoking = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $corrupted = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $isLocked = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $isForceBooking = false;

    /**
     * @var array
     * @ODM\Field(type="collection")
     */
    protected $childAges = [];

    /**
     * @var SearchQuery
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\PackageBundle\Document\SearchQuery")
     */
    protected $searchQuery;
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

    public function allowPercentagePrice($price)
    {
        $minPerPay = $this->getTariff()->getMinPerPrepay();

        return $price * $minPerPay / 100;
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
     * @return \MBH\Bundle\HotelBundle\Document\Hotel
     */
    public function getHotel()
    {
        return $this->getRoomType()->getHotel();
    }

    /**
     * @param bool $old
     * @return mixed|null
     */
    public function getAccommodation(bool $old = false)
    {
        if ($old) {
            return $this->accommodation;
        }
        return $this->accommodations->last() ?? null;
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
     * @return int
     */
    public function getNumberOfGuests()
    {
        return $this->getAdults() + $this->getChildren();
    }

    /**
     * Set begin
     *
     * @param \DateTime $begin
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
     * @return \DateTime $begin
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
     * @return Promotion|null
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * @param Promotion|null $promotion
     * @return $this
     */
    public function setPromotion(Promotion $promotion = null)
    {
        $this->promotion = $promotion;
        return $this;
    }

    /**
     * @param bool|false $calculate
     * @deprecated
     * @return float
     */
    public function getPromotionTotal($calculate = false)
    {
        if ($calculate) {
            if ($this->getPromotion()) {
                $promotion = $this->getPromotion();
                $this->promotionTotal = $promotion->getisPercentDiscount() ?
                    $this->price * $promotion->getDiscount() / 100 :
                    $promotion->getDiscount();
            } else {
                $this->promotionTotal = 0;
            }
        }
        return $this->promotionTotal;
    }

    /**
     * @deprecated
     * @param float $promotionTotal
     */
    public function setPromotionTotal($promotionTotal)
    {
        $this->promotionTotal = $promotionTotal;
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
        if (!empty($this->getTotalOverwrite())) {
            return $this->getTotalOverwrite();
        }

        return $this->price - $this->getDiscountMoney() + $this->getServicesPrice();
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
            return $this->price - $this->getDiscountMoney();
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
        $this->services = new ArrayCollection();
        $this->restarauntSeat = new ArrayCollection();
        $this->tourists = new ArrayCollection();
        $this->accommodations = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->searchQuery = new ArrayCollection();
    }


    /**
     * Add tourist
     *
     * @param Tourist $tourist
     */
    public function addTourist(Tourist $tourist)
    {
        if (!$this->tourists->contains($tourist)) {
            $this->tourists[] = $tourist;
        }
    }

    /**
     * Remove tourist
     *
     * @param Tourist $tourist
     */
    public function removeTourist(Tourist $tourist)
    {
        $this->tourists->removeElement($tourist);
    }

    /**
     * Get tourists
     *
     * @return Tourist[]|\Doctrine\Common\Collections\Collection $tourists
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
     * @return float|int
     */
    public function getCalculatedPayment()
    {
        return $this->getOrder()->getPrice() == 0 ? 0 : round($this->getPrice() / $this->getOrder()->getPrice() * $this->getPaid(), 2);
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
        return $this->getTitle();
    }

    public function getTitle(bool $accommodation = false, bool $payer = false)
    {
        $title = $this->getNumberWithPrefix();
        if ($accommodation && $this->getAccommodation()) {
            $title .=' Номер: '.$this->getAccommodation()->getName().'. ';
        }
        /** @var Tourist|Organization $name */
        if ($payer && $name = $this->getOrder()->getPayer()) {
            $title .= ' Плательщик: '.$name.'. ';
        }
        return $title;
    }

    /**
     * @param \DateTime $arrivalTime
     * @return $this
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
     * @return $this
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

    /**
     * @param int $discount
     * @return $this
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return int $discount
     */
    public function getDiscount($percent = true)
    {
        return ($percent) ? $this->discount : $this->discount/100;
    }

    /**
     * @return float|int
     */
    public function getDiscountMoney()
    {
        if ($this->isPercentDiscount) {
            return $this->price * $this->getDiscount(false);
        }
        return $this->discount;
    }

    /**
     * @return bool
     */
    public function getIsPercentDiscount()
    {
        return $this->isPercentDiscount;
    }

    /**
     * @param bool $isPercentDiscount
     */
    public function setIsPercentDiscount($isPercentDiscount)
    {
        $this->isPercentDiscount = $isPercentDiscount;
        return $this;
    }

    /**
     * Add service
     *
     * @param PackageService $service
     */
    public function addService(PackageService $service)
    {
        $this->services[] = $service;
    }

    /**
     * Remove service
     *
     * @param PackageService $service
     */
    public function removeService(PackageService $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection|PackageService[] $services
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return array
     */
    public function getUnDeletedServices()
    {
        $services = [];
        /** @var PackageService $service */
        foreach ($this->services as $service) {
            if (empty($service->getDeletedAt())) {
                $services[] = $service;
            }
        }

        return $services;
    }
    
    /**
     * get services for recalculation
     *
     * @return array
     */
    public function getServicesForRecalc(): array
    {
        return array_filter($this->services->toArray(), function ($service) {
            if (!$service->isRecalcWithPackage()) {
                return false;
            }
            if ($service->getBegin() == $this->getBegin() && $service->getEnd() == $this->getEnd()) {
                return false;
            }
            return true;
        });
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
     * @param \DateTime|string|null $day Date format d.m.Y
     * @return float
     */
    public function getOneDayPrice($day = null)
    {
        $date = null;

        if ($day instanceof \DateTime) {
            $date = $day>format('d_m_Y');
        }
        if (preg_match('/^\d{2}\.\d{2}.\d{4}$/ui', (string) $day)) {
            $date = str_replace('.', '_', $day);
        }

        if (!$date || empty($this->pricesByDate[$date])) {
            return round($this->getPackagePrice()/$this->getNights(), 2);
        } else {
            return (float) $this->pricesByDate[$date];
        }
    }

    public function isEarlyCheckIn()
    {
        foreach ($this->getServices() as $service) {
            if ($service->getService()->getCode() == 'Early check-in') {
                return true;
            }
        }

        return false;
    }

    public function isLateCheckOut()
    {
        foreach ($this->getServices() as $service) {
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
     * @deprecated
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
     * @deprecated
     */
    public function getPricesByDate()
    {
        return $this->pricesByDate;
    }

    /**
     * @return array
     */
    public function getPricesByDateByPrice()
    {
        $data = $this->getPricesByDate();
        $dates = array_keys($data);
        $prices = array_values($data);
        $result = [];
        $begin = null;
        $nights = 1;
        for ($i = 0; $i < count($prices); ++$i) {
            $price = $prices[$i];
            $nextPrice = @$prices[$i+1];
            $date = $dates[$i];
            $nextDate = @$dates[$i+1];
            if ($nextPrice) {
                if ($price == $nextPrice) {
                    if ($begin == null) {
                        $begin = $date;
                    }
                    ++$nights;
                } else {
                    $result[$begin == null || $begin == $date ? ($date.' - '.$nextDate) : ($begin.' - '.$nextDate)] = [
                        'price' => $price,
                        'nights' => $nights
                    ];
                    $begin = null;
                    $nights = 1;
                }
            } else {
                if (!$nextDate) {
                    $nextDate = \DateTime::createFromFormat('d_m_Y', $date)->modify('+1 day')->format('d_m_Y');
                }
                $result[$begin.' - '.$nextDate] = [
                    'price' => $price,
                    'nights' => $nights
                ];
            }
        }

        return $result;
    }

    /**
     * Set isSmoking
     *
     * @param boolean $isSmoking
     * @return self
     */
    public function setIsSmoking($isSmoking)
    {
        $this->isSmoking = $isSmoking;
        return $this;
    }

    /**
     * Get isSmoking
     *
     * @return boolean $isSmoking
     */
    public function getIsSmoking()
    {
        return $this->isSmoking;
    }

    /**
     * Set corrupted
     *
     * @param boolean $corrupted
     * @return self
     */
    public function setCorrupted($corrupted)
    {
        $this->corrupted = $corrupted;
        return $this;
    }

    /**
     * Get corrupted
     *
     * @return boolean $corrupted
     */
    public function getCorrupted()
    {
        return $this->corrupted;
    }

    /**
     * Set totalOverwrite
     *
     * @param float $totalOverwrite
     * @return self
     */
    public function setTotalOverwrite($totalOverwrite)
    {
        $this->totalOverwrite = $totalOverwrite;
        return $this;
    }

    /**
     * Get totalOverwrite
     *
     * @return float $totalOverwrite
     */
    public function getTotalOverwrite()
    {
        return $this->totalOverwrite;
    }

    /**
     * @param $code
     * @return \MBH\Bundle\PackageBundle\Document\PackageService|null
     */
    public function getService($code)
    {
        foreach ($this->getServices() as $service) {
            if ($service->getService()->getCode() == $code) {
                return $service;
            }
        }

        return null;
    }

    /**
     * @return Tourist|null
     */
    public function getMainTourist()
    {
        return $this->getOrder()->getMainTourist();
    }

    /**
     * @return PayerInterface|null
     */
    public function getPayer()
    {
        return $this->getOrder()->getPayer();
    }

    /**
     * @param boolean $isCheckOut
     */
    public function setIsCheckOut($isCheckOut)
    {
        $this->isCheckOut = $isCheckOut;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsCheckOut()
    {
        return $this->isCheckOut;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->checkCheckInOut();
        $this->setPromotionTotal($this->getPromotionTotal(true));
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->checkCheckInOut();
        $this->setPromotionTotal($this->getPromotionTotal(true));
    }

    public function checkCheckInOut()
    {
        if (!$this->getIsCheckIn()) {
            $this->setArrivalTime(null);
        } elseif (!$this->getArrivalTime()) {
            $this->setArrivalTime(new \DateTime());
        }
        if (!$this->getIsCheckOut()) {
            $this->setDepartureTime(null);
        } elseif (!$this->getDepartureTime()) {
            $this->setDepartureTime(new \DateTime());
        }
    }

    public function toArray()
    {
        return [
            'packageKey' => $this->getId(),
            'number' => $this->getNumberWithPrefix(),
            'hotel' => (string) $this->getRoomType()->getHotel(),
            'roomType' => (string) $this->getRoomType(),
            'payer' => (string) $this->getOrder()->getPayer(),
            'price' => $this->getPrice(),
            'begin' => $this->getBegin()->format('d.m.Y'),
            'end' => $this->getEnd()->format('d.m.Y'),
            'type' => $this->getStatus(),
        ];
    }

    public function jsonSerialize()
    {
        $this->toArray();
    }

    /**
     * @return int
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice ? $this->originalPrice : $this->getPrice();
    }

    /**
     * @param int $originalPrice
     * @return self
     */
    public function setOriginalPrice($originalPrice)
    {
        $this->originalPrice = $originalPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getDebt()
    {
        return $this->order->getDebt();
    }

    /**
     * @return null|string
     */
    public function getPaidStatus()
    {
        return $this->order->getPaidStatus();
    }

    /**
     * @return boolean
     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * @param boolean $isLocked
     * @return $this
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoomStatus()
    {
        if (!$this->getOrder()) {
            return self::ROOM_STATUS_OPEN;
        }
        $today = new \DateTime('midnight');
        $tomorrow = new \DateTime('tomorrow');

        if ($this->getIsCheckIn()) {
            if ($this->getIsCheckOut()) {
                return self::ROOM_STATUS_OPEN;
            } else {
                if ($this->getBegin() == $today) {
                    return self::ROOM_STATUS_IN_TODAY;
                } elseif ($this->getEnd() == $today) {
                    return self::ROOM_STATUS_OUT_TODAY;
                } elseif ($this->getEnd() == $tomorrow) {
                    return self::ROOM_STATUS_OUT_TOMORROW;
                } elseif ($this->getEnd() <= new \DateTime('midnight +1 day')) {
                    return self::ROOM_STATUS_NOT_OUT;
                } else {
                    return self::ROOM_STATUS_WILL_OUT;
                }
            }
        } else {
            return $this->getBegin() == $today ?
                self::ROOM_STATUS_WAIT_TODAY :
                self::ROOM_STATUS_WAIT;
        }
    }

    /**
     * @return array
     */
    public static function getRoomStatuses()
    {
        return [
            self::ROOM_STATUS_OPEN,
            self::ROOM_STATUS_WAIT,
            self::ROOM_STATUS_WAIT_TODAY,
            self::ROOM_STATUS_WILL_OUT,
            self::ROOM_STATUS_OUT_TODAY,
            self::ROOM_STATUS_OUT_TOMORROW,
            self::ROOM_STATUS_NOT_OUT,
        ];
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(callback="isDiscountValid")
     */
    public function isDiscountValid(ExecutionContextInterface $context)
    {
        if ($this->isPercentDiscount) {
            $rangeValidator = new Assert\RangeValidator();
            $rangeValidator->initialize($context);
            $rangeValidator->validate($this->discount, new Assert\Range(['min' => 0, 'max' => 100]));
        }
    }

    /**
     * @return PackagePrice[]|PersistentCollection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @return array
     */
    public function getPricesByDateWithDiscount()
    {
        $prices = [];
        foreach ($this->pricesByDate as $dateString => $price) {
            $prices[$dateString] = $price - ($this->getDiscountMoney() / $this->getNights());
        }

        return $prices;
    }

    /**
     * @return array
     */
    public function getPackagePricesWithDiscount()
    {
        if (!$this->isPackagePricesWithDiscountInit) {
            foreach ($this->getPrices() as $price) {
                $priceFraction = $this->getPackagePrice() != 0 ? $price->getPrice() / $this->getPackagePrice() : 0;

                $clonedPrice = clone $price;
                $priceWithDiscount = $this->isPercentDiscount
                    ? $price->getPrice() * (1 - $this->getDiscount(false))
                    : $price->getPrice() - $this->discount * $priceFraction;
                $clonedPrice->setPrice($priceWithDiscount);
                $this->packagePricesWithDiscount[] = $clonedPrice;
            }
            $this->isPackagePricesWithDiscountInit = true;
        }

        return $this->packagePricesWithDiscount;
    }

    /**
     * @param array $prices
     * @return Package
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
        return $this;
    }

    /**
     * @param PackagePrice $packagePrice
     * @return Package
     */
    public function addPackagePrice(PackagePrice $packagePrice)
    {
        $this->prices->add($packagePrice);

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsForceBooking()
    {
        return $this->isForceBooking;
    }

    /**
     * @param boolean $isForceBooking
     * @return Package
     */
    public function setIsForceBooking($isForceBooking)
    {
        $this->isForceBooking = $isForceBooking;
        return $this;
    }

    public function clearServices()
    {
        $this->services = new ArrayCollection();
        
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVirtualRoom()
    {
        return $this->virtualRoom;
    }

    /**
     * @param mixed $virtualRoom
     * @return Package
     */
    public function setVirtualRoom(Room $virtualRoom = null): self
    {
        $this->virtualRoom = $virtualRoom;
        return $this;
    }

    /**
     * @return Collection|ArrayCollection
     */
    public function getAccommodations(): Collection
    {
        return $this->getSortedAccommodations();
    }

    /**
     * @return Special|null
     */
    public function getSpecial(): ?Special
    {
        return $this->special;
    }

    /**
     * @param Special|null $special
     * @return Package
     */
    public function setSpecial(Special $special = null): self
    {
        $this->special = $special;

        return $this;
    }

    /**
     * @return AddressInterface|null
     */
    public function getAddress(): AddressInterface
    {
        return $this->getHotel()->getOrganization() ?? $this->getHotel();
    }

    public function getSortedAccommodations()
    {
        $data = $this->accommodations->toArray();
        usort($data, function ($a, $b) {
            /** @var PackageAccommodation $a*/
            /** @var PackageAccommodation $b*/
            return ($a->getBegin() < $b->getBegin())? -1 : 1;
        });

        return new ArrayCollection($data);
    }

    /**
     * @param Collection $accommodations
     * @return Package
     */
    public function setAccommodations(Collection $accommodations): self
    {
        $this->accommodations = $accommodations;
        return $this;
    }

    /**
     * Add accommodation
     *
     * @param PackageAccommodation $accommodation
     * @return Package
     */
    public function addAccommodation(PackageAccommodation $accommodation): self
    {
        $this->accommodations[] = $accommodation;

        return $this;
    }

    /**
     * Remove accommodation
     *
     * @param PackageAccommodation $accommodation
     * @return Package
     */
    public function removeAccommodation(PackageAccommodation $accommodation)
    {
        $this->accommodations->removeElement($accommodation);

        return $this;
    }

    /**
     * @return Package
     */
    public function removeAccommodations()
    {
        $this->accommodations = new ArrayCollection();

        return $this;
    }

    /**
     * @return \DateTime
     * @deprecated
     */
    public function getLastEndAccommodation(): \DateTime
    {
        $begin = $this->getBegin();
        if ($this->accommodations->count()) {
            $begin = max(array_map(function ($acc) {
                /** @var PackageAccommodation $acc */
                return $acc->getEnd();
            }, $this->accommodations->toArray()));
        }

        return $begin;
    }

    /**
     * @return mixed
     */
    public function getLastAccommodation()
    {
        return $this->getAccommodations()->last();
    }

    /**
     * @return mixed
     */
    public function getFirstAccommodation()
    {
        return $this->getAccommodations()->first();
    }

    /** Для совместимости в автозадачах */
    public function getAccommodationCheckIn()
    {
        return $this->getFirstAccommodation();
    }

    /**
     * @return mixed
     */
    public function getAccommodationCheckOut()
    {
        return $this->getLastAccommodation();
    }

    /**
     * @param \DateTime $dateTime
     * @return mixed
     */
    public function getAccommodationByDate(\DateTime $dateTime)
    {
        $accommodation = $this->accommodations->filter(function ($accommodation) use ($dateTime) {
            /** @var PackageAccommodation $accommodation */
            return $accommodation->getBegin() <= $dateTime && $dateTime <= $accommodation->getEnd();
        });

        return $accommodation->first();
    }

    /**
     * @param \DateTime $date
     * @param bool $withDiscount
     * @return PackagePrice|null
     */
    public function getPackagePriceByDate(\DateTime $date, bool $withDiscount = false)
    {
        $prices = $withDiscount ? $this->getPackagePricesWithDiscount() : $this->getPrices();
        /** @var PackagePrice $price */
        foreach ($prices as $price) {
            if ($price->getDate() == $date) {
                return $price;
            }
        }

        return null;
    }

    /**
     * @return ArrayCollection
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return Package
     */
    public function addSearchQuery(SearchQuery $searchQuery): Package
    {
        $this->searchQuery->add($searchQuery);

        return $this;
    }

    /**
     * @param \DateTime|null $date
     * @return float
     */
    public function getPriceByDate(\DateTime $date = null): float
    {
        if (!is_null($date)) {
            if ($this->getPrices()->count() > 0 && $this->getPackagePriceByDate($date)) {
                return $this->getPackagePriceByDate($date)->getPrice();
            }

            $firstDateString = $date->format('d.m.Y');
            if (!empty($this->pricesByDate) && isset($this->pricesByDate[$firstDateString])) {
                return $this->pricesByDate[$firstDateString];
            }
        }

        return $this->getPackagePrice() / $this->getNights();
    }

    /**
     * @return array]
     */
    public function getJsonSerialized()
    {
        $services = array_map(function (PackageService $packageService) {
            return $packageService->getService()->getJsonSerialized();
        }, $this->getServices()->toArray());

        return [
            'id' => $this->getId(),
            'roomTypeId' => $this->getRoomType()->getId(),
            'tariffId' => $this->getTariff()->getId(),
            'adults' => $this->getAdults(),
            'children' => $this->getChildren(),
            'begin' => $this->getBegin()->format('d.m.Y'),
            'end' => $this->getEnd()->format('d.m.Y'),
            'services' => $services
        ];
    }
}
