<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\PackageBundle\Services\MagicCalculation;
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
        $key .= '_'.'groups_'.$ageGroups;

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


}