<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


class ChildFreeOccupancyDeterminer extends CommonOccupancyDeterminer
{
    protected function getActualChildrenAges(?int $infantAge, int $maxInfants, array $childrenAges): array
    {
        return $childrenAges;
    }

    protected function getActualAdults(int $adults, int $childAge, array $actualChildrenAges): int
    {
        return $adults;
    }

    protected function getActualChildren(int $childAge, ?int $infantAge, array $actualChildrenAges): int
    {
        return parent::getActualChildren($childAge, null, $actualChildrenAges);
    }

    protected function getActualInfants(?int $infantAge, array $actualChildrenAges): int
    {
        return 0;
    }

}