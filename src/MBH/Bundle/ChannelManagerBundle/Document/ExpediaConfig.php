<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ChannelManagerBundle\Lib\CanPullOldOrdersTrait;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use MBH\Bundle\ChannelManagerBundle\Lib\IsConnectionSettingsReadTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface as BaseInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\ChannelManagerBundle\Validator\Constraints as MBHValidator;

/**
 * @ODM\Document(collection="ExpediaConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class ExpediaConfig extends Base implements BaseInterface
{
    public function getName()
    {
        return 'expedia';
    }

    use ConfigTrait;
    use CanPullOldOrdersTrait;
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
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="expediaConfig")
     * @Assert\NotNull(message="validator.document.expediaConfig.no_hotel_selected")
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.expediaConfig.no_hotel_id_specified")
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
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
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
     * Get hotelId
     *
     * @return string $hotelId
     */
    public function getHotelId()
    {
        return $this->hotelId;
    }

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->tariffs = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    /**
     * Add room
     *
     * @param Room $room
     */
    public function addRoom(Room $room)
    {
        $this->rooms[] = $room;
    }

    /**
     * Remove room
     *
     * @param Room $room
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
     * @param Tariff $tariff
     */
    public function addTariff(Tariff $tariff)
    {
        $this->tariffs[] = $tariff;
    }

    /**
     * Remove tariff
     *
     * @param Tariff $tariff
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

    /**
     * @return $this
     */
    public function removeAllServices()
    {
        $this->services = new ArrayCollection();

        return $this;
    }

    /**
     * Add service
     *
     * @param Service $service
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * Remove service
     *
     * @param Service $service
     */
    public function removeService(Service $service)
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

    /**
     * @param bool $checkOldPackages
     * @return bool
     */
    public function isReadyToSync($checkOldPackages = false): bool {
        return $this->isSettingsFilled() && ($checkOldPackages ? $this->isAllPackagesPulled() : true);
    }
}