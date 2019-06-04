<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\RoomCacheRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Services\Data\ActualChildOptionDeterminer;

class RoomCacheRawFetcher implements DataRawFetcherInterface
{
    /** @var string  */
    public const NAME = 'roomCacheFetcher';

    /** @var RoomCacheRepository */
    private $roomCacheRepository;
    /**
     * @var ActualChildOptionDeterminer
     */
    private $actualChildOptionDeterminer;

    /**
     * RoomCacheRawFetcher constructor.
     * @param RoomCacheRepository $roomCacheRepository
     */
    public function __construct(RoomCacheRepository $roomCacheRepository, ActualChildOptionDeterminer $actualChildOptionDeterminer)
    {
        $this->roomCacheRepository = $roomCacheRepository;
        $this->actualChildOptionDeterminer = $actualChildOptionDeterminer;
    }

    public function getRawData(DataQueryInterface $dataQuery): array
    {
        $conditions = $dataQuery->getSearchConditions();
        if (!$conditions) {
            throw new DataManagerException('Critical Error in %s fetcher. No SearchConditions in SearchQuery', __CLASS__);
        }

        return $this->roomCacheRepository->fetchRaw($conditions->getMaxBegin(), $conditions->getMaxEnd());
    }

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array
    {
        $tariffId = $this->actualChildOptionDeterminer->getActualRoomTariff($tariffId);

        $allFiltered =  array_filter($data, static function ($priceCache) use ($begin, $end, $tariffId, $roomTypeId) {
            $date = Helper::convertMongoDateToDate($priceCache['date']);
            /** Pay attention, $date < $end   */
            $isDateMatch = $begin <= $date && $date < $end;
            $isRoomTypeMatch = (string)$priceCache['roomType']['$id'] === $roomTypeId;

            $priceCacheTariff = $priceCache['tariff'] ?? null;

            $isTariffMatch = null ===  $priceCacheTariff || (string)$priceCacheTariff['$id'] === $tariffId;

            return $isDateMatch && $isRoomTypeMatch && $isTariffMatch;
        });



        return $this->mergeQuotedRoomCaches($allFiltered, $tariffId);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    private function mergeQuotedRoomCaches(array $roomCaches, string $tariffId): array
    {
        $dateGrouped = [];
        foreach ($roomCaches as $roomCache) {
            $date = Helper::convertMongoDateToDate($roomCache['date']);
            $dateGrouped[$date->format('d.m.Y')][] = $roomCache;
        }

        $result = [];
        foreach ($dateGrouped as $group) {
            $amount = count($group);
            if ($amount === 1) {
                $result[] = reset($group);
            } elseif ($amount === 2) {
                $filteredCaches = array_filter($group, function ($roomCache) use ($tariffId){
                    $tariff = $roomCache['tariff'] ?? null;

                    return $tariff ? (string)$tariff['$id'] === $tariffId : false;
                });
                $result[] = array_merge(...$filteredCaches);
            } else {
                throw new DataManagerException('Room Cache amount more than can be in normal work!');
            }
        }

        return $result;
    }


}