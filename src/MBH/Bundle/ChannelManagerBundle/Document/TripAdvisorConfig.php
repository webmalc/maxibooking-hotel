<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\HotelBundle\Document\Hotel;
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
     */
    protected $paymentType;

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
     * @return mixed
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
}