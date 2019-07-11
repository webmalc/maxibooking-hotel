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
     * @param ActualChildOptionDeterminer $actualChildOptionDeterminer
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
            $dateKey = Helper::convertMongoDateToDate($rawRoomCache['date'])->format('d-m-Y');
            $data[$dateKey][] = $rawRoomCache;
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

        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
            /** @var \DateTime $day */
            $dayRoomCaches = $data[$day->format('d-m-Y')] ?? null;
            if (is_array($dayRoomCaches)) {
                $roomCaches[] = array_filter(
                    $dayRoomCaches,
                    static function ($roomCache) use ($tariffId, $roomTypeId) {
                        $isQuotedRoomCache = false;
                        $isPureRoomCache = null === ($roomCache['tariff'] ?? null);
                        if (!$isPureRoomCache) {
                            $isQuotedRoomCache = $tariffId === (string)$roomCache['tariff']['$id'];
                        }
                        $isRoomTypedRoomCache = $roomTypeId === (string)$roomCache['roomType']['$id'];

                        return ($isPureRoomCache || $isQuotedRoomCache) && $isRoomTypedRoomCache;
                    }
                );
            }

        }
        if (is_array($roomCaches)) {
            $roomCaches = array_merge(...$roomCaches);
        }
        return $roomCaches;
    }


    public function getName(): string
    {
        return self::NAME;
    }

}