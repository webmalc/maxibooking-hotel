<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\City;
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
     * Set roomType
     *
     * @param RoomType $roomType
     * @return HomeAwayRoom
     */
    public function setRoomType(RoomType $roomType) : HomeAwayRoom
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * Get roomType
     *
     * @return RoomType $roomType
     */
    public function getRoomType() : ?RoomType
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
}
