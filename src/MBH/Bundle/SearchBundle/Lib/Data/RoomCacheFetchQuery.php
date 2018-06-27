<?php


namespace MBH\Bundle\SearchBundle\Lib\Data;

use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class RoomCacheFetchQuery extends BaseFetchQuery
{
    /** @var string */
    protected $roomTypeId;

    /**
     * @return string
     */
    public function getRoomTypeId(): string
    {
        return $this->roomTypeId;
    }

    /**
     * @param string $roomTypeId
     */
    public function setRoomTypeId(string $roomTypeId): void
    {
        $this->roomTypeId = $roomTypeId;
    }



    public static function createInstanceFromSearchQuery(SearchQuery $searchQuery)
    {
        $cacheQuery = parent::createInstanceFromSearchQuery($searchQuery);
        /** @var static $cacheQuery*/
        $cacheQuery->setRoomTypeId($searchQuery->getRoomTypeId());

        return $cacheQuery;
    }
}