<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;

interface SharedDataFetcherInterface
{

    /**
     * @param string $tariffId
     * @return Tariff
     * @throws SharedFetcherException
     */
    public function getFetchedTariff(string $tariffId): Tariff;


    /**
     * @param string $roomTypeId
     * @return RoomType
     * @throws SharedFetcherException
     */
    public function getFetchedRoomType(string $roomTypeId): RoomType;

}