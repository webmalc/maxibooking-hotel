<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;



class RoomTypeCategoryGrouping extends RoomTypeGrouping
{
    public function group(array $searchResults): array
    {
        $grouped = [];
        $roomTypeGrouped = parent::group($searchResults);
        if (count($roomTypeGrouped)) {
            foreach ($roomTypeGrouped as $roomTypeGroup) {
                $catId = $roomTypeGroup['roomType']['categoryId'];
                if (!($grouped[$catId] ?? null)) {
                    $grouped[$catId] = $roomTypeGroup;
                }

            }
        }

        return $grouped;
    }

}