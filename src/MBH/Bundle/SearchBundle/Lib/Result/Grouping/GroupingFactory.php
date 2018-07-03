<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException;

class GroupingFactory
{
    /** @var array  */
    public const GROUPERS = ['roomType'];

    /**
     * @param string $grouping
     * @return GroupingInterface
     * @throws GroupingFactoryException
     */
    public function createGrouping(string $grouping): GroupingInterface
    {
        if (!\in_array($grouping, self::GROUPERS, true)) {
            throw new GroupingFactoryException('There is no grouper with name ' . $grouping);
        }

        switch ($grouping) {
            case 'roomType':
                return new RoomTypeGrouping();
        }

        throw new GroupingFactoryException('Grouper ' . $grouping. 'not found!');
    }
}