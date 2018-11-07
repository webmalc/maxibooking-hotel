<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\DeterminerFactory;

class ChildrenAgeKey extends AbstractKey
{
    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    public function getKey(SearchQuery $searchQuery): string
    {
        $occupancies = $this->determiner->determine($searchQuery, DeterminerFactory::NO_TRANSFORM_DETERMINER);
        $key = $this->getSharedPartKey($searchQuery);
        $key .= '_' . $occupancies->getAdults();
        $key .= '_' . $occupancies->getChildren();
        $key .= '_' . 'children_ages' . '_' . implode('_', $occupancies->getChildrenAges());

        return $key;
    }

    public function getWarmUpKey(SearchQuery $searchQuery): string
    {
        return $this->getKey($searchQuery);
    }


}