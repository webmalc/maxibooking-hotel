<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException;

class GroupingFactory
{
    /** @var array */
    public const GROUPERS = ['roomType', 'roomTypeCategory'];

    /**
     * @param string $grouping
     * @param bool $isSplitError
     * @return GroupingInterface
     * @throws GroupingFactoryException
     */
    public function createGrouping(string $grouping, bool $isSplitError = true): GroupingInterface
    {
        if (!\in_array($grouping, self::GROUPERS, true)) {
            throw new GroupingFactoryException('There is no grouper with name '.$grouping);
        }

        switch ($grouping) {
            case 'roomType':
                $grouper = new RoomTypeGrouping();
                break;
            case 'roomTypeCategory':
                $grouper = new RoomTypeCategoryGrouping();
                break;
            default:
                throw new GroupingFactoryException('Grouper '.$grouping.'not found!');
        }

        if ($isSplitError) {
            $grouper = new ErrorSplitGrouping($grouper);
        }

        return $grouper;
    }
}