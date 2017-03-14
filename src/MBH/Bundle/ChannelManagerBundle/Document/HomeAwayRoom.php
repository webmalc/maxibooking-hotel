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
    public function getRoomType() : RoomType
    {
        return $this->roomType;
    }

    /**
     * @return string
     */
    public function getRentalAgreement(): string
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

}
