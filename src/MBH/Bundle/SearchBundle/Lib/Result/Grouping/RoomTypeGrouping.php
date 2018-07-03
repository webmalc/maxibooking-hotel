<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\Result;

class RoomTypeGrouping implements GroupingInterface
{
    public function group(array $results): array
    {
        $grouped = [];
        foreach ($results as $result) {
            /** @var Result $result */
            $grouped[$result->getRoomType()->getId()][] = [
                $result->getBegin()->format('d-m-Y').'_'.$result->getEnd()->format('d-m-Y') => $result
            ];
        }

        return $grouped;
    }

}