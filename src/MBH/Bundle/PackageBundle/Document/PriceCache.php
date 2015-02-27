<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class PriceCache 
{   
    /**
     * @var int
     * @ODM\Int()
     */
    protected $adults;
    
    /**
     * @var int
     * @ODM\Int()
     */
    protected $children;
    
    /**
     * @var int
     * @ODM\Int()
     */
    protected $price;

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
        return $this->price;
    }
}
