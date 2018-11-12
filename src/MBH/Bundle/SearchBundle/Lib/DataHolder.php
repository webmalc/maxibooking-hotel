<?php


namespace MBH\Bundle\SearchBundle\Lib;


use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PackageBundle\Document\PackageAccommodationRepository;
use MBH\Bundle\PriceBundle\Document\PriceCacheRepository;
use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\PriceBundle\Document\RoomCacheRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class DataHolder
 * @deprecated 
 * @package MBH\Bundle\SearchBundle\Lib
 */
class DataHolder
{
    /** @var Tariff[] */
    private $tariffs;

    /** @var RoomType[] */
    private $roomTypes;

    /** @var TariffRepository */
    private $tariffRepository;

    /** @var RestrictionRepository */
    private $restrictionRepository;

    /** @var RoomTypeRepository */
    private $roomTypeRepository;

    /** @var RoomCacheRepository */
    private $roomCacheRepository;

    /** @var RoomRepository */
    private $roomRepository;

    /** @var PriceCacheRepository */
    private $priceCacheRepository;

    /** @var PackageAccommodationRepository */
    private $packageAccommodationRepository;

    /** @var SearchConditionsRepository */
    private $searchConditionsRepository;

    /** @var array */
    private $searchConditions;

    /** @var bool */
    private $isUseCategory;

    /** @var array */
    private $restrictions;

    /** @var array */
    private $accommodationsGroupedByRoomType;

    /** @var array */
    private $roomsGroupedByRoomType;

    /** @var array */
    private $roomCaches;

    /** @var array */
    private $priceCaches;

    /** @var array */
    private $hotelIdsInSearch;

    /**
     * TariffHolder constructor.
     * @param TariffRepository $tariffRepository
     * @param RoomTypeRepository $roomTypeRepository
     * @param RestrictionRepository $restrictionRepository
     * @param ClientConfigRepository $configRepository
     * @param RoomCacheRepository $roomCacheRepository
     * @param RoomRepository $roomRepository
     * @param PackageAccommodationRepository $accommodationRepository
     * @param PriceCacheRepository $priceCacheRepository
     * @param SearchConditionsRepository $conditionsRepository
     */
    public function __construct(
        TariffRepository $tariffRepository,
        RoomTypeRepository $roomTypeRepository,
        RestrictionRepository $restrictionRepository,
        ClientConfigRepository $configRepository,
        RoomCacheRepository $roomCacheRepository,
        RoomRepository $roomRepository,
        PackageAccommodationRepository $accommodationRepository,
        PriceCacheRepository $priceCacheRepository,
        SearchConditionsRepository $conditionsRepository,
        HotelRepository $hotelRepository
    ) {
        $this->tariffRepository = $tariffRepository;
        $this->roomTypeRepository = $roomTypeRepository;
        $this->tariffs = $tariffRepository->findAll();
        $this->roomTypes = $roomTypeRepository->findAll();
        $this->restrictionRepository = $restrictionRepository;
        $this->isUseCategory = $configRepository->fetchConfig()->getUseRoomTypeCategory();
        $this->roomCacheRepository = $roomCacheRepository;
        $this->roomRepository = $roomRepository;
        $this->packageAccommodationRepository = $accommodationRepository;
        $this->priceCacheRepository = $priceCacheRepository;
        $this->searchConditionsRepository = $conditionsRepository;
        $this->hotelIdsInSearch = $hotelRepository->getSearchActiveIds();
    }


    /**
     * @param string $tariffId
     * @return Tariff
     * @throws DataHolderException
     */
    public function getFetchedTariff(string $tariffId): Tariff
    {
        foreach ($this->tariffs as $tariff) {
            if ($tariffId === $tariff->getId()) {
                return $tariff;
            }
        }

        throw new DataHolderException('There is no Tariff in tariff holder!');

    }

    /**
     * @param string $roomTypeId
     * @return RoomType
     * @throws DataHolderException
     */
    public function getFetchedRoomType(string $roomTypeId): RoomType
    {
        foreach ($this->roomTypes as $roomType) {
            if ($roomTypeId === $roomType->getId()) {
                return $roomType;
            }
        }

        throw new DataHolderException('There is no RoomType in RoomTypeHolder!');
    }

