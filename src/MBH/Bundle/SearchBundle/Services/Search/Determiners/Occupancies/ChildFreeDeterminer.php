<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\DeterminerInterface;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;

class ChildFreeDeterminer implements DeterminerInterface
{
    public function determine(SearchQuery $searchQuery, Tariff $tariff, RoomType $roomType)
    {
        // TODO: Implement determine() method.
    }

}