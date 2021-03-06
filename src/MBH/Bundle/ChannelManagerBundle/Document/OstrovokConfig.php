<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface as BaseInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use MBH\Bundle\ChannelManagerBundle\Lib\IsConnectionSettingsReadTrait;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="OstrovokConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class OstrovokConfig extends Base implements BaseInterface
{

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="ostrovokConfig")
     * @Assert\NotNull(message="validator.document.ostrovokConfig.no_hotel_selected")
     * @ODM\Index()
     */
    protected $hotel;

    use ConfigTrait;
    use IsConnectionSettingsReadTrait;

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
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.ostrovokConfig.no_hotel_id_specified")
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
    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="Service")
     */
    protected $services;

    public function __construct()
    {
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tariffs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getName()
    {
        return 'ostrovok';
    }

    /**
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * Set hotel
     *
     * @param \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * Get hotelId
     *
     * @return string $hotelId
     */
    public function getHotelId()
    {
        return $this->hotelId;
    }

    /**
     * Set hotelId
     *
     * @param string $hotelId
     * @return self
     */
    public function setHotelId($hotelId)
    {
        $this->hotelId = $hotelId;

        return $this;
    }

    /**
     * Add room
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Room $room
     * @return OstrovokConfig
     */
    public function addRoom(\MBH\Bundle\ChannelManagerBundle\Document\Room $room)
    {
        $this->rooms[] = $room;

        return $this;
    }

    /**
     * Remove room
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Room $room
     */
    public function removeRoom(\MBH\Bundle\ChannelManagerBundle\Document\Room $room)
    {
        $this->rooms->removeElement($room);
    }

    /**
     * Get rooms
     *
     * @return \Doctrine\Common\Collections\Collection|Room[] $rooms
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
    public function addTariff(\MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff)
    {
        $this->tariffs[] = $tariff;

        return $this;
    }

    /**
     * Remove tariff
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff
     */
    public function removeTariff(\MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff)
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
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    /**
     * @return $this
     */
    public function removeAllTariffs()
    {
        $this->tariffs = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    /**
     * @return $this
     */
    public function removeAllServices()
    {
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    /**
     * Add service
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Service $service
     */
    public function addService(\MBH\Bundle\ChannelManagerBundle\Document\Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * Remove service
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Service $service
     */
    public function removeService(\MBH\Bundle\ChannelManagerBundle\Document\Service $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection $services
     */
    public function getServices()
    {
        return $this->services;
    }
}
