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
        $groupedByRoomTypeId = $this->groupByRoomTypeId($searchResults);

        $grouped = array_map(function ($groupedResults) {
            /** @var Result[] $groupedResults */
            return [
                'roomType' => $groupedResults[0]->getResultRoomType(),
//                'results' => $this->groupByDateTime($groupedResults)
                'results' => $groupedResults

            ];
        }, $groupedByRoomTypeId);

        return $grouped;
    }

    private function groupByRoomTypeId(array $searchResults): array
    {
        $grouped = [];
        foreach ($searchResults as $searchResult) {
            /** @var Result $searchResult */
            $resultRoomType = $searchResult->getResultRoomType();
            $grouped[$resultRoomType->getId()][] = $searchResult;
        }

        return $grouped;
    }

    private function groupByDateTime($searchResults): array
    {
        $grouped = [];
        foreach ($searchResults as $result) {
            /** @var Result $result */
            $begin = $result->getBegin();
            $end = $result->getEnd();
            $key = $begin->format('d.m.Y') . '_' . $end->format('d.m.Y');
            $grouped[$key][] = $result;
        }

        return $grouped;
    }

}