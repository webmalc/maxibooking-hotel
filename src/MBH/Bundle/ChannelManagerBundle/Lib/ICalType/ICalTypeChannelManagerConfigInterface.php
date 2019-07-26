<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;


use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;

interface ICalTypeChannelManagerConfigInterface extends ChannelManagerConfigInterface
{
    public function isRoomLinksPageViewed(): bool;
    public function setIsRoomLinksPageViewed(bool $isRoomLinksPageViewed): ICalTypeChannelManagerConfigInterface;
    public function getSyncRoomByRoomType(RoomType $roomType);
}
