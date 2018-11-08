<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;

interface OccupancyDeterminerInterface
{
    public function determine(OccupancyInterface $occupancy, Tariff $tariff, RoomType $roomType): OccupancyInterface;

}