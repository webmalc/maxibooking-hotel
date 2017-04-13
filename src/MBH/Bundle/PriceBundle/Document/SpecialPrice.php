<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument()
 * Class SpecialPrice
 * @package MBH\Bundle\PriceBundle\Services
 */
class SpecialPrice
{
    /**
     * @var Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     */
    protected $tariff;

    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    protected $roomType;

    /**
     * @var array
     * @ODM\Field(type="hash")
     */
    protected $prices;

    /**
     * @var array
     * @ODM\Field(type="hash")
     */
    protected $pricesByDay;



    /**
     * @return Tariff
     */
    public function getTariff(): ?Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return SpecialPrice
     */
    public function setTariff(Tariff $tariff): SpecialPrice
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): ?RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return SpecialPrice
     */
    public function setRoomType(RoomType $roomType): SpecialPrice
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return array
     */
    public function getPrices(): ?array
    {
        return $this->prices;
    }

    /**
     * @param array $prices
     * @return $this
     */
    public function setPrices(array $prices)
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * @return array
     */
    public function getPricesByDay(): ?array
    {
        return $this->pricesByDay;
    }

    /**
     * @param array $pricesByDay
     * @return $this
     */
    public function setPricesByDay(array $pricesByDay)
    {
        $this->pricesByDay = $pricesByDay;

        return $this;
    }

    public function getNewPrice()
    {
        $prices = $this->getPrices();

    }


}