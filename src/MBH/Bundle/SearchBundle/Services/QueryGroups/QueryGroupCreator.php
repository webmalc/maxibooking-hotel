<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\QueryGroupException;
use MBH\Bundle\SearchBundle\Services\QueryGroups\Factories\DayQueryGroupFactory;
use MBH\Bundle\SearchBundle\Services\QueryGroups\Factories\QueryGroupFactoryInterface;
use MBH\Bundle\SearchBundle\Services\QueryGroups\Factories\RoomTypeQueryGroupFactory;
use MBH\Bundle\SearchBundle\Services\Search\SearchCombinations;

class QueryGroupCreator
{

    /**
     * @param SearchConditions $conditions
     * @param SearchCombinations $combinations
     * @param string $groupName
     * @return QueryGroupInterface[]
     * @throws QueryGroupException
     */
    public function createQueryGroups(SearchConditions $conditions, SearchCombinations $combinations, string $groupName): array
    {
        switch ($groupName) {
            case 'QueryGroupByRoomType':
                $factory = new RoomTypeQueryGroupFactory();
                break;
            case 'QueryGroupByDay':
                $factory = new DayQueryGroupFactory();
                break;
            default:
                throw new QueryGroupException('No group factory  with name '. $groupName);
        }

        /** @var QueryGroupFactoryInterface $factory */
        return $factory->createQueryGroups($conditions, $combinations);

    }
}