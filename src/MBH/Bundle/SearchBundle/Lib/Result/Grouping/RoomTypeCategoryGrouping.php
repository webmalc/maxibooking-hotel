<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;



class RoomTypeCategoryGrouping extends AbstractGrouping
{
    protected function getGroupField(): string
    {
        return 'roomTypeCategory';
    }

}