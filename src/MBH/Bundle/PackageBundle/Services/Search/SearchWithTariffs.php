<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;


/**
 *  Search with tariffs service
 */
class SearchWithTariffs implements SearchInterface
{

    /**
     * @var SearchInterface
     */
    protected $search = null;

    /**
     * @param SearchInterface $search
     * @return $this
     */
    public function setSearch(SearchInterface $search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @param SearchQuery $query
     * @return array
     * @throws Exception
     */
    public function search(SearchQuery $query)
    {

        if (!$this->search) {
            throw new Exception('SearchInterface $search is null.');
        }

        $results = $groupedResult = [];
        $tariffs = $this->searchTariffs($query);

        foreach ($tariffs as $tariff) {
            $query->tariff = $tariff;
            $results = array_merge($results, $this->search->search($query));
        }

        // Group results by roomTypes
        foreach($results as $row) {
            if (!isset($groupedResult[$row->getRoomType()->getId()])) {
                $groupedResult[$row->getRoomType()->getId()] = [
                    'roomType' => $row->getRoomType(),
                    'results' => []
                ];
            }
            $groupedResult[$row->getRoomType()->getId()]['results'][] = $row;
        }


        return $groupedResult;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query)
    {
        return $this->search->searchTariffs($query);
    }
}
