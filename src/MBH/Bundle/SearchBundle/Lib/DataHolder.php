<?php


namespace MBH\Bundle\SearchBundle\Lib;


use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\PriceBundle\Document\RoomCacheRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

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

    /** @var bool */
    private $isUseCategory;

    /** @var array */
    private $restrictions;

    /** @var array */
    private $roomCaches;

    /**
     * TariffHolder constructor.
     * @param TariffRepository $tariffRepository
     * @param RoomTypeRepository $roomTypeRepository
     * @param ClientConfigRepository $configRepository
     */
    public function __construct(TariffRepository $tariffRepository, RoomTypeRepository $roomTypeRepository, RestrictionRepository $restrictionRepository, ClientConfigRepository $configRepository, RoomCacheRepository $roomCacheRepository)
    {
        $this->tariffRepository = $tariffRepository;
        $this->roomTypeRepository = $roomTypeRepository;
        $this->tariffs = $tariffRepository->findAll();
        $this->roomTypes = $roomTypeRepository->findAll();
        $this->restrictionRepository = $restrictionRepository;
        $this->isUseCategory = $configRepository->fetchConfig()->getUseRoomTypeCategory();
        $this->roomCacheRepository = $roomCacheRepository;
    }


    /**
     * @param string $tariffId
     * @return Tariff|null
     */
    public function getFetchedTariff(string $tariffId): ?Tariff
    {
        foreach ($this->tariffs as $tariff) {
            if ($tariffId === $tariff->getId()) {
                return $tariff;
            }
        }

        return null;
    }

    public function getFetchedRoomType(string $roomTypeId): ?RoomType
    {
        foreach ($this->roomTypes as $roomType) {
            if ($roomTypeId === $roomType->getId()) {
                return $roomType;
            }
        }

        return null;
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
            true,
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


    public function getCheckNecessaryRestrictions(SearchQuery $searchQuery): array
    {
        $restrictions = $this->getRestrictions($searchQuery);
        if (null === $restrictions) {
            $conditions = $searchQuery->getSearchConditions();
            $rawRestrictions = $this->restrictionRepository->getAllSearchPeriod($conditions);
            $this->setRestrictions($rawRestrictions, $searchQuery);

            return $this->getRestrictions($searchQuery);
        }

        return $restrictions;

    }

    //** TODO: REDIS MIDDLE CACHE? */
    private function getRestrictions(SearchQuery $searchQuery): ?array
    {
        $restrictions = [];
        $hash = $searchQuery->getSearchHash();
        $hashedRestrictions = $this->restrictions[$hash] ?? null;
        if (null === $hashedRestrictions) {
            return null;
        }

        if (!empty($hashedRestrictions)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $tariffId = $searchQuery->getRestrictionTariffId();
            $roomTypeId = $searchQuery->getRoomTypeId();
            $restrictionBegin = $searchQuery->getBegin();
            $restrictionEnd = (clone $searchQuery->getEnd())->modify('+ 1 day');
            foreach (new \DatePeriod($restrictionBegin, \DateInterval::createFromDateString('1 day'), $restrictionEnd) as $day) {
                $key = $this->getAccessRestrictionKey($day, $tariffId, $roomTypeId);
                if (null !== $restriction = $accessor->getValue($hashedRestrictions, $key)) {
                    $restrictions[] = $restriction;
                }
            }

        }

        return $restrictions;

    }

    private function setRestrictions(array $rawRestrictions, SearchQuery $searchQuery): void
    {
        $restrictions = [];
        foreach ($rawRestrictions as $rawRestriction) {
            $key = $this->generateRestrictionKey(
                $rawRestriction['date'],
                $rawRestriction['tariff'],
                $rawRestriction['roomType']
            );
            $restrictions[$key] = $rawRestriction;

        }
        $hash = $searchQuery->getSearchHash();
        $this->restrictions[$hash] = $restrictions;
    }

    /**
     * @param $date
     * @param array $tariff
     * @param array $roomType
     * @return string
     */
    private function generateRestrictionKey($date, array $tariff, array $roomType): string
    {
        if ($date instanceof \MongoDate) {
            $date = Helper::convertMongoDateToDate($date);
        }
        return "{$date->format('d-m-Y')}_{$tariff['$id']}_{$roomType['$id']}";
    }

    /**
     * @param \DateTime $date
     * @param string $tariffId
     * @param string $roomTypeId
     * @return string
     */
    private function getAccessRestrictionKey(\DateTime $date, string $tariffId, string $roomTypeId): string
    {
        return "[{$date->format('d-m-Y')}_{$tariffId}_{$roomTypeId}]";
    }


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
            $groupedByDayRoomCaches = $hashedRoomCaches[$searchQuery->getRoomTypeId()];
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

        return $roomCaches;
    }

    /**
     * @param array $rawRoomCaches
     * @param SearchQuery $searchQuery
     */
    private function setRoomCaches(array $rawRoomCaches, SearchQuery $searchQuery): void
    {
        $roomCaches = [];
        foreach ($rawRoomCaches as $rawRoomCache) {
            $roomTypeIdKey = (string)$rawRoomCache['roomType']['$id'];
            $dateKey = Helper::convertMongoDateToDate($rawRoomCache['date'])->format('d-m-Y');
            $roomCaches[$roomTypeIdKey][$dateKey] = $rawRoomCache;
        }
        $hash = $searchQuery->getSearchHash();
        $this->roomCaches[$hash] = $roomCaches;
    }



}