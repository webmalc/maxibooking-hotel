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
 * @ODM\Document(collection="Housing")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Housing extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Hotel")
     * @Assert\NotNull()
     */
    protected $hotel;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     * @Assert\NotNull()
     */
    protected $name;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $internalName;

    /**
     * @var City
     * @ODM\ReferenceOne(targetDocument="City")
     */
    protected $city;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $street;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $house;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $corpus;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $flat;

    /**
     * @var int
     * @ODM\Int
     * @Assert\Type(type="numeric")
     */
    protected $vegaAddressId;

    /**
     * @return mixed
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param mixed $hotel
     */
    public function setHotel($hotel)
    {
        $this->hotel = $hotel;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getInternalName()
    {
        return $this->internalName;
    }

    /**
     * @param mixed $internalName
     */
    public function setInternalName($internalName)
    {
        $this->internalName = $internalName;
    }

    /**
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param City $city
     */
    public function setCity(City $city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param mixed $house
     */
    public function setHouse($house)
    {
        $this->house = $house;
    }

    /**
     * @return mixed
     */
    public function getCorpus()
    {
        return $this->corpus;
    }

    /**
     * @param mixed $corpus
     */
    public function setCorpus($corpus)
    {
        $this->corpus = $corpus;
    }

    /**
     * @return mixed
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param mixed $flat
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;
    }

    public function getAddress()
    {
        return $this->getStreet().' '.$this->getHouse().' '.$this->getCorpus();
    }


    /**
     * @return int
     */
    public function getVegaAddressId()
    {
        return $this->vegaAddressId;
    }

    /**
     * @param int $vegaAddressId
     */
    public function setVegaAddressId($vegaAddressId)
    {
        $this->vegaAddressId = $vegaAddressId;
    }

    public function __toString()
    {
        return $this->getName();
    }
}