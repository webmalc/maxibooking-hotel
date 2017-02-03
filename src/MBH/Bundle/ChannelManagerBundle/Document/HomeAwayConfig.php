<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="HomeAwayConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class HomeAwayConfig extends Base implements ChannelManagerConfigInterface
{
    public function getName()
    {
        return 'homeaway';
    }

    use ConfigTrait;

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
     * @ODM\EmbedMany(targetDocument="Room")
     */
    protected $rooms;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="Tariff")
     */
    protected $tariffs;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->tariffs = new ArrayCollection();
    }

    /**
     * @return mixed
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
    public function getHotelId(): string
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
     * Add room
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Room $room
     */
    public function addRoom(Room $room)
    {
        $this->rooms[] = $room;
    }

    /**
     * Remove room
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Room $room
     */
    public function removeRoom(Room $room)
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
     * Add tariff
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff
     */
    public function addTariff(Tariff $tariff)
    {
        $this->tariffs[] = $tariff;
    }

    /**
     * Remove tariff
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff
     */
    public function removeTariff(Tariff $tariff)
    {
        $this->tariffs->removeElement($tariff);
    }

    /**
     * Get tariffs
     *
     * @return \Doctrine\Common\Collections\Collection $tariffs
     */
    public function getTariffs()
    {
        return $this->tariffs;
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
     * @return $this
     */
    public function removeAllTariffs()
    {
        $this->tariffs = new ArrayCollection();

        return $this;
    }
}