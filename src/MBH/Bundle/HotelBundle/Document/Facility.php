<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\LocalizableTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ODM\Document()
 * Class Facility
 * @package MBH\Bundle\HotelBundle\Document
 */
class Facility extends Base
{
    use LocalizableTrait;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $facilityId;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $description;

    /**
     * @var Hotel
     * @ODM\ReferenceOne(targetDocument="Hotel")
     */
    private $hotel;

    /**
     * @return string
     */
    public function getFacilityId(): ?string
    {
        return $this->facilityId;
    }

    /**
     * @param string $facilityId
     * @return Facility
     */
    public function setFacilityId(string $facilityId): Facility
    {
        $this->facilityId = $facilityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Facility
     */
    public function setDescription(string $description): Facility
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return Facility
     */
    public function setHotel(Hotel $hotel): Facility
    {
        $this->hotel = $hotel;

        return $this;
    }
}