<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\VegaBundle\Document\VegaRegion;

/**
 * @ODM\EmbeddedDocument
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class AddressObjectDecomposed
{
    /**
     * @var String
     * @ODM\String
     */
    protected $country;
    /**
     * @var String
     * @ODM\String
     */
    protected $city;
    /**
     * @var String
     * @ODM\String
     */
    protected $zipCode;
    /**
     * @var VegaRegion
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\VegaBundle\Document\VegaRegion")
     */
    protected $district;

    /**
     * @var String
     * @ODM\String
     */
    protected $region;

    /**
     * Населенный пункт
     * @var String
     * @ODM\String
     */
    protected $settlement;
    /**
     * @var String
     * @ODM\String
     */
    protected $urbanArea;
    /**
     * @var String
     * @ODM\String
     */
    protected $street;

    /**
     * @var String
     * @ODM\String
     */
    protected $corpus;

    /**
     * @var String
     * @ODM\String
     */
    protected $house;

    /**
     * @var String
     * @ODM\String
     */
    protected $flat;

    /**
     * @return String
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param String $country
     */
    public function setCountry($country)
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
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param String $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return VegaRegion
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param VegaRegion $district
     */
    public function setDistrict(VegaRegion $district = null)
    {
        $this->district = $district;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return String
     */
    public function getSettlement()
    {
        return $this->settlement;
    }

    /**
     * @param String $settlement
     */
    public function setSettlement($settlement)
    {
        $this->settlement = $settlement;
    }

    /**
     * @return String
     */
    public function getUrbanArea()
    {
        return $this->urbanArea;
    }

    /**
     * @param String $urbanArea
     */
    public function setUrbanArea($urbanArea)
    {
        $this->urbanArea = $urbanArea;
    }

    /**
     * @return String
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param String $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return String
     */
    public function getCorpus()
    {
        return $this->corpus;
    }

    /**
     * @param String $corpus
     */
    public function setCorpus($corpus)
    {
        $this->corpus = $corpus;
    }

    /**
     * @return String
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param String $house
     */
    public function setHouse($house)
    {
        $this->house = $house;
    }

    /**
     * @return String
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param String $flat
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;
    }

    public function __toString()
    {
        return $this->getRegion().' '. $this->getCity().' ул.'. $this->getStreet().' д.'. $this->getHouse().' кор.'. $this->getCorpus();
    }
}