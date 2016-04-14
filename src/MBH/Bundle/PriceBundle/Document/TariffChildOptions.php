<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PriceBundle\Validator\Constraints as MBHValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ODM\EmbeddedDocument
 * @Gedmo\Loggable
 */
class TariffChildOptions
{
    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $inheritPrices = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $inheritRestrictions = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $inheritRooms = true;

    /**
     * @return boolean
     */
    public function isInheritRooms()
    {
        return $this->inheritRooms;
    }

    /**
     * @param boolean $inheritRooms
     * @return TariffChildOptions
     */
    public function setInheritRooms($inheritRooms)
    {
        $this->inheritRooms = $inheritRooms;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isInheritRestrictions()
    {
        return $this->inheritRestrictions;
    }

    /**
     * @param boolean $inheritRestrictions
     * @return TariffChildOptions
     */
    public function setInheritRestrictions($inheritRestrictions)
    {
        $this->inheritRestrictions = $inheritRestrictions;
        
        return $this;
    }

    /**
     * @return boolean
     */
    public function isInheritPrices()
    {
        return $this->inheritPrices;
    }

    /**
     * @param boolean $inheritPrices
     * @return TariffChildOptions
     */
    public function setInheritPrices($inheritPrices)
    {
        $this->inheritPrices = $inheritPrices;

        return $this;
    }
}
