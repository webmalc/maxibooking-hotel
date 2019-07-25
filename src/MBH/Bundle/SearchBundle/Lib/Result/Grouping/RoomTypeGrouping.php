<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


/**
 * Class RoomTypeGrouping
 * @package MBH\Bundle\SearchBundle\Lib\Result\Grouping
 */
class RoomTypeGrouping extends AbstractGrouping
{
    protected function getGroupField(): string
    {
        return 'roomType';
    }

}