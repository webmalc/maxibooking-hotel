<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

/**
 *  Search Interface
 */
interface SearchInterface
{
    /**
     * @param SearchQuery $query
     * @return SearchResult[]
     */
    public function search(SearchQuery $query);


    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query);
}
