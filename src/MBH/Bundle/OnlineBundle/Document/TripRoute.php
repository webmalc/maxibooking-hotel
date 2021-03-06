<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument()
 * @Gedmo\Loggable
 * @ODM\HasLifecycleCallbacks
 */
class TripRoute implements \JsonSerializable
{
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $address;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $hotel;

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param string $hotel
     * @return $this
     */
    public function setHotel($hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'hotel' => $this->hotel,
            'address' => $this->address
        ];
    }
}