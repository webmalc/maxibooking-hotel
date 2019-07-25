<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;

/**
 * Interface SharedDataFetcherInterface
 * @package MBH\Bundle\SearchBundle\Services\Data
 */
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


    /**
     * @param string $categoryId
     * @return RoomTypeCategory
     */
    public function getFetchedCategory(string $categoryId): RoomTypeCategory;

}