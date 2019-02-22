<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups\Factories;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupSync;

class SyncQueryGroupFactory implements QueryGroupFactoryInterface
{
    public function createQueryGroups(SearchConditions $searchConditions, array $dates, array $combinations): array
    {
        $queries = [];
        foreach ($dates as $period) {
            $begin = $period['begin'];
            $end = $period['end'];
            foreach ($combinations as $combination) {
                $queries[] = SearchQuery::createInstance($searchConditions, $begin, $end, $combination);
            }
        }

        $group = new QueryGroupSync();
        $group->setSearchQueries($queries);

        return [$group];
    }

}