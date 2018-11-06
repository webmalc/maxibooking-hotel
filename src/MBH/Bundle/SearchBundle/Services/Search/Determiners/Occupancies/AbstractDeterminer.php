<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\OccupancyDeterminerException;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;

abstract class AbstractDeterminer
{

    public function determine(OccupancyInterface $occupancy, Tariff $tariff, RoomType $roomType): OccupancyInterface
    {
        $adults = $occupancy->getAdults();
        $childrenAges = $occupancy->getChildrenAges();
        $children = $occupancy->getChildren();
        if (\count($childrenAges) !== $children) {
            throw new OccupancyDeterminerException('ChildrenAges and children not the same value!');
        }

        $maxInfants = $roomType->getMaxInfants();
        $infantAge = $tariff->getInfantAge();
        $childAge = $tariff->getChildAge();


        $actualChildrenAges = $this->getActualChildrenAges($infantAge, $maxInfants, $childrenAges);
        $actualAdults = $this->getActualAdults($adults, $childAge, $actualChildrenAges);
        $actualChildren = $this->getActualChildren($childAge, $infantAge, $actualChildrenAges);
        $actualInfants = $this->getActualInfants($infantAge, $actualChildrenAges);

        return new Occupancy($actualAdults, $actualChildren, $actualInfants, $actualChildrenAges);
    }


    abstract protected function getActualChildrenAges(int $infantAge, int $maxInfants, array $childrenAges):array ;

    abstract protected function getActualAdults(int $adults, int $childAge, array $actualChildrenAges): int;

    abstract protected function getActualChildren(int $childAge, int $infantAge, array $actualChildrenAges): int;

    abstract protected function getActualInfants(int $infantAge, array $actualChildrenAges): int;
}