<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataQueries;


use DateTime;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\ExtendedDataQueryInterface;

class ExtendedDataQuery implements ExtendedDataQueryInterface
{
    /** @var DateTime */
    private $begin;

    /** @var DateTime */
    private $end;

    /** @var Hotel[] */
    private $hotels = [];

    /** @var RoomType[] */
    private $roomTypes = [];

    /** @var Tariff[] */
    private $tariffs = [];

    /**
     * @return DateTime
     */
    public function getBegin(): DateTime
    {
        return $this->begin;
    }

    /**
     * @param DateTime $begin
     * @return ExtendedDataQuery
     */
    public function setBegin(DateTime $begin): ExtendedDataQuery
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEnd(): DateTime
    {
        return $this->end;
    }

    /**
     * @param DateTime $end
     * @return ExtendedDataQuery
     */
    public function setEnd(DateTime $end): ExtendedDataQuery
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return Hotel[]
     */
    public function getHotels(): iterable
    {
        return $this->hotels;
    }

    /**
     * @param Hotel[] $hotels
     * @return ExtendedDataQuery
     */
    public function setHotels(iterable $hotels): ExtendedDataQuery
    {
        $this->hotels = $hotels;

        return $this;
    }

    /**
     * @return RoomType[]
     */
    public function getRoomTypes(): iterable
    {
        return $this->roomTypes;
    }

    /**
     * @param RoomType[] $roomTypes
     * @return ExtendedDataQuery
     */
    public function setRoomTypes(iterable $roomTypes): ExtendedDataQuery
    {
        $this->roomTypes = $roomTypes;

        return $this;
    }

    /**
     * @return Tariff[]
     */
    public function getTariffs(): iterable
    {
        return $this->tariffs;
    }

    /**
     * @param Tariff[] $tariffs
     * @return ExtendedDataQuery
     */
    public function setTariffs(iterable $tariffs): ExtendedDataQuery
    {
        $this->tariffs = $tariffs;

        return $this;
    }




}