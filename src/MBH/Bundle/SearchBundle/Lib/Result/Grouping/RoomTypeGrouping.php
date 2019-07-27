<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\Result;

/**
 * Class RoomTypeGrouping
 * @package MBH\Bundle\SearchBundle\Lib\Result\Grouping
 */
class RoomTypeGrouping implements GroupingInterface
{
    /**
     * @param array $searchResults
     * @return array
     */
    public function group(array $searchResults): array
    {
        $groupedByRoomTypeId = $this->groupByRoomTypeId($searchResults);

        $grouped = array_map(function ($groupedResults) {
            return [
                'roomType' => $groupedResults[0]['roomType'],
                'results' => $this->groupByDateTime($groupedResults)

            ];
        }, $groupedByRoomTypeId);

        return $grouped;
    }

    /**
     * @param array $searchResults
     * @return array
     */
    private function groupByRoomTypeId(array $searchResults): array
    {
        $grouped = [];
        foreach ($searchResults as $searchResult) {
            /** @var Result $searchResult */
            $resultRoomType = $searchResult['roomType'];
            $grouped[$resultRoomType][] = $searchResult;
        }

        return $grouped;
    }

    /**
     * @param $searchResults
     * @return array
     * @throws \Exception
     */
    private function groupByDateTime($searchResults): array
    {
        $grouped = [];
        foreach ($searchResults as $result) {
            /** @var Result $result */
            $begin = new \DateTime($result['begin']);
            $end = new \DateTime($result['end']);
            $key = $begin->format('d.m.Y') . '_' . $end->format('d.m.Y');
            $grouped[$key][] = $result;
        }

        return $grouped;
    }

}