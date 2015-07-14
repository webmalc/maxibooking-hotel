<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Region")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Region extends Base
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

    /** @ODM\ReferenceMany(targetDocument="City", mappedBy="region") */
    protected $cities;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Country", inversedBy="regions")
     * @Assert\NotNull(message="validator.document.region.country_not_selected")
     */
    protected $country;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.region.min_name",
     *      max=100,
     *      maxMessage="validator.document.region.max_name"
     * )
     */
    protected $title;

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

    public function __construct()
    {
        $this->cities = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add city
     *
     * @param \MBH\Bundle\HotelBundle\Document\City $city
     */
    public function addCity(\MBH\Bundle\HotelBundle\Document\City $city)
    {
        $this->cities[] = $city;
    }

    /**
     * Remove city
     *
     * @param \MBH\Bundle\HotelBundle\Document\City $city
     */
    public function removeCity(\MBH\Bundle\HotelBundle\Document\City $city)
    {
        $this->cities->removeElement($city);
    }

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection $cities
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * Set country
     *
     * @param \MBH\Bundle\HotelBundle\Document\Country $country
     * @return self
     */
    public function setCountry(\MBH\Bundle\HotelBundle\Document\Country $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return \MBH\Bundle\HotelBundle\Document\Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    public function __toString()
    {
        return (string) $this->getTitle();
    }
}
