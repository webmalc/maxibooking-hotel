<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;

abstract class AbstractGrouping implements GroupingInterface
{
    private const ALLOW_GROUP_FIELDS = [
        'roomType',
        'roomTypeCategory',
    ];

    /**
     * @param array $searchResults
     * @return array
     * @throws SearchResultComposerException
     */
    public function group(array $searchResults): array
    {
        $groupField = $this->getGroupField();
        if (!in_array($groupField, self::ALLOW_GROUP_FIELDS, true)) {
            throw new SearchResultComposerException('No allow group field for grouping result');
        }
        $grouped = [];
        foreach ($searchResults as $searchResult) {
            /** @var Result $searchResult */
            $fieldId = $searchResult[$groupField];
            $grouped[$fieldId][] = $searchResult;
        }

        $grouped = array_map(function ($groupedResults) use ($groupField){
            return [
                $groupField => $groupedResults[0][$groupField],
                'results' => $this->groupByDateTime($groupedResults)

            ];
        }, $grouped);

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

    abstract protected function getGroupField(): string;
}