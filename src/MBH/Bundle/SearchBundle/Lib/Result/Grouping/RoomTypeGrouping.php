<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\Result;

class RoomTypeGrouping implements GroupingInterface
{
    public function group(array $searchResults): array
    {
        $grouped = [];
        foreach ($searchResults as $searchResult) {
            /** @var Result $searchResult */
            $roomType = $searchResult->getResultRoomType();
            $roomTypes[$roomType->getId()][] = $roomType;
            $grouped[$roomType->getId()][] = $searchResult;
        }

        $grouped = array_map(function ($groupedResults) {
            /** @var Result[] $groupedResults */
            return [
                'roomType' => $groupedResults[0]->getResultRoomType(),
                'results' => $groupedResults
            ];
        }, $grouped);

        return $grouped;
    }

}