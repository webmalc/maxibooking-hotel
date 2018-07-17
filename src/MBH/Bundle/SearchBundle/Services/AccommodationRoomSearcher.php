<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Lib\Data\PackageAccommodationFetchQuery;
use MBH\Bundle\SearchBundle\Lib\Data\RoomFetchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\DataFetcherInterface;
use MBH\Bundle\SearchBundle\Services\Data\PackageAccommodationFetcher;
use MBH\Bundle\SearchBundle\Services\Data\RoomFetcher;

class AccommodationRoomSearcher
{

    /** @var DataFetcherInterface|RoomFetcher */
    private $roomFetcher;

    /** @var DataFetcherInterface|PackageAccommodationFetcher */
    private $packageAccommodationFetcher;

    /**
     * AccommodationRoomSearcher constructor.
     * @param DataFetcherInterface $roomFetcher
     * @param DataFetcherInterface $packageAccommodationFetcher
     */
    public function __construct(DataFetcherInterface $roomFetcher, DataFetcherInterface $packageAccommodationFetcher)
    {
        $this->roomFetcher = $roomFetcher;
        $this->packageAccommodationFetcher = $packageAccommodationFetcher;
    }


    public function search(SearchQuery $searchQuery): array
    {
        $packageAccommodationQuery = PackageAccommodationFetchQuery::createInstanceFromSearchQuery($searchQuery);
        $packageAccommodations = $this->packageAccommodationFetcher->fetchNecessaryDataSet($packageAccommodationQuery);
        $forExcludeRoomsIds = $this->findIntersectWithDates($searchQuery->getBegin(), $searchQuery->getEnd(), $packageAccommodations);
        $roomQuery = RoomFetchQuery::createInstanceFromSearchQuery($searchQuery);
        $allRooms = $this->roomFetcher->fetchNecessaryDataSet($roomQuery);
        $allRoomsIds = array_map('\strval', array_column($allRooms, '_id'));
        $accommodationRoomsIds = array_diff($allRoomsIds, $forExcludeRoomsIds);
        $accommodationRooms = array_filter($allRooms, function ($room) use ($accommodationRoomsIds) {
            return \in_array((string)$room['_id'], $accommodationRoomsIds, true);
        });

        return $accommodationRooms;

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
}