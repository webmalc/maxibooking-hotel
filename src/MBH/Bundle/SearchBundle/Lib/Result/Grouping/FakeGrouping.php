<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


class FakeGrouping implements GroupingInterface
{
    public function group(array $searchResults): array
    {
        return $searchResults;
    }

}