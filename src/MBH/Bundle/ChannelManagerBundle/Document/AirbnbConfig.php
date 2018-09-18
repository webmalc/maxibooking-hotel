<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use MBH\Bundle\ChannelManagerBundle\Lib\IsConnectionSettingsReadTrait;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * @ODM\Document(collection="AirbnbConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class AirbnbConfig extends Base implements ChannelManagerConfigInterface
{
    use ConfigTrait;
    use IsConnectionSettingsReadTrait;
    use SoftDeleteableDocument;
    use BlameableDocument;
    use TimestampableDocument;

    /**
     * @var Hotel
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="bookingConfig")
     * @Assert\NotNull(message="document.bookingConfig.no_hotel_selected")
     * @ODM\Index()
     */
    protected $hotel;

    /**
     * @var array|BookingRoom[]
     * @ODM\EmbedMany(targetDocument="AirbnbRoom")
     */
    protected $rooms;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
    }

    public function getName()
    {
        return Airbnb::NAME;
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
     * @return AirbnbConfig
     */
    public function setHotel(Hotel $hotel): AirbnbConfig
    {
        $this->hotel = $hotel;

        return $this;
    }

    public function getHotelId()
    {
        // TODO: Implement getHotelId() method.
    }

    public function setHotelId($hotelId)
    {
        // TODO: Implement setHotelId() method.
    }

    public function removeAllRooms()
    {
        $this->rooms = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|array|AirbnbRoom[]
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    public function addRoom(Room $room)
    {
        $this->rooms->add($room);

        return $this;
    }

    public function removeRoom(Room $room)
    {
        $this->rooms->remove($room);

        return $this;
    }

    public function removeAllTariffs()
    {
        return $this;
    }

    /**
     * @return ArrayCollection|array|Tariff[]
     */
    public function getTariffs()
    {
        return new ArrayCollection();
    }

    public function addTariff(Tariff $tariff)
    {
        return $this;
    }

    public function removeTariff(Tariff $tariff)
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isMainSettingsFilled()
    {
        return $this->getIsEnabled();
    }

    /**
     * @param RoomType $roomType
     * @return AirbnbRoom|mixed|null
     */
    public function getSyncRoomByRoomType(RoomType $roomType)
    {
        foreach ($this->getRooms() as $room) {
            if ($roomType === $room->getRoomType()) {
                return $room;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    protected function isSettingsFilled()
    {
        return $this->getIsEnabled()
            && !$this->getRooms()->isEmpty()
            && $this->isConfirmedWithDataWarnings();
    }
}