<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

abstract class AbstractKey implements CacheKeyInterface
{
    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    protected function getSharedPartKey(SearchQuery $searchQuery): string
    {
        $key = '';
        $key .= $searchQuery->getBegin()->format('d.m.Y') . '_' . $searchQuery->getEnd()->format('d.m.Y');
        $key .= '_' . $searchQuery->getRoomTypeId();
        $key .= '_' . $searchQuery->getTariffId();

        return $key;
    }
}