    /**
     * @param array $hotelIds
     * @param array $tariffIds
     * @param bool $isEnabled
     * @param bool $isOnline
     * @return array
     * @throws MongoDBException
     */
    public function getTariffsRaw(array $hotelIds, array $tariffIds, bool $isEnabled, bool $isOnline): array
    {
        return $this->tariffRepository->fetchRaw(
            $hotelIds,
            $tariffIds,
            $isEnabled,
            $isOnline
        );
    }

    /**
     * @param iterable $rawRoomTypeIds
     * @param array $hotelIds
     * @return array
     */
    public function getRoomTypesRaw(iterable $rawRoomTypeIds, array $hotelIds): array
    {
        if ($this->isUseCategory) {
            $roomTypeIds = $this->roomTypeRepository->fetchRawWithCategory($rawRoomTypeIds, $hotelIds);
        } else {
            $roomTypeIds = $this->roomTypeRepository->fetchRaw($rawRoomTypeIds, $hotelIds);
        }

        return $roomTypeIds;
    }


    /***********************************
     * RoomCachesBlock
     * @param SearchQuery $searchQuery
     * @return array|null
     * @throws MongoDBException
     */
    public function getNecessaryRoomCaches(SearchQuery $searchQuery): ?array
    {
        $roomCaches = $this->getRoomCaches($searchQuery);
        if (null === $roomCaches) {
            $conditions = $searchQuery->getSearchConditions();
            $rawRoomCaches = $this->roomCacheRepository->fetchRaw($conditions->getMaxBegin(), $conditions->getMaxEnd());
            $this->setRoomCaches($rawRoomCaches, $searchQuery);

            return $this->getRoomCaches($searchQuery);
        }

        return $roomCaches;

    }

    private function getRoomCaches(SearchQuery $searchQuery): ?array
    {
        $roomCaches = [];
        $hash = $searchQuery->getSearchHash();
        $hashedRoomCaches = $this->roomCaches[$hash] ?? null;
        if (null === $hashedRoomCaches) {
            return null;
        }

        if (!empty($hashedRoomCaches)) {
            $groupedByDayRoomCaches = $hashedRoomCaches[$searchQuery->getRoomTypeId()] ?? [];
            if (!\count($groupedByDayRoomCaches)) {
                return [];
            }
            $begin = $searchQuery->getBegin();
            $end = $searchQuery->getEnd();
            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                /** @var \DateTime $day */
                $roomCache = $groupedByDayRoomCaches[$day->format('d-m-Y')] ?? null;
                if (null !== $roomCache) {
                    $roomCaches[] = $roomCache;
                }
            }
        }

