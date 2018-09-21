<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class NoChildrenAgeKey extends AbstractKey
{
    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    public function getKey(SearchQuery $searchQuery): string
    {
        $key = $this->getSharedPartKey($searchQuery);

        $key .= '_' . $searchQuery->getActualAdults();
        $key .= '_' . $searchQuery->getActualChildren();

        return $key;
    }


}