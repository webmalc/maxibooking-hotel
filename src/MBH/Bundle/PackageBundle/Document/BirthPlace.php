<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\VegaBundle\Document\VegaRegion;
use MBH\Bundle\VegaBundle\Document\VegaState;

/**
 * @ODM\EmbeddedDocument

 */
class BirthPlace
{
    /**
     * @var VegaState
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\VegaBundle\Document\VegaState")
     */
    protected $country;

    /**
     * @var string
     * @ODM\Field(type="string") 
     */
    protected $city;

    /**
     * @var string
     * @ODM\Field(type="string") 
     */
    protected $mainRegion;

    /**
     * @var string
     * @ODM\Field(type="string") 
     */
    protected $district;

    /**
     * @var string
     * @ODM\Field(type="string") 
     */
    protected $settlement;

    /**
     * @return VegaState
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param VegaState $country
     */
    public function setCountry(VegaState $country = null)
    {
        $this->country = $country;
    }
    /**
     * @return String
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param String $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return String
     */
    public function getMainRegion()
    {
        return $this->mainRegion;
    }

    /**
     * @param String $mainRegion
     */
    public function setMainRegion($mainRegion)
    {
        $this->mainRegion = $mainRegion;
    }

    /**
     * @return mixed
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param string $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
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
     */
    public function setSettlement($settlement)
    {
        $this->settlement = $settlement;
    }

    public function __toString()
    {
        return strval($this->getCountry() . ' ' . $this->getCity());
    }
}