<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ChannelManagerBundle\Lib\CanPullOldOrdersTrait;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

/**
 * @ODM\Document(collection="HundredOneHotelsConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class HundredOneHotelsConfig extends Base implements ChannelManagerConfigInterface
{
    use ConfigTrait;
    use CanPullOldOrdersTrait;

    public function getName()
    {
        return 'hundred_one_hotels';
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
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="hundredOneHotelsConfig")
     * @Assert\NotNull(message="validator.document.hundredOneHotelsConfig.no_hotel_selected")
     * @ODM\Index()
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.hundredOneHotelsConfig.no_hotel_id_specified")
     */
    protected $hotelId;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.hundredOneHotelsConfig.no_api_key_specified")
     */
    protected $apiKey;

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
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="Room")
     */
    protected $rooms;

    /**
     * @return mixed
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param mixed $hotel
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }

    /**
     * @return array
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @return array
     */
    public function getTariffs()
    {
        return $this->tariffs;
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
     * Add tariff
     *
     * @param Tariff $tariff
     */
    public function addTariff(Tariff $tariff)
    {
        $this->tariffs[] = $tariff;
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
    public function removeAllRooms()
    {
        $this->rooms = new ArrayCollection();

        return $this;
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

    public function getHotelId()
    {
        return $this->hotelId;
    }

    public function setHotelId($hotelId)
    {
        $this->hotelId = $hotelId;

        return $this;
    }

    /**
     * @param bool $checkOldPackages
     * @return bool
     */
    public function isReadyToSync($checkOldPackages = true): bool {
        return $this->isSettingsFilled() && ($checkOldPackages ? $this->isAllPackagesPulled() : true);
    }
}