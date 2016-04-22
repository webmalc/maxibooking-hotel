<?php

namespace MBH\Bundle\WarehouseBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Traits\InternableDocument;
use MBH\Bundle\BaseBundle\Service\Helper;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Wareitems")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @ODM\HasLifecycleCallbacks
 */
class WareItem extends Base
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
    use InternableDocument;
	
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="WareCategory", inversedBy="items")
     * @Assert\NotNull(message="validator.warehouse.cat.notchosen")
     */
    protected $category;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.tooshortname",
     *      max=100,
     *      maxMessage="validator.toolongname"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.tooshortname",
     *      max=100,
     *      maxMessage="validator.toolongname"
     * )
     */
    protected $title;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Float(name="price")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.negativeprice"
     * )
     */
    protected $price = 0;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="unit")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.tooshorttitle",
     *      max=32,
     *      maxMessage="validator.toolongtitle"
     * )
     */
    protected $unit;

    /**
     * @todo rename isSystem
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $system = false;

    /**
     * Set category
     *
     * @param \MBH\Bundle\WarehouseBundle\Document\WareCategory $category
     * @return self
     */
    public function setCategory(\MBH\Bundle\WarehouseBundle\Document\WareCategory $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return \MBH\Bundle\WarehouseBundle\Document\WareCategory $category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set fullTitle
     *
     * @param string $fullTitle
     * @return self
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;
        return $this;
    }

    /**
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set price
     *
     * @param float $price
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
     * @return string
     */
    public function getName()
    {
        if (!empty($this->title)) {
            return $this->title;
        }
        return $this->fullTitle;
    }

    /**
     * Set system
     *
     * @param boolean $system
     * @return self
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * Get system
     *
     * @return boolean $system
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set unit
     *
     * @param string $unit
     * @return self
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * Get unit
     *
     * @return string $unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @ODM\PreUpdate()
     */
    public function preUpdate()
    {
        if(!$this->internationalTitle && $this->fullTitle) {
            $this->internationalTitle = Helper::translateToLat($this->fullTitle);
        }
    }

}
