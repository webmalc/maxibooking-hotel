<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\RoomType;

/**
 * @ODM\EmbeddedDocument
 */
class AirbnbRoom
{
    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    private $roomType;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $syncUrl;

    /**
     * @return RoomType
     */
    public function getRoomType(): ?RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return AirbnbRoom
     */
    public function setRoomType(RoomType $roomType): AirbnbRoom
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSyncUrl(): ?string
    {
        return $this->syncUrl;
    }

    /**
     * @param string $syncUrl
     * @return AirbnbRoom
     */
    public function setSyncUrl(string $syncUrl): AirbnbRoom
    {
        $this->syncUrl = $syncUrl;

        return $this;
    }
}