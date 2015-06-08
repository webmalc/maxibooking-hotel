<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\VegaBundle\Document\VegaRegion;

/**
 * @ODM\EmbeddedDocument
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class AddressObjectDecomposed {
    /**
     * @var String
     * @ODM\String
     */
    protected $city;
    /**
     * @var VegaRegion
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\VegaBundle\Document\VegaRegion")
     */
    protected $district;
    /**
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
}