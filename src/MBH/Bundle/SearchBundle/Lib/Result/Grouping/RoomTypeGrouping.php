<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\Result;

class RoomTypeGrouping implements GroupingInterface
{
    public function group(array $searchResults): array
    {
        $grouped = [];
        foreach ($searchResults as $seasrchResult) {
            /** @var Result $seasrchResult */
            $roomType = $seasrchResult->getRoomType();
            $roomTypes[$roomType->getId()][] = $roomType;
            $grouped[$roomType->getId()][] = $seasrchResult;
        }

        $grouped = array_map(function ($groupedResults) {
            /** @var Result[] $groupedResults */
            return [
                'roomType' => $groupedResults[0]->getRoomType(),
                'results' => $groupedResults
            ];
        }, $grouped);

        return $grouped;
    }

}