<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(collection="RoomCacheOverwrite", repositoryClass="MBH\Bundle\PackageBundle\Document\RoomCacheOverwriteRepository")
 * @Gedmo\Loggable
 */
class RoomCacheOverwrite extends Base
{
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
     * @var \MBH\Bundle\PriceBundle\Document\Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull(message="Не выбран тариф")
     */
    protected $tariff;
    
    /** 
     * @var \MBH\Bundle\HotelBundle\Document\RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull(message="Не выбран тип номера")
     */
    protected $roomType;
    
    /**
     * @var \DateTime
     * @ODM\Date()
     * @Assert\NotNull(message="Дата не указана")
     * @Assert\Date()
     */
    protected $date;

    /**
     * @var int
     * @ODM\Int()
     */
    protected $places;

    /**
     * @var array
     * @ODM\Hash()
     */
    protected $prices = [];

    /**
     * @var array
     * @ODM\Hash()
     */
    protected $foodPrices = [];

    /**
     * Set tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return self
     */
    public function setTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * Get tariff
     *
     * @return \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * Set roomType
     *
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @return self
     */
    public function setRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
    {
        $this->roomType = $roomType;
        return $this;
    }

    /**
     * Get roomType
     *
     * @return \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set places
     *
     * @param int $places
     * @return self
     */
    public function setPlaces($places)
    {
        $this->places = $places;
        return $this;
    }

    /**
     * Get places
     *
     * @return int $places
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Set prices
     *
     * @param array $prices
     * @return self
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
        return $this;
    }

    /**
     * Get prices
     *
     * @return array $prices
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Set foodPrices
     *
     * @param array $foodPrices
     * @return self
     */
    public function setFoodPrices($foodPrices)
    {
        $this->foodPrices = $foodPrices;
        return $this;
    }

    /**
     * Get foodPrices
     *
     * @return array $foodPrices
     */
    public function getFoodPrices()
    {
        return $this->foodPrices;
    }

    /**
     * Get price by type
     * @param string $type price type
     * @return float|null
     */
    public function getPrice($type = 'price')
    {
        $prices = $this->getPrices();
        if (is_array($prices) && isset($prices[$type]) && is_numeric($prices[$type])) {
            return (float) $prices[$type];
        }

        return null;
    }

    /**
     * Get food price by type
     * @param string $type food type
     * @return float|null
     */
    public function getFoodPrice($type)
    {
        $prices = $this->getFoodPrices();
        if (is_array($prices) && isset($prices[$type]) && is_numeric($prices[$type])) {
            return (float) $prices[$type];
        }

        return null;
    }
}
