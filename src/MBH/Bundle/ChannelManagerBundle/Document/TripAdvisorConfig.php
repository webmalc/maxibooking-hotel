<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\ConfigTrait;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
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
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="bookingConfig")
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
     * @return mixed
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

}