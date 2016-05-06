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
class RecordFilter extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;
	
    /**
     * @var \DateTime
     * @ODM\Date()
     * @Assert\Date()
     */
    protected $recordDateFrom;    
    /**
     * @var \DateTime
     * @ODM\Date()
     * @Assert\Date()
     */
    protected $recordDateTo;    
    /**
     * @var string
     * @ODM\String()
     * @Assert\Choice(
     *      choices = {"in", "out"},
     *      message = "validator.warehouse.record.wrongOperation"
     * )
     */
    protected $operation;
    /**
     * @var Hotel
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    protected $hotel;
    /**
     * @var WareItem
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\WarehouseBundle\Document\WareItem")
     * @Assert\NotNull()
     */
    protected $wareItem;
	protected $search;
	public $sortBy;
	public $sortDirection;



	/**
     * @return string
     */
    public function __toString() {
        return $this->wareItem->getName();
    }

	/**
     * @return \DateTime
     */
    public function getRecordDateFrom() {
        return $this->recordDateFrom;
    }

    /**
     * @param \DateTime $recordDate
     * @return self
     */
    public function setRecordDateFrom(\DateTime $recordDate =  null) {
        $this->recordDateFrom = $recordDate;
		
        return $this;
    }

	/**
     * @return \DateTime
     */
    public function getRecordDateTo() {
        return $this->recordDateTo;
    }

    /**
     * @param \DateTime $recordDate
     * @return self
     */
    public function setRecordDateTo(\DateTime $recordDate = null) {
        $this->recordDateTo = $recordDate;
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

    public function getSearch() {
        return $this->search;
    }

    public function setSearch($param = null) {
        $this->search = $param;
		
        return $this;
    }

}
