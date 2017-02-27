<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="City")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class City extends Base
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
     * @ODM\ReferenceOne(targetDocument="Country", inversedBy="cities")
     * @Assert\NotNull(message="validator.document.city.country_not_selected")
     * @ODM\Index()
     */
    protected $country;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Region", inversedBy="cities")
     * @Assert\NotNull(message="validator.document.city.region_not_selected")
     * @ODM\Index()
     */
    protected $region;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.city.min_name",
     *      max=100,
     *      maxMessage="validator.document.city.max_name"
     * )
     * @ODM\Index()
     * @Gedmo\Translatable()
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Locale
     */
    protected $locale;

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

    /**
     * Set region
     *
     * @param \MBH\Bundle\HotelBundle\Document\Region $region
     * @return self
     */
    public function setRegion(\MBH\Bundle\HotelBundle\Document\Region $region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * Get region
     *
     * @return \MBH\Bundle\HotelBundle\Document\Region $region
     */
    public function getRegion()
    {
        return $this->region;
    }

    public function __toString()
    {
        return is_string($this->getTitle()) ? $this->getTitle() : parent::__toString();
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
