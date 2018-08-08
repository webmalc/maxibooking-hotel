<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;

class RoomTypeGroupedResult
{
    /** @var ResultRoomType */
    private $roomType;

    /** @var array */
    private $results;

    /**
     * @return ResultRoomType
     */
    public function getRoomType(): ResultRoomType
    {
        return $this->roomType;
    }

    /**
     * @param ResultRoomType $roomType
     */
    public function setRoomType(ResultRoomType $roomType): void
    {
        $this->roomType = $roomType;
    }

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param array $results
     */
    public function setResults(array $results): void
    {
        $this->results = $results;
    }


}