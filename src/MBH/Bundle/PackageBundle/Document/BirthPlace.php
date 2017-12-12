<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class BirthPlace
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $countryTld;

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
     * @return string
     */
    public function getCountryTld()
    {
        return $this->countryTld;
    }

    /**
     * @param string $countryTld
     */
    public function setCountryTld(string $countryTld = null)
    {
        $this->countryTld = $countryTld;
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
        return strval($this->getCountryTld() . ' ' . $this->getCity());
    }
}