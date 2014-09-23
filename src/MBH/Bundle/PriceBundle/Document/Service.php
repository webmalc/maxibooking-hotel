<?php

namespace MBH\Bundle\PriceBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Service")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Service extends Base
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
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="ServiceCategory", inversedBy="services")
     * @Assert\NotNull(message="Не выбрана категория")
     */
    protected $category;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $title;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое описание",
     *      max=300,
     *      maxMessage="Слишком длинное описание"
     * )
     */
    protected $description;

    /**
     * @var int
     * @ODM\Int(name="price")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $price;

    /**
     * Set category
     *
     * @param \MBH\Bundle\PriceBundle\Document\ServiceCategory $category
     * @return self
     */
    public function setCategory(\MBH\Bundle\PriceBundle\Document\ServiceCategory $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return \MBH\Bundle\PriceBundle\Document\ServiceCategory $category
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
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
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
}
