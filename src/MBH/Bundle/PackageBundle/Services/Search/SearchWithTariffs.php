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
            $q = clone $query;
            $q->tariff = $tariff;
            $results = array_merge($results, $this->search->search($q));
        }

        // Group results by roomTypes
        foreach($results as $row) {
            $roomType = $row->getRoomType();

            if ($row->isUseCategories()) {
                $roomType = $roomType->getCategory();
            }

            if (!isset($groupedResult[$roomType->getId()])) {
                $groupedResult[$roomType->getId()] = [
                    'roomType' => $roomType,
                    'results' => []
                ];
            }
            $groupedResult[$roomType->getId()]['results'][] = $row;
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
