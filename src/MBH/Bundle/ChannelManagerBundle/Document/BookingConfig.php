<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use MBH\Bundle\ChannelManagerBundle\Lib\CurrencyConfigInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface as BaseInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\ChannelManagerBundle\Validator\Constraints as MBHValidator;

/**
 * @ODM\Document(collection="BookingConfig")
 * @Gedmo\Loggable
 * @MBHValidator\Currency
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class BookingConfig extends Base implements BaseInterface, CurrencyConfigInterface
{

    public function getName()
    {
        return 'booking';
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
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="bookingConfig")
     * @Assert\NotNull(message="document.bookingConfig.no_hotel_selected")
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.bookingConfig.no_hotel_id_specified")
     */
    protected $hotelId;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $currency;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     */
    protected $currencyDefaultRatio;

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
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tariffs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add room
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\Room $room
     */
    public function addRoom(\MBH\Bundle\ChannelManagerBundle\Document\Room $room)
    {
        $this->rooms[] = $room;
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
    public function addTariff(\MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff)
    {
        $this->tariffs[] = $tariff;
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

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return self
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return float
     */
    public function getCurrencyDefaultRatio()
    {
        return $this->currencyDefaultRatio;
    }

    /**
     * @param float $currencyDefaultRatio
     * @return self
     */
    public function setCurrencyDefaultRatio($currencyDefaultRatio)
    {
        $this->currencyDefaultRatio = $currencyDefaultRatio;

        return $this;
    }


}
