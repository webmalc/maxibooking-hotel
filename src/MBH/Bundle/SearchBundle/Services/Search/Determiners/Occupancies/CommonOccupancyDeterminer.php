<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyDeterminerInterface;

class CommonOccupancyDeterminer extends AbstractDeterminer implements OccupancyDeterminerInterface
{

    protected function getActualAdults(int $adults, int $childAge, array $actualChildrenAges): int
    {
        $actualAdultAges = array_filter(
            $actualChildrenAges,
            function ($age) use ($childAge) {
                return $age > $childAge;
            }
        );

        return $adults + \count($actualAdultAges);
    }

    protected function getActualChildrenAges(?int $infantAge, int $maxInfants, array $childrenAges): array
    {
        $founded = 0;
        array_walk($childrenAges, function (&$age) use ($infantAge, $maxInfants, &$founded) {

            if (null !== $infantAge && $age <= $infantAge) {
                $founded++;
                if ($founded > $maxInfants) {
                    $age = $infantAge + 1;
                }
            }
        });

        return $childrenAges;
    }


    protected function getActualChildren(int $childAge, ?int $infantAge, array $actualChildrenAges): int
    {
        $childrenByAge = array_filter(
            $actualChildrenAges,
            function ($age) use ($infantAge, $childAge) {
                return (null === $infantAge) || ($age > $infantAge && $age <= $childAge);
            }
        );

        return \count($childrenByAge);
    }

    protected function getActualInfants(?int $infantAge, array $actualChildrenAges): int
    {
        $infants = array_filter(
            $actualChildrenAges,
            function ($age) use ($infantAge) {
                return (null !== $infantAge) && $age <= $infantAge;
            }
        );

        return \count($infants);
    }


}