<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\QueryGroups\SearchNecessarilyInterface;
use Predis\Client;

/**
 * Class RoomTypeSearchDecisionMaker
 * @package MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers
 */
class RoomTypeSearchDecisionMaker implements AsyncSearchDecisionMakerInterface
{
    /**
     *
     */
    public const EXPIRED = 600;


    /** @var Client */
    private $cache;

    /**
     * RoomTypeSearchDecisionMaker constructor.
     * @param Client $cache
     */
    public function __construct(Client $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param SearchConditions $conditions
     * @param QueryGroupInterface $group
     * @return bool
     * @throws AsyncResultReceiverException
     */
    public function isNeedSearch(SearchConditions $conditions, QueryGroupInterface $group): bool
    {
        if (!$group instanceof QueryGroupByRoomType) {
            throw new AsyncResultReceiverException('Wrong query group in RoomType Decision maker');
        }
        $roomTypeId = $group->getRoomTypeId();
        $hash = $conditions->getSearchHash();

        $results = (int)$this->cache->get($this->getFoundedKey($hash, $roomTypeId));

        printf("\n".'%u conditions, %u founded'. "\n", $conditions->getAdditionalResultsLimit(), $results);

        return ($results < $conditions->getAdditionalResultsLimit()) || $group->isSearchNecessarily() ;
    }

    /**
     * @param SearchConditions $conditions
     * @param QueryGroupInterface $group
     * @throws AsyncResultReceiverException
     */
    public function markFoundedResults(SearchConditions $conditions, QueryGroupInterface $group): void
    {
        if (!$group instanceof QueryGroupByRoomType) {
            throw new AsyncResultReceiverException('Wrong query group in RoomType Decision maker');
        }

        $key = $this->getFoundedKey($conditions->getSearchHash(), $group->getRoomTypeId());
        $this->cache->incr($key);
        $this->cache->expire($key, self::EXPIRED);
    }

    /**
     * @param string $hash
     * @param string $roomTypeId
     * @return string
     */
    private function getFoundedKey(string $hash, string $roomTypeId): string
    {
        return 'already_founded_room_types_' .$roomTypeId.'_'. $hash;
    }

    public function canIStoreInStock(SearchConditions $conditions, QueryGroupInterface $group): bool
    {
        if (!$group instanceof QueryGroupByRoomType) {
            throw new AsyncResultReceiverException('Wrong query group in RoomType Decision maker');
        }

        $storedKey = $this->getStoredInStackKey($conditions->getSearchHash(), $group->getRoomTypeId());
        $stored = (int)$this->cache->get($storedKey);
        $result = $stored < $conditions->getAdditionalResultsLimit();
        $this->cache->incr($storedKey);
        $this->cache->expire($storedKey, self::EXPIRED);

        return $result || $this->isMustDate($group);
    }

    private function getStoredInStackKey(string $hash, string $roomTypeId): string
    {
        return 'already_stored_in_stack_room_types_' .$roomTypeId.'_'. $hash;
    }

    private function isMustDate(SearchNecessarilyInterface $group): bool
    {
        return $group->isSearchNecessarily();
    }



}