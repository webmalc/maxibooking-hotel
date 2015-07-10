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
     * @var Hotel
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Hotel")
     * @Assert\NotNull()
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     * @Assert\NotNull()
     */
    protected $name;

    /**
     * @var string
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
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $settlement;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $street;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $house;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $corpus;

    /**
     * @var string
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
     * @return self
     */
    public function setHotel($hotel)
    {
        $this->hotel = $hotel;

        return $this;
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
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return self
     */
    public function setInternalName($internalName)
    {
        $this->internalName = $internalName;

        return $this;
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
     * @return self
     */
    public function setCity(City $city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getSettlement()
    {
        return $this->settlement;
    }

    /**
     * @param string $settlement
     * @return self
     */
    public function setSettlement($settlement)
    {
        $this->settlement = $settlement;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return self
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param string $house
     * @return self
     */
    public function setHouse($house)
    {
        $this->house = $house;

        return $this;
    }

    /**
     * @return string
     */
    public function getCorpus()
    {
        return $this->corpus;
    }

    /**
     * @param string $corpus
     * @return self
     */
    public function setCorpus($corpus)
    {
        $this->corpus = $corpus;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param string $flat
     * @return self
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->getStreet() . ' ' . $this->getHouse() . ' ' . $this->getCorpus();
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
     * @return self
     */
    public function setVegaAddressId($vegaAddressId)
    {
        $this->vegaAddressId = $vegaAddressId;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if (!empty($this->internalName)) {
            return $this->internalName;
        }

        return $this->name;
    }
}