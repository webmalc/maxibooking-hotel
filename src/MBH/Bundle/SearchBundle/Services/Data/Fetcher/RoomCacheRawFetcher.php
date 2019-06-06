<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\RoomCacheRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;
use MBH\Bundle\SearchBundle\Services\Data\ActualChildOptionDeterminer;

class RoomCacheRawFetcher implements DataRawFetcherInterface
{
    /** @var string */
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
    public function __construct(
        RoomCacheRepository $roomCacheRepository,
        ActualChildOptionDeterminer $actualChildOptionDeterminer
    ) {
        $this->roomCacheRepository = $roomCacheRepository;
        $this->actualChildOptionDeterminer = $actualChildOptionDeterminer;
    }

    public function getRawData(DataQueryInterface $dataQuery): array
    {
        $conditions = $dataQuery->getSearchConditions();
        if (!$conditions) {
            throw new DataManagerException(
                'Critical Error in %s fetcher. No SearchConditions in SearchQuery', __CLASS__
            );
        }

        $rawData = $this->roomCacheRepository->fetchRaw($conditions->getMaxBegin(), $conditions->getMaxEnd());
        $data = [];
        foreach ($rawData as $rawRoomCache) {
            $roomTypeIdKey = (string)$rawRoomCache['roomType']['$id'];
            $dateKey = Helper::convertMongoDateToDate($rawRoomCache['date'])->format('d-m-Y');
            $data[$roomTypeIdKey][$dateKey][] = $rawRoomCache;
        }

        return $data;
    }

    public function getExactData(
        DateTime $begin,
        DateTime $end,
        string $tariffId,
        string $roomTypeId,
        array $data
    ): array {

        $tariffId = $this->actualChildOptionDeterminer->getActualRoomTariff($tariffId);
        $roomCaches = [];
        $groupedRoomCaches = $data[$roomTypeId] ?? [];

        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
            /** @var \DateTime $day */
            $dayRoomCaches = $groupedRoomCaches[$day->format('d-m-Y')] ?? null;
            if (is_array($dayRoomCaches)) {
                $roomCaches[] = array_reduce(
                    $dayRoomCaches,
                    static function ($carry, $roomCache) use ($tariffId) {
                        if (null === $carry && !isset($roomCache['tariff'])) {
                            $carry = $roomCache;
                        }
                        if (isset($roomCache['tariff']) && (string)$roomCache['tariff']['$id'] === $tariffId) {
                            $carry = $roomCache;
                        }

                        return $carry;
                    }
                );
            }

        }

        return $roomCaches;
    }


    public function getName(): string
    {
        return self::NAME;
    }

}