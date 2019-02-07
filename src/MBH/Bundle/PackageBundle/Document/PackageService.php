<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\PackageBundle\Validator\Constraints as MBHValidator;

/**
 * @ODM\Document(collection="PackageService")
 * @Gedmo\Loggable
 * @MBHValidator\PackageService
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class PackageService extends Base
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

    public const TYPE_PER_STAY = 'per_stay';
    public const TYPE_PER_NIGHT = 'per_night';
    public const TYPE_NOT_APPLICABLE = 'not_applicable';
    public const TYPE_DAY_PERCENT = 'day_percent';

    /**
     * @var \MBH\Bundle\PriceBundle\Document\Service
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Service")
     * @Assert\NotNull(message= "validator.document.packageService.no_service_selected")
     */
    protected $service;

    /**
     * @var Package
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Package", inversedBy="services")
     * @Assert\NotNull(message= "validator.document.packageService.no_reservation_selected")
     */
    protected $package;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\NotNull()
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.packageService.price_less_zero"
     * )
     */
    protected $price;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $recalcWithPackage = false;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $includeArrival;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $includeDeparture;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     */
    protected $total;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.packageService.price_less_zero"
     * )
     */
    protected $totalOverwrite;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage= "validator.document.packageService.amount_less_1"
     * )
     * @Assert\NotNull()
     */
    protected $amount;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage= "validator.document.packageService.person_amount_less_1"
     * )
     */
    protected $persons;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage= "validator.document.packageService.nights_amount_less_1"
     * )
     */
    protected $nights;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\DateTime()
     */
    protected $time;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $end;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="note")
     */
    protected $note;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $isCustomPrice = false;

    /**
     * @var bool
     * @Gedmo\Versioned()
     * @ODM\Field(type="bool")
     */
    protected $recalcCausedByTouristsNumberChange = false;

    /**
     * @return bool
     */
    public function isRecalcCausedByTouristsNumberChange(): ?bool
    {
        return $this->recalcCausedByTouristsNumberChange;
    }

    /**
     * @param bool $recalcCausedByTouristsNumberChange
     * @return PackageService
     */
    public function setRecalcCausedByTouristsNumberChange(bool $recalcCausedByTouristsNumberChange): PackageService
    {
        $this->recalcCausedByTouristsNumberChange = $recalcCausedByTouristsNumberChange;

        return $this;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getActuallyAmount()
    {
        $type = $this->getService()->getCalcType();
        if ($type == self::TYPE_PER_STAY) {
            return $this->getAmount() * $this->getPersons();
        }
        if ($type == self::TYPE_PER_NIGHT) {
            return $this->getPersons() * $this->getNights() * $this->getAmount();
        }
        if ($type == self::TYPE_NOT_APPLICABLE or $type == self::TYPE_DAY_PERCENT) {
            return $this->getAmount();
        }

        throw new \Exception();
    }

    /**
     * Get service
     *
     * @return \MBH\Bundle\PriceBundle\Document\Service $service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set service
     *
     * @param \MBH\Bundle\PriceBundle\Document\Service $service
     * @return self
     */
    public function setService(\MBH\Bundle\PriceBundle\Document\Service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Get amount
     *
     * @return int $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amount
     *
     * @param int $amount
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get persons
     *
     * @return int $persons
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * Set persons
     *
     * @param int $persons
     * @return self
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;
        return $this;
    }

    /**
     * Get nights
     *
     * @return int $nights
     */
    public function getNights()
    {
        return $this->nights;
    }

    /**
     * Set nights
     *
     * @param int $nights
     * @return self
     */
    public function setNights($nights)
    {
        $this->nights = $nights;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalAmount()
    {
        $result = $this->getAmount();

        if ($this->getCalcType() == self::TYPE_PER_NIGHT) {
            $result *= $this->getNights();
        }

        if (!in_array($this->getCalcType(), [self::TYPE_NOT_APPLICABLE, self::TYPE_DAY_PERCENT])) {
            $result *= $this->getPersons();
        }

        return $result;
    }

    public function getCalcType()
    {
        return $this->getService()->getCalcType();
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        if (is_null($this->total)) {
            $this->total = $this->calcTotal();
        }

        return $this->total;
    }

    /**
     * @param mixed $total
     * @return PackageService
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @param bool $forInnerCalculation
     * @param null $singlePrice
     * @return int
     */
    public function calcTotal($forInnerCalculation = false, $singlePrice = null)
    {
        if (!empty($this->getTotalOverwrite()) && !$forInnerCalculation) {
            return $this->getTotalOverwrite();
        }

        if (is_null($singlePrice)) {
            $singlePrice = $forInnerCalculation && !is_null($this->getService()->getInnerPrice())
                ? $this->getService()->getInnerPrice()
                : $this->getPrice();
        }

        $price = $singlePrice * $this->getAmount();

        if ($this->getCalcType() == self::TYPE_PER_NIGHT) {
            $price *= $this->getNights();
        }

        if (!in_array($this->getCalcType(), [self::TYPE_NOT_APPLICABLE, self::TYPE_DAY_PERCENT])) {
            $price *= $this->getPersons();
        }

        return $price;
    }

    /**
     * @return float $totalOverwrite
     */
    public function getTotalOverwrite()
    {
        return $this->totalOverwrite;
    }

    /**
     * @param float $totalOverwrite
     * @return self
     */
    public function setTotalOverwrite($totalOverwrite)
    {
        $this->totalOverwrite = $totalOverwrite;
        return $this;
    }

    /**
     * Get price
     *
     * @return float $price
     */
    public function getPrice()
    {
        return $this->price;
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
     * Get note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
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
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->setDefaults();
    }

    public function setDefaults()
    {
        $service  = $this->getService();
        $calcType = $service->getCalcType();

        if ($calcType == self::TYPE_PER_STAY || !$this->getNights()) {
            $this->setNights(1);
        }

        if (in_array($calcType, [self::TYPE_NOT_APPLICABLE, self::TYPE_DAY_PERCENT])) {
            $this->setNights(1);
            $this->setPersons(1);
        }

        if (!$this->getPersons()) {
            $this->setPersons(1);
        }

        if (!$service->getTime()) {
            $this->setTime(null);
        }

        if ($calcType != self::TYPE_PER_STAY) {
            if (!$this->getBegin() || !$service->getDate()) {
                $this->setBegin($this->getPackage()->getBegin());
            }
            $end = clone $this->getBegin();
            if (!$service->getDate()) {
                $end->modify('+' . $this->getNights() . ' days');
            }
            $this->setEnd($end);
        } else {
            if (!$this->getBegin()) {
                $this->setBegin($this->calcBegin());
            }
            if (!$this->getEnd()) {
                $this->setEnd($this->calcEnd());
            }
            if ($this->getBegin() > $this->getEnd()) {
                $this->setBegin(null)->setEnd(null);
            }
        }

        $this->total = $this->calcTotal();
    }

    /**
     * calcEnd
     *
     * @return \DateTime
     */
    public function calcEnd(): \DateTime
    {
        $end = clone $this->getPackage()->getEnd();
        if (!$this->isIncludeDeparture()) {
            $end->modify('-1 day');
        }
        return $end;
    }

    /**
     * calcBegin
     *
     * @return \DateTime
     */
    public function calcBegin(): \DateTime
    {
        $begin = clone $this->getPackage()->getBegin();
        if (!$this->isIncludeArrival()) {
            $begin->modify('+1 day');
        }
        return $begin;
    }

    /**
     * @return \DateTime $begin
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return self
     */
    public function setBegin(\DateTime $begin = null)
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * Get package
     *
     * @return \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Set package
     *
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     * @return self
     */
    public function setPackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->setDefaults();
    }

    /**
     * @return boolean $isCustomPrice
     */
    public function getIsCustomPrice()
    {
        return $this->isCustomPrice;
    }

    /**
     * @param boolean $isCustomPrice
     * @return self
     */
    public function setIsCustomPrice($isCustomPrice)
    {
        $this->isCustomPrice = $isCustomPrice;
        return $this;
    }

    /**
     * @return \DateTime $end
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return self
     */
    public function setEnd(\DateTime $end = null)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return \DateTime $time
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     * @return self
     */
    public function setTime(\DateTime $time = null)
    {
        $this->time = $time;
        return $this;
    }

    public function __toString()
    {
        return $this->service->getName();
    }

    /**
     * Set recalcWithPackage
     *
     * @param bool $recalcWithPackage
     * @return self
     */
    public function setRecalcWithPackage($recalcWithPackage): self
    {
        $this->recalcWithPackage = $recalcWithPackage;
        return $this;
    }

    /**
     * Get recalcWithPackage
     *
     * @return bool $recalcWithPackage
     */
    public function isRecalcWithPackage(): ?bool
    {
        return $this->recalcWithPackage;
    }

    /**
     * includeDeparture set
     *
     * @param bool $includeDeparture
     * @return self
     */
    public function setIncludeDeparture(?bool $includeDeparture): self
    {
        $this->includeDeparture = $includeDeparture;

        return $this;
    }

    /**
     * includeDeparture get
     *
     * @return bool
     */
    public function isIncludeDeparture(): ?bool
    {
        return $this->includeDeparture;
    }

    /**
     * includeArrival set
     *
     * @param bool $includeArrival
     * @return self
     */
    public function setIncludeArrival(?bool $includeArrival): self
    {
        $this->includeArrival = $includeArrival;

        return $this;
    }

    /**
     * includeArrival get
     *
     * @return bool
     */
    public function isIncludeArrival(): ?bool
    {
        return $this->includeArrival;
    }
}
