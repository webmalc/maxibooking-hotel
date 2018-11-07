<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\DeterminerFactory;

class NoChildrenAgeKey extends AbstractKey
{

    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    public function getKey(SearchQuery $searchQuery): string
    {
        $occupancies = $this->determiner->determine($searchQuery, DeterminerFactory::COMMON_DETERMINER);
        $key = $this->getSharedPartKey($searchQuery);
        $key .= '_' . $occupancies->getAdults();
        $key .= '_' . $occupancies->getChildren();

        return $key;
    }

    public function getWarmUpKey(SearchQuery $searchQuery): string
    {
        $key = $this->getSharedPartKey($searchQuery);
        $key .= '_' . $searchQuery->getAdults();
        $key .= '_' . $searchQuery->getChildren();

        return $key;
    }


}