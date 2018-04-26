<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * @ODM\Document
 * Class SiteConfig
 * @package MBH\Bundle\OnlineBundle\Document
 */
class SiteConfig extends Base
{
    /**
     * @ODM\Field(type="collection")
     * @var array
     */
    private $keyWords = [];

    /**
     * @ODM\Field(type="string")
     * @var string
     */
    private $personalDataPolicies;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $contract;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     * @var array|ArrayCollection|Hotel[]
     */
    private $hotels;

    public function __construct() {
        $this->hotels = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function getKeyWords(): ?array
    {
        return $this->keyWords;
    }

    /**
     * @param array $keyWords
     * @return SiteConfig
     */
    public function setKeyWords(array $keyWords): SiteConfig
    {
        $this->keyWords = $keyWords;

        return $this;
    }

    /**
     * @return string
     */
    public function getPersonalDataPolicies(): ?string
    {
        return $this->personalDataPolicies;
    }

    /**
     * @param string $personalDataPolicies
     * @return SiteConfig
     */
    public function setPersonalDataPolicies(string $personalDataPolicies): SiteConfig
    {
        $this->personalDataPolicies = $personalDataPolicies;

        return $this;
    }

    /**
     * @return string
     */
    public function getContract(): ?string
    {
        return $this->contract;
    }

    /**
     * @param string $contract
     * @return SiteConfig
     */
    public function setContract(string $contract): SiteConfig
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * @return array|ArrayCollection|Hotel[]
     */
    public function getHotels()
    {
        return $this->hotels;
    }

    /**
     * @param array|ArrayCollection|Hotel[] $hotels
     * @return SiteConfig
     */
    public function setHotels($hotels)
    {
        $this->hotels = $hotels;

        return $this;
    }

    /**
     * @param Hotel $hotel
     * @return SiteConfig
     */
    public function addHotel(Hotel $hotel)
    {
        $this->hotels->add($hotel);

        return $this;
    }
}