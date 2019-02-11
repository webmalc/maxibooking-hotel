<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


/**
 * Interface GroupingInterface
 * @package MBH\Bundle\SearchBundle\Lib\Result\Grouping
 */
interface GroupingInterface
{
    public function group(array $searchResults): array;
}