<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="PackageService")
 * @Gedmo\Loggable
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
     * @ODM\Float()
     * @Assert\Type(type="numeric")
     * @Assert\NotNull()
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.packageService.price_less_zero"
     * )
     */
    protected $price;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Float()
     * @Assert\Type(type="numeric")
     */
    protected $total;


    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Float()
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
     * @ODM\Int()
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
     * @ODM\Int()
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
     * @ODM\Int()
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
     * @ODM\Date()
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     * @Assert\DateTime()
     */
    protected $time;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     * @Assert\Date()
     */
    protected $end;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="note")
     */
    protected $note;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $isCustomPrice = false;
    

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
     * Get service
     *
     * @return \MBH\Bundle\PriceBundle\Document\Service $service
     */
    public function getService()
    {
        return $this->service;
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
     * Get package
     *
     * @return \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function getPackage()
    {
        return $this->package;
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
     * @return float $price
     */
    public function getPrice()
    {
        return $this->price;
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
     * Get amount
     *
     * @return int $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getActuallyAmount()
    {
        $type = $this->getService()->getCalcType();
        if($type == 'per_stay') {
            return $this->getAmount() * $this->getPersons();
        }
        if($type == 'per_night') {
            return $this->getPersons() * $this->getNights() * $this->getAmount();
        }
        if($type == 'not_applicable' or $type == 'day_percent') {
            return $this->getAmount();
        }

        throw new \Exception();
    }

    /**
     * @return int
     */
    public function calcTotal()
    {
        if (!empty($this->getTotalOverwrite())) {
            return $this->getTotalOverwrite();
        }

        $price = $this->getPrice() * $this->getAmount();
        
        if ($this->getCalcType() == 'per_night') {
            $price *= $this->getNights();
        }

        if (!in_array($this->getCalcType(), ['not_applicable', 'day_percent'])) {
            $price *= $this->getPersons();
        }

        return $price;
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
     * @return float
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
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
     * Get persons
     *
     * @return int $persons
     */
    public function getPersons()
    {
        return $this->persons;
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
     * Get nights
     *
     * @return int $nights
     */
    public function getNights()
    {
        return $this->nights;
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
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->setDefaults();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->setDefaults();
    }

    public function setDefaults()
    {
        $service  = $this->getService();
        if ($service->getCalcType() == 'per_stay') {
            $this->setNights(1);
        }
        if ($service->getCalcType() == 'not_applicable') {
            $this->setNights(1);
            $this->setPersons(1);
        }
        if (!$this->getBegin() || !$service->getDate()) {
            $this->setBegin($this->getPackage()->getBegin());
        }
        if (!$service->getTime()) {
            $this->setTime(null);
        }
        $nights = clone $this->getBegin();
        $nights->modify('+' . $this->getNights() . ' days');
        $this->setEnd($nights);

        $this->total = $this->calcTotal();
    }
    
    public function getCalcType()
    {
        return $this->getService()->getCalcType();
    }        

    /**
     * Set isCustomPrice
     *
     * @param boolean $isCustomPrice
     * @return self
     */
    public function setIsCustomPrice($isCustomPrice)
    {
        $this->isCustomPrice = $isCustomPrice;
        return $this;
    }

    /**
     * Get isCustomPrice
     *
     * @return boolean $isCustomPrice
     */
    public function getIsCustomPrice()
    {
        return $this->isCustomPrice;
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
     * Set begin
     *
     * @param \DateTime $begin
     * @return self
     */
    public function setBegin(\DateTime $begin = null)
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
     * @param \DateTime $end
     * @return self
     */
    public function setEnd(\DateTime $end = null)
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
     * Set time
     *
     * @param \DateTime $time
     * @return self
     */
    public function setTime(\DateTime $time = null)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime $time
     */
    public function getTime()
    {
        return $this->time;
    }

    public function __toString()
    {
        return $this->service->getName();
    }

}
