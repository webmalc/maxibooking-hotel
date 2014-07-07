<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class FoodPrice
{
    /**
     * @var string
     * @ODM\String(name="type")
     * @Assert\NotNull()
     */
    protected $type;
    
    /**
     * @var int
     * @ODM\Int(name="price")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $price;

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
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
