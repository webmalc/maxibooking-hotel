<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Lib\Data\PackageAccommodationFetchQuery;
use MongoDate;

class PackageAccommodationHolder implements DataHolderInterface
{

    protected $data;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;

    /**
     * PackageAccommodationHolder constructor.
     * @param SharedDataFetcher $sharedDataFetcher
     */
    public function __construct(SharedDataFetcher $sharedDataFetcher)
    {
        $this->sharedDataFetcher = $sharedDataFetcher;
    }

    /**
     * @param DataFetchQueryInterface|PackageAccommodationFetchQuery $fetchQuery
     * @return array|null
     */
    public function get(DataFetchQueryInterface $fetchQuery): ?array
    {
        $roomTypeId = $fetchQuery->getRoomTypeId();
        $hash = $fetchQuery->getHash();
        $hashed = $this->data[$hash] ?? null;
        if (null === $hashed) {
            return null;
        }

        return $this->data[$hash][$roomTypeId] ?? [];
    }

    public function set(DataFetchQueryInterface $fetchQuery, array $data): void
    {
        $hash = $fetchQuery->getHash();
        $accommodationGroupedByRoomType = [];

        foreach ($data as $packageAccommodation) {
            $roomId = (string)$packageAccommodation['accommodation']['$id'];
            $roomTypeId = $this->sharedDataFetcher->getRoomTypeIdOfRoomId($roomId);
            $accommodationDateKey = $this->createAccommodationDateKey($packageAccommodation['begin'], $packageAccommodation['end']);
            $accommodationGroupedByRoomType[$roomTypeId][$accommodationDateKey][] = $packageAccommodation;
        }

        $this->data[$hash] = $accommodationGroupedByRoomType;
    }

    private function createAccommodationDateKey(MongoDate $begin, MongoDate $end): string
    {
        $keyBegin = Helper::convertMongoDateToDate($begin)->format('d-m-Y');
        $keyEnd = Helper::convertMongoDateToDate($end)->format('d-m-Y');

        return sprintf('%s_%s', $keyBegin, $keyEnd);
    }

}