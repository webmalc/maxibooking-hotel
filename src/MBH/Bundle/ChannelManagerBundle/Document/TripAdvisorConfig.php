<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="TripAdvisorConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class TripAdvisorConfig extends Base
{

    public function getName()
    {
        return 'tripadvisor';
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
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="tripAdvisorConfig")
     * @Assert\NotNull(message="validator.document.trip_advisor_config.no_hotel_selected")
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.trip_advisor_config.no_hotel_id_specified")
     */
    protected $hotelId;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull(message="validator.document.trip_advisor_config.main_tariff_not_specified")
     */
    protected $mainTariff;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $locale;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.trip_advisor_config.hotel_url.not_specified")
     */
    protected $hotelUrl;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    protected $paymentPolicy;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    protected $termsAndConditions;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $paymentType;

    /**
     * @var ArrayCollection
     * @ODM\EmbedMany(targetDocument="TripAdvisorTariff")
     */
    protected $tariffs;

    /**
     * @var ArrayCollection
     * @ODM\EmbedMany(targetDocument="TripAdvisorRoomType")
     */
    protected $rooms;

    public function __construct()
    {
        $this->tariffs = new ArrayCollection();
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
     * @return TripAdvisorConfig
     */
    public function setHotel($hotel)
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
     * @return TripAdvisorConfig
     */
    public function setHotelId(string $hotelId): TripAdvisorConfig
    {
        $this->hotelId = $hotelId;

        return $this;
    }

    /**
     * @return Tariff
     */
    public function getMainTariff()
    {
        return $this->mainTariff;
    }

    /**
     * @param mixed $mainTariff
     * @return TripAdvisorConfig
     */
    public function setMainTariff($mainTariff)
    {
        $this->mainTariff = $mainTariff;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return TripAdvisorConfig
     */
    public function setLocale(string $locale): TripAdvisorConfig
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getHotelUrl(): ?string
    {
        return $this->hotelUrl;
    }

    /**
     * @param string $hotelUrl
     * @return TripAdvisorConfig
     */
    public function setHotelUrl(string $hotelUrl = null): TripAdvisorConfig
    {
        $this->hotelUrl = $hotelUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentPolicy(): ?string
    {
        return $this->paymentPolicy;
    }

    /**
     * @param string $paymentPolicy
     * @return TripAdvisorConfig
     */
    public function setPaymentPolicy(string $paymentPolicy): TripAdvisorConfig
    {
        $this->paymentPolicy = $paymentPolicy;

        return $this;
    }

    /**
     * @return string
     */
    public function getTermsAndConditions(): ?string
    {
        return $this->termsAndConditions;
    }

    /**
     * @param string $termsAndConditions
     * @return TripAdvisorConfig
     */
    public function setTermsAndConditions(string $termsAndConditions): TripAdvisorConfig
    {
        $this->termsAndConditions = $termsAndConditions;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     * @return TripAdvisorConfig
     */
    public function setPaymentType(string $paymentType): TripAdvisorConfig
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTariffs()
    {
        return $this->tariffs;
    }

    /**
     * @param TripAdvisorTariff $tariff
     * @return TripAdvisorConfig
     */
    public function addTariff(TripAdvisorTariff $tariff)
    {
        $this->tariffs->add($tariff);

        return $this;
    }

    /**
     * @param TripAdvisorTariff $tariff
     * @return TripAdvisorConfig
     */
    public function removeTariff(TripAdvisorTariff $tariff)
    {
        $this->tariffs->remove($tariff);

        return $this;
    }

    /**
     * @return TripAdvisorConfig
     */
    public function removeAllTariffs()
    {
        $this->tariffs = new ArrayCollection();

        return $this;
    }

    public function getMBHTariffs()
    {
        $tariffs = [];
        foreach ($this->getTariffs() as $tripAdvisorTariff) {
            /** @var TripAdvisorTariff $tripAdvisorTariff */
            $tariffs[] = $tripAdvisorTariff->getTariff();
        }

        return $tariffs;
    }

    /**
     * @param TripAdvisorRoomType $room
     * @return TripAdvisorConfig
     */
    public function addRoom(TripAdvisorRoomType $room)
    {
        $this->rooms->add($room);

        return $this;
    }

    /**
     * @param TripAdvisorRoomType $room
     * @return TripAdvisorConfig
     */
    public function removeRoom(TripAdvisorRoomType $room)
    {
        $this->rooms->removeElement($room);

        return $this;
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

    public function getTATariffByMBHTariffId($tariffId)
    {
        foreach ($this->getTariffs() as $tripAdvisorTariff) {
            /** @var TripAdvisorTariff $tripAdvisorTariff */
            if ($tripAdvisorTariff->getTariff()->getId() == $tariffId) {
                return $tripAdvisorTariff;
            }
        }

        return null;
    }

    public function getTARoomTypeByMBHRoomTypeId($roomTypeId)
    {
        if (!is_null($this->getRooms())) {
            foreach ($this->getRooms() as $tripAdvisorRoomType) {
                /** @var TripAdvisorRoomType $tripAdvisorRoomType */
                if ($tripAdvisorRoomType->getRoomType()->getId() == $roomTypeId) {
                    return $tripAdvisorRoomType;
                }
            }
        }

        return null;
    }
}