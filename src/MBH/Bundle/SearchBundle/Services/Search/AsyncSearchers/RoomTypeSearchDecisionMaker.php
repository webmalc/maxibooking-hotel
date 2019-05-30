<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use Predis\Client;

class RoomTypeSearchDecisionMaker implements AsyncSearchDecisionMakerInterface
{

    /** @var RedisCache */
    private $cache;

    /**
     * RoomTypeSearchDecisionMaker constructor.
     * @param Client $cache
     */
    public function __construct(Client $cache)
    {
        $this->cache = $cache;
    }

    public function isNeedSearch(SearchConditions $conditions, QueryGroupInterface $group): bool
    {
        if (!$group instanceof QueryGroupByRoomType) {
            throw new AsyncResultReceiverException('Wrong query group in RoomType Decision maker');
        }
        $roomTypeId = $group->getRoomTypeId();
        $hash = $conditions->getSearchHash();
        $results = (int)$this->cache->get($this->getKey($hash, $roomTypeId));

        return $results < $conditions->getAdditionalResultsLimit();
    }

    public function markFoundedResults(SearchConditions $conditions, QueryGroupInterface $group): void
    {
        if (!$group instanceof QueryGroupByRoomType) {
            throw new AsyncResultReceiverException('Wrong query group in RoomType Decision maker');
        }

        $key = $this->getKey($conditions->getSearchHash(), $group->getRoomTypeId());
        $this->cache->incr($key);
    }

    private function getKey(string $hash, string $roomTypeId): string
    {
        return 'already_received_room_types_' .$roomTypeId.'_'. $hash;
    }

}