        return empty($roomCaches) ? $roomCaches : array_merge(...$roomCaches);
    }

    /**
     * @param array $rawRoomCaches
     * @param SearchQuery $searchQuery
     */
    private function setRoomCaches(array $rawRoomCaches, SearchQuery $searchQuery): void
    {
        $groupedByRoomTypeRoomCaches = [];
        foreach ($rawRoomCaches as $rawRoomCache) {
            $roomTypeIdKey = (string)$rawRoomCache['roomType']['$id'];
            $dateKey = Helper::convertMongoDateToDate($rawRoomCache['date'])->format('d-m-Y');
            $groupedByRoomTypeRoomCaches[$roomTypeIdKey][$dateKey][] = $rawRoomCache;
        }
        $hash = $searchQuery->getSearchHash();
        $this->roomCaches[$hash] = $groupedByRoomTypeRoomCaches;
    }


    /*************************************
     * Accommodation block.
     * @param SearchQuery $searchQuery
     * @return array
     */
    public function getNecessaryAccommodationRooms(SearchQuery $searchQuery): array
    {
        $accommodations = $this->getAccommodationRooms($searchQuery);
        if (null === $accommodations) {
            $hash = $searchQuery->getSearchHash();
            $conditions = $searchQuery->getSearchConditions();
            if (!($this->roomsGroupedByRoomType[$hash] ?? null)) {
                $this->roomsGroupedByRoomType[$hash] = $this->getAllRoomsByRoomType();
            }
            $allAccommodations = $this->packageAccommodationRepository->getRawAccommodationByPeriod($conditions->getMaxBegin(), $conditions->getMaxEnd());
            $this->setAccommodationsRooms($allAccommodations, $searchQuery);

            return $this->getAccommodationRooms($searchQuery);
        }

        return $accommodations;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return array|null
     */
    private function getAccommodationRooms(SearchQuery $searchQuery): ?array
    {
        $hash = $searchQuery->getSearchHash();
        $hashedPackageAccommodations = $this->accommodationsGroupedByRoomType[$hash] ?? null;
        if (null === $hashedPackageAccommodations) {
            return null;
        }
        $roomTypeId = $searchQuery->getRoomTypeId();
        $packageAccommodations = $hashedPackageAccommodations[$roomTypeId] ?? [];
        $searchBegin = $searchQuery->getBegin();
        $searchEnd = $searchQuery->getEnd();
        $forExcludeRoomsIds = $this->findIntersectWithDates($searchBegin, $searchEnd, $packageAccommodations);
        $allRooms = $this->roomsGroupedByRoomType[$hash][$roomTypeId];

        $allRoomsIds = array_map('\strval', array_column($allRooms, '_id'));
        $accommodationRoomsIds = array_diff($allRoomsIds, $forExcludeRoomsIds);
        $accommodationRooms = array_filter($allRooms, function ($room) use ($accommodationRoomsIds) {
            return \in_array((string)$room['_id'], $accommodationRoomsIds, true);
        });

        return $accommodationRooms;

    }

    private function setAccommodationsRooms(array $packageAccommodations, SearchQuery $searchQuery): void
    {
        $hash = $searchQuery->getSearchHash();
        $accommodationGroupedByRoomType = [];
        foreach ($packageAccommodations as $accommodation) {
            $roomId = (string)$accommodation['accommodation']['$id'];
            $roomTypeId = $this->getRoomTypeIdByRoom($roomId, $hash);
            $accommodationDateKey = $this->getAccommodationDateKey($accommodation['begin'], $accommodation['end']);
            $accommodationGroupedByRoomType[$roomTypeId][$accommodationDateKey][] = $accommodation;
        }

        $this->accommodationsGroupedByRoomType[$hash] = $accommodationGroupedByRoomType;
    }

    private function getAllRoomsByRoomType(): array
    {
        return $this->roomRepository->fetchRawAllRoomsByRoomType([], true);
    }

    private function getRoomTypeIdByRoom(string $needleRoomId, string $hash): string
    {
        $groupedRooms = $this->roomsGroupedByRoomType[$hash];
        if (!$groupedRooms) {
            throw new DataHolderException('There is no sure grouped Rooms dadta!');
        }
        foreach ($groupedRooms as $roomTypeId => $rooms) {
            $roomsIds = array_map('\strval', array_column($rooms, '_id'));
            if (\in_array($needleRoomId, $roomsIds, true)) {
                return $roomTypeId;
            }
        }

        throw new DataHolderException('There is error in determine roomType of room');
    }

    private function getAccommodationDateKey(\MongoDate $begin, \MongoDate $end): string
    {
        $key = Helper::convertMongoDateToDate($begin)->format('d-m-Y');
        $key .= '_';
        $key .= Helper::convertMongoDateToDate($end)->format('d-m-Y');

        return $key;
    }

    private function findIntersectWithDates(\DateTime $searchBegin, \DateTime $searchEnd, array $packageAccommodations): array
    {
        $intersected = [];
        foreach ($packageAccommodations as $dateKey => $accommodations) {
            [$beginKey, $endKey] = explode('_', $dateKey);
            $accommodationBegin = new \DateTime($beginKey);
            $accommodationEnd = new \DateTime($endKey);
            if ($accommodationBegin < $searchEnd && $accommodationEnd > $searchBegin) {
                $rooms = array_column($accommodations, 'accommodation');
                $roomsIds = array_map('\strval', array_column($rooms, '$id'));
                $intersected[] = $roomsIds;
            }
        }

        return empty($intersected) ? $intersected : array_merge(...$intersected);
    }

    /****************************
     *  PriceCaches Block
     * @param CalcQuery $calcQuery
     * @param string $searchingTariffId
     * @return array
     * @throws DataHolderException
     */
    public function getNecessaryPriceCaches(CalcQuery $calcQuery, string $searchingTariffId): array
    {
        $hash = $calcQuery->getConditionHash();
        if (!$hash) {
            return $this->getRawPriceCachesWithNoCondition($calcQuery, $searchingTariffId);
        }
        $priceCaches = $this->getPriceCaches($calcQuery, $searchingTariffId);
        if (null === $priceCaches) {
            $allRawPriceCaches = $this->getAllPeriodPriceCaches($calcQuery);
            $this->setPriceCaches($allRawPriceCaches, $calcQuery);
            $priceCaches = $this->getPriceCaches($calcQuery, $searchingTariffId);
        }

        return $priceCaches;

    }

    private function getPriceCaches(CalcQuery $calcQuery, string $searchingTariffId): ?array
    {
        $hash = $calcQuery->getConditionHash();
        $priceCaches = $this->priceCaches[$hash] ?? null;
        if (null === $priceCaches) {
            return null;
        }

        $priceCacheBegin = $calcQuery->getSearchBegin();
        $priceCacheEnd = $calcQuery->getPriceCacheEnd();
        $roomTypeId = $calcQuery->getPriceRoomTypeId();

        $groupedPriceCaches = $priceCaches[$roomTypeId.'_'.$searchingTariffId] ?? [];
        $priceCaches = [];
        if (\count($groupedPriceCaches)) {
            foreach (new \DatePeriod($priceCacheBegin, \DateInterval::createFromDateString('1 day'), (clone $priceCacheEnd)->modify('+1 days')) as $day) {
                /** @var \DateTime $day */
                $dateKey = $day->format('d-m-Y');
                $priceCache = $groupedPriceCaches[$dateKey] ?? null;
                if ($priceCache) {
                    $priceCaches[] = $priceCache;
                }
                $keys[] = $dateKey;
            }

        }

        return $priceCaches;
    }

    private function setPriceCaches(array $rawPriceCaches, CalcQuery $calcQuery): void
    {
        $hash = $calcQuery->getConditionHash();
        $priceCaches = [];
        foreach ($rawPriceCaches as $priceCache) {
            $priceSetKey = $this->createPriceCacheSetKey($priceCache, $calcQuery->isUseCategory());
            $dateTimeKey = Helper::convertMongoDateToDate($priceCache['date'])->format('d-m-Y');
            $priceCaches[$priceSetKey][$dateTimeKey] = $priceCache;
        }
        $this->priceCaches[$hash] = $priceCaches;
    }

    private function createPriceCacheSetKey(array $priceCache, bool $isUseCategory): string
    {
        $roomTypeField = $isUseCategory ? 'roomTypeCategory' : 'roomType';
        $key = (string)$priceCache[$roomTypeField]['$id'] . '_' . (string)$priceCache['tariff']['$id'];

        return $key;
    }

    private function getRawPriceCachesWithNoCondition(CalcQuery $calcQuery, string $searchingTariffId)
    {
        $begin = $calcQuery->getSearchBegin();
        $end = $calcQuery->getPriceCacheEnd();
        $roomTypeId = $calcQuery->getPriceRoomTypeId();
        $isUseCategory = $calcQuery->isUseCategory();
        return $this->priceCacheRepository
            ->fetchRaw(
                $begin,
                $end,
                $roomTypeId,
                $searchingTariffId,
                $isUseCategory
            );
    }

    private function getAllPeriodPriceCaches(CalcQuery $calcQuery): array
    {
        $begin = $calcQuery->getConditionMaxBegin();
        $end = $calcQuery->getConditionMaxEnd();
        if (($roomTypes = $calcQuery->getConditionRoomTypes()) && \count($roomTypes)) {
            foreach ($roomTypes as $roomType) {
                $roomTypeIds[] = $roomType->getId();
            }
        }
        $tariffIds = $calcQuery->getConditionPricedTariffs();

        if (!$begin || !$end) {
            throw new DataHolderException('Error in CalcQuery Condition Max Begin for period search');
        }
        $isUseCategory = $calcQuery->isUseCategory();

        return $this->priceCacheRepository->fetchRawPeriod($begin, $end, $roomTypeIds ??  [], $tariffIds ?? [], $isUseCategory);
    }

    public function getConditions(string $hash, string $conditionsId): SearchConditions
    {
        /** @var SearchConditions $searchConditions */
        if ($searchConditions = ($this->searchConditions[$hash] !== null)) {
            return $searchConditions;
        }
        $searchConditions = $this->searchConditionsRepository->find($conditionsId);
        if ($searchConditions) {
            $this->searchConditions[$hash] = $searchConditions;

            return $searchConditions;
        }

        throw new DataHolderException('Can not find SearchConditions by hash and Id');
    }

    public function getHotelIdsInSearch(): array
    {
        return $this->hotelIdsInSearch;
    }


}