<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="HomeAwayConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class HomeAwayConfig extends Base
{
    public function getName()
    {
        return 'homeaway';
    }


    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;

    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="homeAwayConfig")
     * @Assert\NotNull(message="validator.document.homeawayconfig.no_hotel_selected")
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.homeawayconfig.no_hotel_id_specified")
     */
    protected $hotelId;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="HomeAwayRoom")
     */
    protected $rooms;

    /**
     * @var  Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull(message="validator.document.homeawayconfig.main_tariff_not_specified")
     */
    protected $mainTariff;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
    }

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param mixed $hotel
     * @return HomeAwayConfig
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * @return string
     */
    public function getHotelId()
    {
        return $this->hotelId;
    }

    /**
     * @param string $hotelId
     * @return HomeAwayConfig
     */
    public function setHotelId($hotelId): HomeAwayConfig
    {
        $this->hotelId = $hotelId;

        return $this;
    }

    /**
     * Add channel manager room
     * @param HomeAwayRoom $room
     */
    public function addRoom(HomeAwayRoom $room)
    {
        $this->rooms[] = $room;
    }

    /**
     * Remove room
     *
     * @param HomeAwayRoom $room
     */
    public function removeRoom(HomeAwayRoom $room)
    {
        $this->rooms->removeElement($room);
    }

    /**
     * Get rooms
     *
     * @return \Doctrine\Common\Collections\Collection $rooms
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @return $this
     */
    public function removeAllRooms()
    {
        $this->rooms = new ArrayCollection();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMainTariff()
    {
        return $this->mainTariff;
    }

    /**
     * @param mixed $mainTariff
     */
    public function setMainTariff($mainTariff)
    {
        $this->mainTariff = $mainTariff;
    }

    public function getSyncRoomTypes()
    {

    }
}