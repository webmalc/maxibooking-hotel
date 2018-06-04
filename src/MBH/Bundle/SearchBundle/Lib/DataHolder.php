<?php


namespace MBH\Bundle\SearchBundle\Lib;


use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
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

    /** @var bool */
    private $isUseCategory;

    /** @var array */
    private $restrictions;

    /**
     * TariffHolder constructor.
     * @param TariffRepository $tariffRepository
     * @param RoomTypeRepository $roomTypeRepository
     * @param ClientConfigRepository $configRepository
     */
    public function __construct(TariffRepository $tariffRepository, RoomTypeRepository $roomTypeRepository, RestrictionRepository $restrictionRepository, ClientConfigRepository $configRepository)
    {
        $this->tariffRepository = $tariffRepository;
        $this->roomTypeRepository = $roomTypeRepository;
        $this->tariffs = $tariffRepository->findAll();
        $this->roomTypes = $roomTypeRepository->findAll();
        $this->restrictionRepository = $restrictionRepository;
        $this->isUseCategory = $configRepository->fetchConfig()->getUseRoomTypeCategory();
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


    public function getCheckNecessaryRestrictions(SearchQuery $searchQuery, SearchConditions $conditions): array
    {
        $hash = $searchQuery->getSearchHash();
        $restrictions = $this->getRestrictions($hash, $searchQuery);

        if (null === $restrictions) {
            $rawRestrictions = $this->restrictionRepository->getWithConditions($conditions);
            $this->setRestrictions($rawRestrictions, $hash);

            return $this->getRestrictions($hash, $searchQuery);
        }

        return $restrictions;

    }

    //** TODO: REDIS MIDDLE CACHE? */


    private function setRestrictions(array $rawRestrictions, string $hash): void
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
        $this->restrictions[$hash] = $restrictions;
    }
    //** TODO: REDIS MIDDLE CACHE? */

    private function getRestrictions(string $hash, SearchQuery $searchQuery): ?array
    {
        $restrictions = [];
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



}