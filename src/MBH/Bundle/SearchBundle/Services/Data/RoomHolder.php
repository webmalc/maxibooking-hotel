<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Lib\Data\RoomFetchQuery;

class RoomHolder implements DataHolderInterface
{

    protected $data;

    /**
     * @param DataFetchQueryInterface|RoomFetchQuery $fetchQuery
     * @return array|null
     */
    public function get(DataFetchQueryInterface $fetchQuery): ?array
    {
        $hash = $fetchQuery->getHash();
        $hashed = $this->data[$hash] ?? null;
        if (null === $hashed) {
            return null;
        }

        return $this->data[$fetchQuery->getHash()][$fetchQuery->getRoomTypeId()] ?? [];
    }

    public function set(DataFetchQueryInterface $fetchQuery, array $data): void
    {
        $hash = $fetchQuery->getHash();
        $this->data[$hash] = $data;
    }

}