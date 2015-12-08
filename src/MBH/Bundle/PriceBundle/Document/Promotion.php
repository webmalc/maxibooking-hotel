<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;


/**
 * @ODM\Document(collection="promotions")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 *
 * @todo add validator
 * @see Package::isDiscountValid
 */
class Promotion extends Base
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

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_name",
     *      max=100,
     *      maxMessage="validator.document.hotel.max_name"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_name",
     *      max=100,
     *      maxMessage="validator.document.hotel.min_name"
     * )
     */
    protected $title;

    /**
     * @ODM\Float()
     * @var int
     * @Assert\Range(min=0)
     */
    protected $discount;

    /**
     * @ODM\Bool()
     * @var bool
     * @Assert\Type(type="boolean")
     */
    protected $isPercentDiscount;

    /**
     * @ODM\Bool()
     * @var bool
     * @Assert\Type(type="boolean")
     */
    protected $isIndividual;

    /**
     * @ODM\String()
     * @var string
     */
    protected $comment;

    /**
     * @ODM\Integer()
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1", max="10")
     */
    protected $freeChildrenQuantity;

    /**
     * @ODM\Integer()
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1", max="10")
     */
    protected $freeAdultsQuantity;

    /**
     * @ODM\String()
     * @var string
     * @Assert\Choice(callback={"MBH\Bundle\PriceBundle\Services\PromotionConditionFactory", "getAvailableConditions"})
     */
    protected $condition;

    /**
     * @ODM\Integer()
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1", max="10")
     */
    protected $conditionQuantity;

    /**
     * @return string
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * @param string $fullTitle
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param int $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return boolean
     */
    public function getIsPercentDiscount()
    {
        return $this->isPercentDiscount;
    }

    /**
     * @param boolean $isPercentDiscount
     */
    public function setIsPercentDiscount($isPercentDiscount)
    {
        $this->isPercentDiscount = $isPercentDiscount;
    }

    /**
     * @return boolean
     */
    public function getIsIndividual()
    {
        return $this->isIndividual;
    }

    /**
     * @param boolean $isIndividual
     */
    public function setIsIndividual($isIndividual)
    {
        $this->isIndividual = $isIndividual;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return int
     */
    public function getFreeChildrenQuantity()
    {
        return $this->freeChildrenQuantity;
    }

    /**
     * @param int $freeChildrenQuantity
     */
    public function setFreeChildrenQuantity($freeChildrenQuantity)
    {
        $this->freeChildrenQuantity = $freeChildrenQuantity;
    }

    /**
     * @return int
     */
    public function getFreeAdultsQuantity()
    {
        return $this->freeAdultsQuantity;
    }

    /**
     * @param int $freeAdultsQuantity
     */
    public function setFreeAdultsQuantity($freeAdultsQuantity)
    {
        $this->freeAdultsQuantity = $freeAdultsQuantity;
    }



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


}