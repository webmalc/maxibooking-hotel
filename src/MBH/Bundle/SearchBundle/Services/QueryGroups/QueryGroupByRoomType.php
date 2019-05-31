<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class QueryGroupByRoomType implements QueryGroupInterface, AsyncQueryGroupInterface
{
    public const QUERY_GROUP_BY_ROOM_TYPE = 'queryGroupByRoomType';

    /** @var SearchQuery[] */
    private $searchQueries = [];

    /** @var int  */
    private $priority = 1;

    /** @var string */
    private $roomTypeId;

    public function unsetConditions(): void
    {
        array_map(static function (SearchQuery $query) {
            $query->unsetConditions();
        }, $this->searchQueries);
    }

    /**
     * @param SearchQuery[] $searchQueries
     * @return QueryGroupByRoomType
     */
    public function setSearchQueries(array $searchQueries): QueryGroupByRoomType
    {
        $this->searchQueries = $searchQueries;

        return $this;
    }

    public function getSearchQueries(): array
    {
        return $this->searchQueries;
    }



    public function setQueuePriority(int $priority): QueryGroupByRoomType
    {
        $this->priority = $priority;

        return $this;
    }

    public function getQueuePriority(): int
    {
        return $this->priority;
    }

    public function countQueries(): int
    {
        return \count($this->searchQueries);
    }


    /**
     * @return string
     */
    public function getRoomTypeId(): string
    {
        return $this->roomTypeId;
    }

    /**
     * @param string $roomTypeId
     * @return QueryGroupByRoomType
     */
    public function setRoomTypeId(string $roomTypeId): QueryGroupByRoomType
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }

    public function getGroupName(): string
    {
        return self::QUERY_GROUP_BY_ROOM_TYPE;
    }




}