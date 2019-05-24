<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\InternableDocument;
use MBH\Bundle\BaseBundle\Service\Helper;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Service", repositoryClass="ServiceRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @ODM\HasLifecycleCallbacks
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
    use InternableDocument;
    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="ServiceCategory", inversedBy="services")
     * @Assert\NotNull(message="mbhpricebundle.document.service.ne.vybrana.kategoriya")
     */
    protected $category;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Gedmo\Translatable
     * @Assert\Length(
     *      min=2,
     *      minMessage="mbhpricebundle.document.so_short_name",
     *      max=100,
     *      maxMessage="mbhpricebundle.document.name_is_too_long"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="mbhpricebundle.document.so_short_name",
     *      max=100,
     *      maxMessage="mbhpricebundle.document.name_is_too_long"
     * )
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="mbhpricebundle.document.so_short_description",
     *      max=300,
     *      maxMessage="mbhpricebundle.document.description_is_too_long"
     * )
     */
    protected $description;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float", name="price")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="mbhpricebundle.document.price_can_not_be_less_than_zero"
     * )
     */
    protected $price = 0;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="mbhpricebundle.document.price_can_not_be_less_than_zero"
     * )
     */
    protected $innerPrice;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    protected $includeInAccommodationPrice = false;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    protected $subtractFromAccommodationPrice = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isOnline = true;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Choice(choices = {"per_stay", "per_night", "not_applicable", "day_percent"})
     */
    protected $calcType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="code")
     */
    protected $code;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $system = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $recalcWithPackage = false;

    /**
     * @var bool
     * @Gedmo\Versioned()
     * @ODM\Field(type="boolean")
     */
    protected $recalcCausedByTouristsNumberChange = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $date = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $time = false;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    private $includeArrival;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    private $includeDeparture;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @return mixed
     */
    public function getInnerPrice()
    {
        return $this->innerPrice;
    }

    /**
     * @param mixed $innerPrice
     * @return Service
     */
    public function setInnerPrice($innerPrice)
    {
        $this->innerPrice = $innerPrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function subtractFromAccommodationPrice(): ?bool
    {
        return $this->subtractFromAccommodationPrice;
    }

    /**
     * @param bool $subtractFromAccommodationPrice
     * @return Service
     */
    public function setSubtractFromAccommodationPrice(bool $subtractFromAccommodationPrice): Service
    {
        $this->subtractFromAccommodationPrice = $subtractFromAccommodationPrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludeInAccommodationPrice(): ?bool
    {
        return $this->includeInAccommodationPrice;
    }

    /**
     * @param bool $includeInAccommodationPrice
     * @return Service
     */
    public function setIncludeInAccommodationPrice(bool $includeInAccommodationPrice): Service
    {
        $this->includeInAccommodationPrice = $includeInAccommodationPrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRecalcCausedByTouristsNumberChange(): ?bool
    {
        return $this->recalcCausedByTouristsNumberChange;
    }

    /**
     * @param bool $recalcCausedByTouristsNumberChange
     * @return Service
     */
    public function setRecalcCausedByTouristsNumberChange(bool $recalcCausedByTouristsNumberChange): Service
    {
        $this->recalcCausedByTouristsNumberChange = $recalcCausedByTouristsNumberChange;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return Service
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

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

    /**
     * Set isOnline
     *
     * @param boolean $isOnline
     * @return self
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;
        return $this;
    }

    /**
     * Get isOnline
     *
     * @return boolean $isOnline
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * @return \MBH\Bundle\HotelBundle\Document\Hotel
     */
    public function getHotel()
    {
        return $this->getCategory()->getHotel();
    }

    /**
     * Set code
     *
     * @param string $code
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode()
    {
        return $this->code;
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
     * Set calcType
     *
     * @param string $calcType
     * @return self
     */
    public function setCalcType($calcType)
    {
        $this->calcType = $calcType;
        return $this;
    }

    /**
     * Get calcType
     *
     * @return string $calcType
     */
    public function getCalcType()
    {
        return $this->calcType;
    }

    /**
     * Set date
     *
     * @param boolean $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return boolean $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set time
     *
     * @param boolean $time
     * @return self
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * Get time
     *
     * @return boolean $time
     */
    public function getTime()
    {
        return $this->time;
    }


    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return self
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return boolean $isEnabled
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }


    /**
     * Set updatedBy
     *
     * @param string $updatedBy
     * @return self
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return string $updatedBy
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }


    /**
     * @ODM\PreUpdate()
     */
    public function preUpdate()
    {
        if (!$this->internationalTitle && $this->fullTitle) {
            $this->internationalTitle = Helper::translateToLat($this->fullTitle);
        }
    }

    /**
     * Set recalcWithPackage
     *
     * @param bool $recalcWithPackage
     * @return self
     */
    public function setRecalcWithPackage($recalcWithPackage): self
    {
        $this->recalcWithPackage = $recalcWithPackage;
        return $this;
    }

    /**
     * Get recalcWithPackage
     *
     * @return bool $recalcWithPackage
     */
    public function isRecalcWithPackage()
    {
        return $this->recalcWithPackage;
    }

    /**
     * includeDeparture set
     *
     * @param bool $includeDeparture
     * @return self
     */
    public function setIncludeDeparture(bool $includeDeparture): self
    {
        $this->includeDeparture = $includeDeparture;

        return $this;
    }

    /**
     * includeDeparture get
     *
     * @return bool
     */
    public function isIncludeDeparture()
    {
        if ($this->getCalcType() == 'per_stay') {
            return $this->includeDeparture;
        }

        return true;
    }

    /**
     * includeArrival set
     *
     * @param bool $includeArrival
     * @return self
     */
    public function setIncludeArrival(bool $includeArrival): self
    {
        $this->includeArrival = $includeArrival;

        return $this;
    }

    /**
     * includeArrival get
     *
     * @return bool
     */
    public function isIncludeArrival()
    {
        if ($this->getCalcType() == 'per_stay') {
            return $this->includeArrival;
        }

        return true;
    }

    /**
     * @param bool $isFull
     * @return array
     */
    public function getJsonSerialized($isFull = false)
    {
        $data = [
            'id' => $this->getId(),
            'title' => $this->getName(),
        ];

        return $data;
    }
}
