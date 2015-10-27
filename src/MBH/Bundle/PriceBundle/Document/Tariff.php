<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PriceBundle\Validator\Constraints as MBHValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * @ODM\Document(collection="Tariffs", repositoryClass="MBH\Bundle\PriceBundle\Document\TariffRepository")
 * @Gedmo\Loggable
 * @MBHValidator\Tariff
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle", "hotel"}, message="Такой тариф уже существует")
 * @ODM\HasLifecycleCallbacks
 */
class Tariff extends Base
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
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="tariffs")
     * @Assert\NotNull(message="Не выбран отель")
     */
    protected $hotel;
    
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
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isDefault")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isDefault = false;
    
    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isOnline")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isOnline = true;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="begin")
     * @Assert\Date()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="end")
     * @Assert\Date()
     */
    protected $end;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=18)
     */
    protected $childAge = 7;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=18)
     */
    protected $infantAge = 2;

    /**
     * @var Promotion[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="Promotion")
     */
    protected $promotions;

    /**
     * @ODM\ReferenceOne(targetDocument="Promotion")
     * @var Promotion|null
     */
    protected $defaultPromotion;

    /**
     * @var Service[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="Service")
     */
    protected $services;

    /**
     * @var TariffService[]
     * @ODM\EmbedMany(targetDocument="TariffService")
     */
    protected $defaultServices;

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
        $this->defaultServices = new ArrayCollection();
    }

    /**
     * Set hotel
     *
     * @param Hotel $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
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
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return self
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
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
     * Set begin
     *
     * @param date $begin
     * @return self
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * Get begin
     *
     * @return date $begin
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Set end
     *
     * @param date $end
     * @return self
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Get end
     *
     * @return date $end
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set childAge
     *
     * @param int $childAge
     * @return self
     */
    public function setChildAge($childAge)
    {
        $this->childAge = $childAge;
        return $this;
    }

    /**
     * Get childAge
     *
     * @return int $childAge
     */
    public function getChildAge()
    {
        return $this->childAge;
    }

    /**
     * Set infantAge
     *
     * @param int $infantAge
     * @return self
     */
    public function setInfantAge($infantAge)
    {
        $this->infantAge = $infantAge;
        return $this;
    }

    /**
     * Get infantAge
     *
     * @return int $infantAge
     */
    public function getInfantAge()
    {
        return $this->infantAge;
    }

    /**
     * @return ArrayCollection|Promotion[]
     */
    public function getPromotions()
    {
        return $this->promotions;
    }

    /**
     * @param ArrayCollection|Promotion[] $promotions
     */
    public function setPromotions($promotions)
    {
        $this->promotions = $promotions;
    }

    /**
     * @return Promotion|null
     */
    public function getDefaultPromotion()
    {
        return $this->defaultPromotion;
    }

    /**
     * @param Promotion|null $defaultPromotion
     */
    public function setDefaultPromotion(Promotion $defaultPromotion = null)
    {
        $this->defaultPromotion = $defaultPromotion;
    }

    /**
     * @return ArrayCollection|Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param ArrayCollection|Service[] $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * @return TariffService[]
     */
    public function getDefaultServices()
    {
        return $this->defaultServices;
    }

    /**
     * @param TariffService[] $defaultServices
     */
    public function setDefaultServices($defaultServices)
    {
        $this->defaultServices = $defaultServices;
    }
}
