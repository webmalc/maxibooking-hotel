<?php

/**
 * Class, used for search form filling with db data only.
 * No submittance, no validation etc.
 */

namespace MBH\Bundle\WarehouseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use Symfony\Component\Validator\Constraints as Assert;


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
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $recordDateFrom;    
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $recordDateTo;    
    /**
     * @var string
     * @ODM\Field(type="string")
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
	/**
	 * No correspondence to db fields; form entry only
	 * @var string
	 */
	protected $search;
	/**
	 * No correspondence to db fields; table sort usage only
	 * @var string
	 */
	protected $sortBy;
	/**
	 * No correspondence to db fields; table sort usage only
	 * @var int
	 */
	protected $sortDirection;



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

    public function getSortBy() {
        return $this->sortBy;
    }

    public function setSortBy($param = null) {
        $this->sortBy = $param;
		
        return $this;
    }

    public function getSortDirection() {
        return $this->sortDirection;
    }

    public function setSortDirection($param = null) {
        $this->sortDirection = $param;
		
        return $this;
    }

}
