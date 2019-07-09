<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups\Factories;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;
use MBH\Bundle\SearchBundle\Services\Search\SearchCombinations;

/**
 * Class RoomTypeQueryGroupFactory
 * @package MBH\Bundle\SearchBundle\Services\QueryGroups\Factories
 */
class RoomTypeQueryGroupFactory implements QueryGroupFactoryInterface
{
    /**
     * @param SearchConditions $searchConditions
     * @param SearchCombinations $combinations
     * @return array
     */
    public function createQueryGroups(SearchConditions $searchConditions, SearchCombinations $combinations): array
    {
        $roomTypeGroups = $this->createRoomTypeGroups($combinations->getTariffRoomTypeCombinations());
        $dates = $combinations->getDates();
        $groups = [];
        foreach ($dates as $periodKey => $period) {
            $begin = $period['begin'];
            $end = $period['end'];
            $isMainGroup = ($begin == $searchConditions->getBegin()) && ($end == $searchConditions->getEnd());
            foreach ($roomTypeGroups as $combinationByRoomTypeId => $roomTypeCombinations) {
                $queries = [];
                foreach ($roomTypeCombinations as $combination) {
                    $queries[] = SearchQuery::createInstance($searchConditions, $begin, $end, $combination);
                }
                $group = new QueryGroupByRoomType();
                $group
                    ->setRoomTypeId($combinationByRoomTypeId)
                    ->setQueuePriority($isMainGroup ? 10 : 1)
                    ->setGroupIsMain($isMainGroup)
                    ->setSearchQueries($queries)
                    ->setGroupDatePeriodKey($periodKey)
                ;

                $groups[] = $group;
            }
        }


        return $groups;

    }


    /**
     * @param array $combinations
     * @return QueryGroupByRoomType[]
     */
    private function createRoomTypeGroups(array $combinations): array
    {
        $result = [];
        foreach ($combinations as $combination) {
            $result[$combination['roomTypeId']][] = $combination;
        }

        return $result;
    }


}