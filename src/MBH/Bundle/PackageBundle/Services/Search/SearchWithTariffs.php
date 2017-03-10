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

        return $this->sort($groupedResult);
    }

    public function sort(array $groupedResult)
    {
        // sort RoomTypes
        usort($groupedResult, function ($prev, $next) {
            if (!isset($prev['results'][0])) {
                return 1;
            }
            if (!isset($next['results'][0])) {
                return -1;
            }

            $getPrice = function (array $results) {
                foreach ($results['results'] as $result) {
                    if ($result->getTariff()->getIsDefault() && isset(array_values($result->getPrices())[0])) {
                        return array_values($result->getPrices())[0];
                    }
                }
                return null;
            };

            $prevPrice = $getPrice($prev);
            $nextPrice = $getPrice($next);

            if ($prevPrice === null) {
                return 1;
            }
            if ($nextPrice === null) {
                return -1;
            }
            if ($prevPrice == $nextPrice) {
                return 0;
            }

            return ($prevPrice < $nextPrice) ? -1 : 1;
        });

        // sort tariffs
        foreach ($groupedResult as $key => $roomTypeData) {
            usort($groupedResult[$key]['results'], function ($prev, $next) {

                $prevFirstPrices = array_values($prev->getPrices());
                $nextFirstPrices = array_values($next->getPrices());

                if (!isset($prevFirstPrices[0])) {
                    return 1;
                }
                if (!isset($nextFirstPrices[0])) {
                    return -1;
                }
                if ($prevFirstPrices[0] == $nextFirstPrices[0]) {
                    return 0;
                }

                return ($prevFirstPrices[0] < $nextFirstPrices[0]) ? -1 : 1;
            });

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

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchSpecials(SearchQuery $query)
    {
        return $this->search->searchSpecials($query);
    }
}
