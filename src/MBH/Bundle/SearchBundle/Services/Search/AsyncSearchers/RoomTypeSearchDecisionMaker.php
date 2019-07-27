<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConfigException;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\QueryGroups\SearchNecessarilyInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\SearchConditionsInterface;
use MBH\Bundle\SearchBundle\Services\SearchConfigService;
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
     * @var bool
     */
    private $isShowNecessarilyDate;


    /**
     * RoomTypeSearchDecisionMaker constructor.
     * @param Client $cache
     * @param SearchConfigService $configService
     * @throws SearchConfigException
     */
    public function __construct(Client $cache, SearchConfigService $configService)
    {
        $this->cache = $cache;
        $config = $configService->getConfig();
        $this->isShowNecessarilyDate = $config->isMustShowNecessarilyDate();
    }

    /**
     * @param SearchConditionsInterface $conditions
     * @param QueryGroupInterface $group
     * @return bool
     * @throws AsyncResultReceiverException
     */
    public function isNeedSearch(SearchConditionsInterface $conditions, QueryGroupInterface $group): bool
    {
        if (!$group instanceof QueryGroupByRoomType) {
            throw new AsyncResultReceiverException('Wrong query group in RoomType Decision maker');
        }
        $roomTypeId = $group->getRoomTypeId();
        $hash = $conditions->getSearchHash();

        $results = (int)$this->cache->get(self::getFoundedKey($hash, $roomTypeId));

        printf("\n".'%u conditions, %u founded'. "\n", $conditions->getAdditionalResultsLimit(), $results);

        return ($results < $conditions->getAdditionalResultsLimit()) || $group->isSearchNecessarily() ;
    }

    /**
     * @param SearchConditionsInterface $conditions
     * @param QueryGroupInterface $group
     * @throws AsyncResultReceiverException
     */
    public function markFoundedResults(SearchConditionsInterface $conditions, QueryGroupInterface $group): void
    {
        if (!$group instanceof QueryGroupByRoomType) {
            throw new AsyncResultReceiverException('Wrong query group in RoomType Decision maker');
        }

        $key = static::getFoundedKey($conditions->getSearchHash(), $group->getRoomTypeId());
        $this->cache->incr($key);
        $this->cache->expire($key, self::EXPIRED);
    }

    /**
     * @param string $hash
     * @param string $roomTypeId
     * @return string
     */
    public static function getFoundedKey(string $hash, string $roomTypeId): string
    {
        return 'already_founded_room_types_' .$roomTypeId.'_'. $hash;
    }

    public function canIStoreInStock(SearchConditionsInterface $conditions, QueryGroupInterface $group): bool
    {
        if (!$group instanceof QueryGroupByRoomType) {
            throw new AsyncResultReceiverException('Wrong query group in RoomType Decision maker');
        }

        $storedKey = static::getStoredInStackKey($conditions->getSearchHash(), $group->getRoomTypeId());
        $stored = (int)$this->cache->get($storedKey);

        return ($stored < $conditions->getAdditionalResultsLimit())
            || ($this->isMustDate($group) && $this->isShowNecessarilyDate);
    }

    public function markStoredInStockResult(SearchConditionsInterface $conditions, QueryGroupInterface $group): void
    {
        /** @var QueryGroupByRoomType $group */
        $storedKey = static::getStoredInStackKey($conditions->getSearchHash(), $group->getRoomTypeId());
        $this->cache->incr($storedKey);
        $this->cache->expire($storedKey, self::EXPIRED);

    }

    public static function getStoredInStackKey(string $hash, string $roomTypeId): string
    {
        return 'already_stored_in_stack_room_types_' .$roomTypeId.'_'. $hash;
    }

    private function isMustDate(SearchNecessarilyInterface $group): bool
    {
        return $group->isSearchNecessarily();
    }



}