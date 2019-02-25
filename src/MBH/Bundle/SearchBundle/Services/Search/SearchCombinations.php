<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

/**
 * Class SearchCombinations
 * @package MBH\Bundle\SearchBundle\Services\Search
 */
class SearchCombinations
{
    /** @var array */
    private $dates;

    /** @var array */
    private $tariffRoomTypeCombinations;

    /**
     * @return mixed
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * @param mixed $dates
     * @return SearchCombinations
     */
    public function setDates(array $dates): SearchCombinations
    {
        $this->dates = $dates;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTariffRoomTypeCombinations()
    {
        return $this->tariffRoomTypeCombinations;
    }

    /**
     * @param mixed $tariffRoomTypeCombinations
     * @return SearchCombinations
     */
    public function setTariffRoomTypeCombinations(array $tariffRoomTypeCombinations): SearchCombinations
    {
        $this->tariffRoomTypeCombinations = $tariffRoomTypeCombinations;

        return $this;
    }

    /**
     * @param SearchConditions $conditions
     * @return SearchQuery[]
     */
    public function createSearchQueries(SearchConditions $conditions): array
    {
        $searchQueries = [];
        foreach ($this->dates as $period) {
            $begin = $period['begin'];
            $end = $period['end'];
            foreach ($this->tariffRoomTypeCombinations as $combination) {
                $searchQueries[] = SearchQuery::createInstance($conditions, $begin, $end, $combination);
            }
        }

        return $searchQueries;
    }


}