<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\Result;

/**
 * Interface GroupingInterface
 * @package MBH\Bundle\SearchBundle\Lib\Result\Grouping
 */
interface GroupingInterface
{
    /** @param Result[] $searchResults
     * @return array
     */
    public function group(array $searchResults): array;
}