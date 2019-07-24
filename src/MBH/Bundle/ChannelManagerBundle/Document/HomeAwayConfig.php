<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use MBH\Bundle\ChannelManagerBundle\Lib\IsConnectionSettingsReadTrait;
use MBH\Bundle\ChannelManagerBundle\Services\HomeAway\HomeAway;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="HomeAwayConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class HomeAwayConfig extends Base implements ChannelManagerConfigInterface
{
    use ConfigTrait;
    use IsConnectionSettingsReadTrait;
    use SoftDeleteableDocument;
    use BlameableDocument;
    use TimestampableDocument;

    /**
     * @var Hotel
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="homeAwayConfig")
     * @Assert\NotNull(message="document.homeAwayConfig.no_hotel_selected")
     * @ODM\Index()
     */
    protected $hotel;

    /**
     * @var array|HomeAwayRoom[]
     * @ODM\EmbedMany(targetDocument="HomeAwayRoom")
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
    protected $isRoomLinksPageViewed = false;


    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->tariffs = new ArrayCollection();
    }

    /**
     * @param RoomType $roomType
     * @return HomeAwayRoom|mixed|null
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
     * @return string
     */
    public function getName(): string
    {
        return HomeAway::NAME;
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
     * @return HomeAwayConfig
     */
    public function setHotel(Hotel $hotel): HomeAwayConfig
    {
        $this->hotel = $hotel;

        return $this;
    }

    public function getHotelId()
    {
    }

    public function setHotelId($hotelId)
    {
    }

    public function removeAllRooms(): void
    {
        $this->rooms = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|array|HomeAwayRoom[]
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @param Room $room
     * @return HomeAwayConfig
     */
    public function addRoom(Room $room): HomeAwayConfig
    {
        $this->rooms->add($room);

        return $this;
    }

    /**
     * @param Room $room
     * @return $this
     */
    public function removeRoom(Room $room)
    {
        $this->rooms->remove($room);

        return $this;
    }

    /**
     * @return HomeAwayConfig
     */
    public function removeAllTariffs(): HomeAwayConfig
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

    /**
     * @param Tariff $tariff
     * @return HomeAwayConfig
     */
    public function addTariff(Tariff $tariff): HomeAwayConfig
    {
        $this->tariffs->add($tariff);

        return $this;
    }

    /**
     * @param Tariff $tariff
     * @return HomeAwayConfig
     */
    public function removeTariff(Tariff $tariff): HomeAwayConfig
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
     * @return HomeAwayConfig
     */
    public function setIsRoomLinksPageViewed(bool $isRoomLinksPageViewed): HomeAwayConfig
    {
        $this->isRoomLinksPageViewed = $isRoomLinksPageViewed;

        return $this;
    }
}
