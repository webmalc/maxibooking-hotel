<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Lib\Data\RoomCacheFetchQuery;

class RoomCacheHolder implements DataHolderInterface
{

    private $data;

    public function set(DataFetchQueryInterface $fetchQuery, array $data): void
    {
        $hash = $fetchQuery->getHash();
        $groupedByRoomTypeRoomCaches = [];
        foreach ($data as $rawRoomCache) {
            $roomTypeIdKey = (string)$rawRoomCache['roomType']['$id'];
            $dateKey = Helper::convertMongoDateToDate($rawRoomCache['date'])->format('d-m-Y');
            $groupedByRoomTypeRoomCaches[$roomTypeIdKey][$dateKey][] = $rawRoomCache;
        }
        $this->data[$hash] = $groupedByRoomTypeRoomCaches;
    }


    /**
     * @param RoomCacheFetchQuery|DataFetchQueryInterface $fetchQuery
     * @return array|null
     */
    public function get(DataFetchQueryInterface $fetchQuery): ?array
    {
        $roomCaches = [];
        $hash = $fetchQuery->getHash();
        $hashedRoomCaches = $this->data[$hash] ?? null;
        if (null === $hashedRoomCaches) {
            return null;
        }

        $roomTypeId = $fetchQuery->getRoomTypeId();
        if (!empty($hashedRoomCaches)) {
            $groupedByDayRoomCaches = $hashedRoomCaches[$roomTypeId] ?? [];
            if (!\count($groupedByDayRoomCaches)) {
                return [];
            }
            $begin = $fetchQuery->getBegin();
            $end = $fetchQuery->getEnd();
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

}