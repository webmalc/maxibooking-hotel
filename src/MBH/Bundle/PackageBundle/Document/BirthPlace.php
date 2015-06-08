<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\VegaBundle\Document\VegaRegion;
use MBH\Bundle\VegaBundle\Document\VegaState;

/**
 * @ODM\EmbeddedDocument
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class BirthPlace
{
    /**
     * @var VegaState
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\VegaBundle\Document\VegaState")
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
    protected $mainRegion;

    /**
     * @var VegaRegion
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\VegaBundle\Document\VegaRegion")
     */
    protected $district;

    /**
     * @var string
     * @ODM\String
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
    public function setCountry(VegaState $country)
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
     * @param VegaRegion $district
     */
    public function setDistrict(VegaRegion $district)
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
}