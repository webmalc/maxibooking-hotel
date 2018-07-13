<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\Result;

class RoomTypeGrouping implements GroupingInterface
{
    /**
     * @param array $searchResults
     * @return Result[]
     */
    public function group(array $searchResults): array
    {
        $grouped = [];
        foreach ($searchResults as $searchResult) {
            /** @var Result $searchResult */
            $resultRoomType = $searchResult->getResultRoomType();
            $roomTypes[$resultRoomType->getId()][] = $resultRoomType;
            $grouped[$resultRoomType->getId()][] = $searchResult;
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