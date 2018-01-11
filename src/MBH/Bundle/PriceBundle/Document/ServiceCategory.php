<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="ServiceCategory")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class ServiceCategory extends Base
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

    /** @ODM\ReferenceMany(targetDocument="Service", mappedBy="category") */
    protected $services;
    
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="servicesCategories")
     * @Assert\NotNull(message="document.service_category.hotel.hotel_not_chosen")
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="document.service_category.full_title.too_short",
     *      max=100,
     *      maxMessage="document.service_category.full_title.too_long"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="document.service_category.title.too_long",
     *      max=100,
     *      maxMessage="document.service_category.title.too_long"
     * )
     */
    protected $title;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="document.service_category.description.too_short",
     *      max=300,
     *      maxMessage="document.service_category.description.too_long"
     * )
     */
    protected $description;
    
    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $system = false;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }
    
    /**
     * Add service
     *
     * @param \MBH\Bundle\PriceBundle\Document\Service $service
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * Remove service
     *
     * @param \MBH\Bundle\PriceBundle\Document\Service $service
     */
    public function removeService(Service $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \MBH\Bundle\PriceBundle\Document\Service[]|ArrayCollection $services
     */
    public function getServices()
    {
        return $this->services;
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
}
