<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Housing")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"name", "hotel"}, message="Такой корпус уже существует")
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
     * @ODM\Index()
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string") 
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $name;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $internalName;

    /**
     * @var City
     * @ODM\ReferenceOne(targetDocument="City")
     * @ODM\Index()
     */
    protected $city;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $settlement;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string") 
     */
    protected $street;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string") 
     */
    protected $house;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string") 
     */
    protected $corpus;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string") 
     */
    protected $flat;

    /**
     * @var int
     * @ODM\Integer()
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