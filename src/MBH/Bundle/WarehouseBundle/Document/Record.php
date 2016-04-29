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
use Gedmo\Blameable\Traits\BlameableDocument;


/**
 * @ODM\Document(collection="WareRecords")
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
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Invoice", inversedBy="records")
     * @Assert\NotNull(message="validator.warehouse.cat.notchosen")
     */
    protected $invoice;
    /**
     * @var WareItem
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\WarehouseBundle\Document\WareItem")
     * @Assert\NotNull()
     */
    protected $wareItem;
    /**
     * @var float
     * @ODM\Float(name="price")
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
     * @ODM\Float(name="qtty")
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
     * @ODM\Float(name="amount")
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
        return $this->wareItem->getName();
    }

    /**
     * Set invoice
     *
     * @param Invoice $param
     * @return self
     */
    public function setInvoice(Invoice $param) {
        $this->invoice = $param;
        return $this;
    }

    /**
     * Get invoice
     *
     * @return Invoice $invoice
     */
    public function getInvoice() {
        return $this->invoice;
    }

	/**
     * @return boolean
     */
    public function getIsSystem() {
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
