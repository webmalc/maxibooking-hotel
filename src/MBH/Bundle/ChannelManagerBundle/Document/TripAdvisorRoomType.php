<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument()
 * Class TripAdvisorRoomType
 * @package MBH\Bundle\ChannelManagerBundle\Document
 */
class TripAdvisorRoomType
{
    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isEnabled = false;

    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull()
     */
    protected $roomType;

    /**
     * @return bool
     */
    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     * @return TripAdvisorRoomType
     */
    public function setIsEnabled(bool $isEnabled): TripAdvisorRoomType
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): ?RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return TripAdvisorRoomType
     */
    public function setRoomType(RoomType $roomType): TripAdvisorRoomType
    {
        $this->roomType = $roomType;

        return $this;
    }

}