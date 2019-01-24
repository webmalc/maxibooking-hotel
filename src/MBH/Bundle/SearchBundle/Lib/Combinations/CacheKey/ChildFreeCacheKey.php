<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\PackageBundle\Services\MagicCalculation;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminerFactory;

class ChildFreeCacheKey extends AbstractKey
{
    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    public function getKey(SearchQuery $searchQuery): string
    {
        $occupancies = $this->determiner->determine($searchQuery, OccupancyDeterminerFactory::CHILD_FREE_TARIFF_DETERMINER);
        $ageGroups = $this->getAgeGroups($occupancies->getChildrenAges());
        $key = $this->getSharedPartKey($searchQuery);
        $key .= '_'.$occupancies->getAdults();
        $key .= '_'.$occupancies->getChildren();
        $key .= '_'.'groups'.$ageGroups;

        return $key;
    }

    public function getWarmUpKey(SearchQuery $searchQuery): string
    {
        return $this->getKey($searchQuery);
    }

    private function getAgeGroups(array $ages): string
    {
        $result = '';
        $groups = MagicCalculation::getAgeGroups($ages);
        foreach ($groups as $key => $groupAges) {
            $result .= '_'. $key.\count($groupAges);
        }

        return $result;
    }

    public function extractWarmUpKey(string $key): array
    {
        [$begin, $end, $roomTypeId, $tariffId, $adults, $children, $group] = explode('_', $key, 7);
        $childrenAges = $this->ageGroupToChildrenAges($group);

        return [
            'begin' => new \DateTime($begin),
            'end' => new \DateTime($end),
            'roomTypeId' => $roomTypeId,
            'tariffId' => $tariffId,
            'combination' => [
                'adults' => $adults,
                'children' => $children,
                'childrenAges' => $childrenAges
            ],
        ];
    }

    private function ageGroupToChildrenAges(string $groups): array
    {
        $childrenAges = [];
        $groups = str_replace('groups_', '', $groups);
        foreach (explode('_', $groups) as $group) {
            $groupType = substr($group, 0, 1);
            $childrenGroupAmount = substr($group, 1);

            for ($i = 1; $i <= $childrenGroupAmount; $i++) {
                if ($groupType === 't') {
                    $age = 7;
                } elseif ($groupType === 'j') {
                    $age = 2;
                }
                if (!isset($age)) {
                    throw new CacheKeyFactoryException('Unknown age group');
                }
                $childrenAges[] = $age;
            }
        }

        return $childrenAges;
    }


}