<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ChildrenAgeKey extends AbstractKey
{
    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    public function getKey(SearchQuery $searchQuery): string
    {
        $key = $this->getSharedPartKey($searchQuery);
        $key .= '_'.$searchQuery->getAdults();
        $key .= '_'.$searchQuery->getChildren();
        $key .= '_'.'children_ages'.'_'.implode('_', $searchQuery->getChildrenAges());

        return $key;
    }

}