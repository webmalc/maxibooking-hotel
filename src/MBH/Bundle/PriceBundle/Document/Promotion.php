<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\PriceBundle\Document\Traits\ConditionsTrait;
use MBH\Bundle\PriceBundle\Lib\ConditionsInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="promotions")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 *
 * @todo add validator
 * @see Package::isDiscountValid
 */
class Promotion extends Base implements ConditionsInterface
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

    use ConditionsTrait;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
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
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_name",
     *      max=100,
     *      maxMessage="validator.document.hotel.min_name"
     * )
     */
    protected $title;

    /**
     * @ODM\Field(type="float")
     * @var int
     * @Assert\Range(min=-100000000)
     * @Assert\Type(type="numeric")
     */
    protected $discount;

    /**
     * @ODM\Field(type="boolean")
     * @var bool
     * @Assert\Type(type="boolean")
     */
    protected $isPercentDiscount = true;

    /**
     * @ODM\Field(type="boolean")
     * @var bool
     * @Assert\Type(type="boolean")
     */
    protected $isIndividual;

    /**
     * @ODM\Field(type="string")
     * @var string
     */
    protected $comment;

    /**
     * @ODM\Field(type="integer")
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1", max="10")
     */
    protected $freeChildrenQuantity;

    /**
     * @ODM\Field(type="integer")
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1", max="10")
     */
    protected $freeAdultsQuantity;

    /**
     * @ODM\Field(type="integer")
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1", max="100")
     */
    protected $childrenDiscount;

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
     * @return int
     */
    public function getChildrenDiscount()
    {
        return $this->childrenDiscount;
    }

    /**
     * @param int $childrenDiscount
     * @return Promotion
     */
    public function setChildrenDiscount($childrenDiscount)
    {
        $this->childrenDiscount = $childrenDiscount;
        return $this;
    }

    /**
     * @return array
     */
    public function getJsonSerialized()
    {
        return [
            'title' => $this->getFullTitle() ?? $this->getTitle(),
            'discount' => $this->getDiscount(),
            'isInPercents' => $this->getIsPercentDiscount(),
            'isIndividual' => $this->getIsIndividual(),
            'numberOfAdultsFree' => $this->getFreeAdultsQuantity() === null ? 0 : $this->getFreeAdultsQuantity(),
            'numberOfChildrenFree' => $this->getFreeChildrenQuantity() === null ? 0 : $this->getFreeChildrenQuantity(),
            'childrenDiscount' => $this->getChildrenDiscount() === null ? 0 : $this->getChildrenDiscount()
        ];
    }
}
