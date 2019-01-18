<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;

class WarmUpOccupancyDeterminer extends AbstractDeterminer
{
    public function determine(OccupancyInterface $occupancy, Tariff $tariff, RoomType $roomType): OccupancyInterface
    {
        return $occupancy;
    }


    protected function getActualChildrenAges(int $infantAge, int $maxInfants, array $childrenAges): array
    {
        // TODO: Implement getActualChildrenAges() method.
    }

    protected function getActualAdults(int $adults, int $childAge, array $actualChildrenAges): int
    {
        // TODO: Implement getActualAdults() method.
    }

    protected function getActualChildren(int $childAge, int $infantAge, array $actualChildrenAges): int
    {
        // TODO: Implement getActualChildren() method.
    }

    protected function getActualInfants(int $infantAge, array $actualChildrenAges): int
    {
        // TODO: Implement getActualInfants() method.
    }

}