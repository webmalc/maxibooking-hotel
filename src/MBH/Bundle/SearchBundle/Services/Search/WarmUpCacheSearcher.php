<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

/**
 * Class WarmUpCacheSearcher
 * @package MBH\Bundle\SearchBundle\Services\Search
 * @property
 */
class WarmUpCacheSearcher extends AbstractCacheSearcher
{
    /**
     * @param SearchQuery $searchQuery
     * @param string|null $resultBuilderType
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function search(SearchQuery $searchQuery): void
    {
        $result = $this->searcher->search($searchQuery);
        $this->cache->saveToCache($result, $searchQuery);
    }

}