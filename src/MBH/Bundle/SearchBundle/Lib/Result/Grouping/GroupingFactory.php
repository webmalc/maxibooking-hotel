<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException;

class GroupingFactory
{
    /** @var array */
    public const GROUPERS = ['roomType', 'roomTypeCategory', 'fake'];

    /**
     * @param string $grouperName
     * @param bool $separateError
     * @return GroupingInterface
     * @throws GroupingFactoryException
     */
    public function createGrouping(?string $grouperName = null, bool $separateError = true): GroupingInterface
    {
        if (null === $grouperName) {
            $grouperName = 'fake';
        }

        $this->checkGrouperExists($grouperName);
        $grouper = $this->createGrouper($grouperName);

        if ($separateError) {
            $grouper = new ErrorSplitGrouping($grouper);
        }

        return $grouper;
    }

    private function checkGrouperExists(string $grouperName)
    {
        if (!\in_array($grouperName, self::GROUPERS, true)) {
            throw new GroupingFactoryException('There is no grouper with name '.$grouperName);
        }

    }

    private function createGrouper(string $grouperName): GroupingInterface
    {
        switch ($grouperName) {
            case 'roomType':
                $grouper = new RoomTypeGrouping();
                break;
            case 'roomTypeCategory':
                $grouper = new RoomTypeCategoryGrouping();
                break;
            case 'fake':
                $grouper = new FakeGrouping();
                break;
            default:
                throw new GroupingFactoryException('Grouper '.$grouperName.'not found!');
        }

        return $grouper;

    }
}