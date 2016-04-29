<?php

namespace MBH\Bundle\WarehouseBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Organization;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;


/**
 * @ODM\Document(collection="WareInvoices")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Invoice extends Base
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
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     * @Assert\Choice(
     *      choices = {"in", "out"},
     *      message = "validator.warehouse.record.wrongOperation"
     * )
     */
    protected $operation;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="docNumber")
     * @Assert\Length(
     *      min=1,
     *      minMessage="validator.tooshortname",
     *      max=100,
     *      maxMessage="validator.toolongname"
     * )
     */
    protected $docNumber;    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $invoiceDate;    
    /**
     * @var Organization
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Organization")
     * @Assert\NotNull()
     */
    protected $organization;
    /**
     * @var Hotel
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    protected $hotel;
    /** 
	 * @ODM\ReferenceMany(targetDocument="Record", mappedBy="invoice", cascade={"persist", "remove"}) 
	 */
    protected $records;
    
	
    public function __construct() {
        $this->records = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * @return string
     */
    public function __toString() {
        return $this->docNumber;
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
     * @return String
     */
    public function getDocNumber() {
        return $this->docNumber;
    }

    /**
     * @param String
     * @return self
     */
    public function setDocNumber($doc) {
        $this->docNumber = $doc;
        return $this;
    }

	/**
     * @return \DateTime
     */
    public function getInvoiceDate() {
        return $this->invoiceDate;
    }

    /**
     * @param \DateTime $param
     * @return self
     */
    public function setInvoiceDate(\DateTime $param) {
        $this->invoiceDate = $param;
        return $this;
    }

    /**
     * @return Organization
     */
    public function getOrganization() {
        return $this->organization;
    }

    /**
     * @param Organization $param
     * @return self
     */
    public function setOrganization(Organization $param) {
        $this->organization = $param;
        return $this;
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
     * Get records
     *
     * @return ArrayCollection $records
     */
    public function getRecords() {
        return $this->records;
    }

    /**
     * Add a record
     *
     * @param Record $record
     */
    public function addRecord(Record $record) {
		$record->setInvoice($this);
		
        $this->records->add($record);
    }

    /**
     * Remove a record
     *
     * @param Record $param
     */
    public function removeRecord(Record $param) {
        $this->records->removeElement($param);
    }

}
