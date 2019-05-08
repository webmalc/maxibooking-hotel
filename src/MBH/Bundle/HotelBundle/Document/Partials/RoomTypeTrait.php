<?php

namespace MBH\Bundle\HotelBundle\Document\Partials;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


trait RoomTypeTrait
{

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isChildPrices = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isIndividualAdditionalPrices = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isSinglePlacement = false;

    /**
     * Set isSinglePlacement
     *
     * @param boolean $isSinglePlacement
     * @return self
     */
    public function setIsSinglePlacement($isSinglePlacement)
    {
        $this->isSinglePlacement = $isSinglePlacement;

        return $this;
    }

    /**
     * Get $isSinglePlacement
     *
     * @return boolean $isSinglePlacement
     */
    public function getIsSinglePlacement()
    {
        return $this->isSinglePlacement;
    }

    /**
     * Set isChildPrices
     *
     * @param boolean $isChildPrices
     * @return self
     */
    public function setIsChildPrices($isChildPrices)
    {
        $this->isChildPrices = $isChildPrices;

        return $this;
    }

    /**
     * Get isChildPrices
     *
     * @return boolean $isChildPrices
     */
    public function getIsChildPrices()
    {
        return $this->isChildPrices;
    }

    /**
     * Set isIndividualAdditionalPrices
     *
     * @param boolean $isIndividualAdditionalPrices
     * @return self
     */
    public function setIsIndividualAdditionalPrices($isIndividualAdditionalPrices)
    {
        $this->isIndividualAdditionalPrices = $isIndividualAdditionalPrices;

        return $this;
    }

    /**
     * Get isIndividualAdditionalPrices
     *
     * @return boolean $isIndividualAdditionalPrices
     */
    public function getIsIndividualAdditionalPrices()
    {
        return $this->isIndividualAdditionalPrices;
    }
}
