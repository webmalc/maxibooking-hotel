<?php

namespace MBH\Bundle\WarehouseBundle\Document;

use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;


/**
 * @ODM\Document(collection="WareRecords", repositoryClass="MBH\Bundle\WarehouseBundle\Document\RecordRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Record extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;
	
    /**
     * @var bool
     * @ODM\Boolean()
     */
    protected $isSystem;
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $recordDate;    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Choice(
     *      choices = {"in", "out"},
     *      message = "validator.warehouse.record.wrongOperation"
     * )
     */
    protected $operation;
    /**
     * @var Hotel
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    protected $hotel;
    /**
     * @var WareItem
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\WarehouseBundle\Document\WareItem")
     * @Assert\NotNull()
     */
    protected $wareItem;
    /**
     * @var float
     * @ODM\Field(type="float", name="price")
     * @Assert\Type(type="numeric")
     * @Gedmo\Versioned
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.negativeprice"
     * )
     */
    protected $price;    
    /**
     * @var float
     * @ODM\Field(type="float", name="qtty")
     * @Assert\Type(type="numeric")
     * @Gedmo\Versioned
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.warehouse.record.negative"
     * )
     */
    protected $qtty;	
    /**
     * @var float
     * @ODM\Field(type="float", name="amount")
     * @Assert\Type(type="numeric")
     * @Gedmo\Versioned
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.warehouse.record.negativeSum"
     * )
     */
    protected $amount;
	
    
    /**
     * @return string
     */
    public function __toString() {
        return (string) $this->wareItem;
    }

	/**
     * @return \DateTime
     */
    public function getRecordDate() {
        return $this->recordDate;
    }

    /**
     * @param \DateTime $recordDate
     * @return self
     */
    public function setRecordDate(\DateTime $recordDate) {
        $this->recordDate = $recordDate;
        return $this;
    }

	/**
     * @return boolean
     */
    public function getisSystem() {
        return $this->isSystem;
    }

    /**
     * @param boolean $isSystem
     * @return self
     */
    public function setIsSystem($isSystem) {
        $this->isSystem = $isSystem;
        return $this;
    }

    /**
     * Set operation
     *
     * @param string $operation
     * @return self
     */
    public function setOperation($operation) {
        $this->operation = $operation;

        return $this;
    }

    /**
     * Get operation
     *
     * @return string $operation
     */
    public function getOperation() {
        return $this->operation;
    }

    /**
     * @return Hotel|null
     */
    public function getHotel() {
        return $this->hotel;
    }

    /**
     * @param Hotel|null $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel = null) {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return WareItem|null
     */
    public function getWareItem() {
        return $this->wareItem;
    }

    /**
     * @param WareItem|null $wareItem
     * @return self
     */
    public function setWareItem($wareItem = null) {
        $this->wareItem = $wareItem;
        return $this;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return self
     */
    public function setPrice($price) {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return float $price
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * Set quantity
     *
     * @param float $param
     * @return self
     */
    public function setQtty($param) {
        $this->qtty = $param;
        return $this;
    }

    /**
     * Get quantity
     *
     * @return float $param
     */
    public function getQtty() {
        return $this->qtty;
    }

    /**
     * Set amount.
     *
     * @param float $param
     * @return self
     */
    public function setAmount($param) {
        $this->amount = $param;
        return $this;
    }

    /**
     * Get amount.
     *
     * @return float $param
     */
    public function getAmount() {
        return $this->amount;
    }

}
