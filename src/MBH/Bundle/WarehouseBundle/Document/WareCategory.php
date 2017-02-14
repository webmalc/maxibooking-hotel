<?php

namespace MBH\Bundle\WarehouseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="WareCategories")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class WareCategory extends Base
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
    

    /** @ODM\ReferenceMany(targetDocument="WareItem", mappedBy="category") */
    private $items;    
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.tooshortname",
     *      max=100,
     *      maxMessage="validator.toolongname"
     * )
     */
    private $fullTitle;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.tooshortname",
     *      max=100,
     *      maxMessage="validator.toolongname"
     * )
     */
    private $title;    
    
    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $system = false;


    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add an item
     *
     * @param WareItem $item
     */
    public function addItem(WareItem $item) {
        $this->items->add($item);
    }

    /**
     * Remove item
     *
     * @param WareItem $item
     */
    public function removeItem(WareItem $item) {
        $this->items->removeElement($item);
    }

    /**
     * Get services
     *
     * @return \MBH\Bundle\WarehouseBundle\Document\WareItem[] $items
     */
    public function getItems()
    {
        return $this->items;
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
}
