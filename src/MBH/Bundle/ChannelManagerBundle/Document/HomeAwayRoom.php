<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\HotelBundle\Document\RoomType;

/**
 * @ODM\EmbeddedDocument
 */
class HomeAwayRoom
{
    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isEnabled = false;

    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="\MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull()
     */
    protected $roomType;

    /**
     * @var string
     * @ODM\Field(type="string", name="rental_agreement")
     * @Assert\NotNull()
     */
    protected $rentalAgreement;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Range(min="20")
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    protected $headLine;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getListingTypes")
     */
    protected $listingType;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getRoomBathSubTypes")
     */
    protected $bathSubType;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getBedRoomSubTypes")
     */
    protected $bedRoomSubType;

    /**
     * Set roomType
     *
     * @param RoomType $roomType
     * @return HomeAwayRoom
     */
    public function setRoomType(RoomType $roomType): HomeAwayRoom
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * Get roomType
     *
     * @return RoomType $roomType
     */
    public function getRoomType(): ?RoomType
    {
        return $this->roomType;
    }

    /**
     * @return string
     */
    public function getRentalAgreement(): ?string
    {
        return $this->rentalAgreement;
    }

    /**
     * @param string $rentalAgreement
     * @return HomeAwayRoom
     */
    public function setRentalAgreement(string $rentalAgreement): HomeAwayRoom
    {
        $this->rentalAgreement = $rentalAgreement;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     * @return HomeAwayRoom
     */
    public function setIsEnabled(bool $isEnabled): HomeAwayRoom
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeadLine(): ?string
    {
        return $this->headLine;
    }

    /**
     * @param string $headLine
     * @return HomeAwayRoom
     */
    public function setHeadLine(string $headLine): HomeAwayRoom
    {
        $this->headLine = $headLine;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingType(): ?string
    {
        return $this->listingType;
    }

    /**
     * @param string $listingType
     * @return HomeAwayRoom
     */
    public function setListingType(string $listingType): HomeAwayRoom
    {
        $this->listingType = $listingType;

        return $this;
    }

    /**
     * @return string
     */
    public function getBathSubType(): ?string
    {
        return $this->bathSubType;
    }

    /**
     * @param string $bathSubType
     * @return HomeAwayRoom
     */
    public function setBathSubType(string $bathSubType): HomeAwayRoom
    {
        $this->bathSubType = $bathSubType;
        return $this;
    }

    /**
     * @return string
     */
    public function getBedRoomSubType(): ?string
    {
        return $this->bedRoomSubType;
    }

    /**
     * @param string $bedRoomSubType
     * @return HomeAwayRoom
     */
    public function setBedRoomSubType(string $bedRoomSubType): HomeAwayRoom
    {
        $this->bedRoomSubType = $bedRoomSubType;
        return $this;
    }


    public static function getRoomBathSubTypes()
    {
        return [
            'FULL_BATH',
            'SHOWER_INDOOR_OR_OUTDOOR',
            'HALF_BATH'
        ];
    }

    public static function getListingTypes()
    {
        return [
            'PROPERTY_TYPE_APARTMENT',
            'PROPERTY_TYPE_BARN',
            'PROPERTY_TYPE_BOAT',
            'PROPERTY_TYPE_BUNGALOW',
            'PROPERTY_TYPE_CABIN',
            'PROPERTY_TYPE_CASTLE',
            'PROPERTY_TYPE_CHALET',
            'PROPERTY_TYPE_COTTAGE',
            'PROPERTY_TYPE_FARMHOUSE',
            'PROPERTY_TYPE_HOUSE',
            'PROPERTY_TYPE_HOUSE_BOAT',
            'PROPERTY_TYPE_MOBILE_HOME',
            'PROPERTY_TYPE_VILLA',
            'PROPERTY_TYPE_YACHT'
        ];
    }

    public static function getBedRoomSubTypes()
    {
        return [
            'BEDROOM',
            'LIVING_SLEEPING_COMBO',
            'OTHER_SLEEPING_AREA'
        ];
    }
}
