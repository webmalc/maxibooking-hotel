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

    /**
     * @var Tariff[]|ArrayCollection
     * @ODM\EmbedMany(targetDocument="Tariff")
     */
    protected $tariffs;

    /**
     * @ODM\Field(type="bool")
     * @var bool
     */
    private $isRoomLinksPageViewed = false;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->tariffs = new ArrayCollection();
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
        $this->tariffs = new ArrayCollection();

        return $this;
    }

    /**
     * @return ArrayCollection|array|Tariff[]
     */
    public function getTariffs()
    {
        return $this->tariffs;
    }

    public function addTariff(Tariff $tariff)
    {
        $this->tariffs->add($tariff);

        return $this;
    }

    public function removeTariff(Tariff $tariff)
    {
        $this->tariffs->remove($tariff);

        return $this;
    }

    /**
     * @return bool
     */
    public function isRoomLinksPageViewed(): bool
    {
        return $this->isRoomLinksPageViewed;
    }

    /**
     * @param bool $isRoomLinksPageViewed
     * @return AirbnbConfig
     */
    public function setIsRoomLinksPageViewed(bool $isRoomLinksPageViewed): AirbnbConfig
    {
        $this->isRoomLinksPageViewed = $isRoomLinksPageViewed;

        return $this;
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
}