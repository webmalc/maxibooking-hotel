<?php

namespace MBH\Bundle\PriceBundle\Document\Traits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ConditionsTrait
 */
trait ConditionsTrait
{
    /**
     * @ODM\Field(type="string")
     * @var string
     * @Assert\Choice(callback={"MBH\Bundle\PriceBundle\Services\PromotionConditionFactory", "getAvailableConditions"})
     */
    protected $condition;

    /**
     * @ODM\Field(type="integer")
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1")
     */
    protected $conditionQuantity;

    /**
     * @ODM\Field(type="string")
     * @var string
     * @Assert\Choice(callback={"MBH\Bundle\PriceBundle\Services\PromotionConditionFactory", "getAvailableConditions"})
     */
    protected $additionalCondition;

    /**
     * @ODM\Field(type="integer")
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1")
     */
    protected $additionalConditionQuantity;

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param mixed $condition
     * @return Promotion
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return int
     */
    public function getConditionQuantity()
    {
        return $this->conditionQuantity;
    }

    /**
     * @param int $conditionQuantity
     * @return Promotion
     */
    public function setConditionQuantity($conditionQuantity)
    {
        $this->conditionQuantity = $conditionQuantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalCondition()
    {
        return $this->additionalCondition;
    }

    /**
     * @param string $additionalCondition
     * @return ConditionsTrait
     */
    public function setAdditionalCondition($additionalCondition)
    {
        $this->additionalCondition = $additionalCondition;
        return $this;
    }

    /**
     * @return int
     */
    public function getAdditionalConditionQuantity()
    {
        return $this->additionalConditionQuantity;
    }

    /**
     * @param int $additionalConditionQuantity
     * @return ConditionsTrait
     */
    public function setAdditionalConditionQuantity($additionalConditionQuantity)
    {
        $this->additionalConditionQuantity = $additionalConditionQuantity;
        return $this;
    }

}
