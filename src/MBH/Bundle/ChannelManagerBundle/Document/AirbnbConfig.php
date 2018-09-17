<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use MBH\Bundle\ChannelManagerBundle\Lib\IsConnectionSettingsReadTrait;
use MBH\Bundle\HotelBundle\Document\Hotel;

class AirbnbConfig extends Base implements ChannelManagerConfigInterface
{
    use ConfigTrait;
    use IsConnectionSettingsReadTrait;

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
     * @return ArrayCollection|array|Room[]
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
}