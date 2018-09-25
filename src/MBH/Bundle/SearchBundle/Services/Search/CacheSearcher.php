<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\SearchCacheInterface;

class CacheSearcher extends AbstractCacheSearcher
{

    /**
     * @param SearchQuery $searchQuery
     * @return Result|array
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function search(SearchQuery $searchQuery)
    {
        $result = $this->cache->searchInCache($searchQuery);
        if (null !== $result) {
            if ($result instanceof Result) {
                $result->setCached(true);
            } else {
                $result['cached'] = true;
            }

            return $result;
        }
        $result = $this->searcher->search($searchQuery);
        $this->cache->saveToCache($result, $searchQuery);

        return $result;
    }


}