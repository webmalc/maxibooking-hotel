<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\Translatable\Translatable;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Lib\TranslatableInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Country")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Country extends Base implements TranslatableInterface
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

    /** @ODM\ReferenceMany(targetDocument="Region", mappedBy="country") */
    protected $regions;

    /** @ODM\ReferenceMany(targetDocument="City", mappedBy="country") */
    protected $cities;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.country.min_name",
     *      max=100,
     *      maxMessage="validator.document.country.max_name"
     * )
     * @Gedmo\Translatable
     * @ODM\Index()
     */
    protected $title;


    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

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
        $this->regions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add region
     *
     * @param Region $region
     */
    public function addRegion(Region $region)
    {
        $this->regions[] = $region;
    }

    /**
     * Remove region
     *
     * @param Region $region
     */
    public function removeRegion(Region $region)
    {
        $this->regions->removeElement($region);
    }

    /**
     * Get regions
     *
     * @return \Doctrine\Common\Collections\Collection $regions
     */
    public function getRegions()
    {
        return $this->regions;
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

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function __toString()
    {
        return (string) $this->getTitle();
    }
}
