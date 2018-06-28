<?php


namespace MBH\Bundle\SearchBundle\Lib\Data;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class PackageAccommodationFetchQuery extends BaseFetchQuery
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
     * @return PackageAccommodationFetchQuery
     */
    public function setRoomTypeId(string $roomTypeId): PackageAccommodationFetchQuery
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }

    public static function createInstanceFromSearchQuery(SearchQuery $searchQuery)
    {
        /** @var self $fetchQuery */
        $fetchQuery = parent::createInstanceFromSearchQuery($searchQuery);
        $fetchQuery->setRoomTypeId($searchQuery->getRoomTypeId());

        return $fetchQuery;
    }

}