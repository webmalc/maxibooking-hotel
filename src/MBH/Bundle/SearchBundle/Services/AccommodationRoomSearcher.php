<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Lib\Data\PackageAccommodationFetchQuery;
use MBH\Bundle\SearchBundle\Lib\Data\RoomFetchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\DataFetcherInterface;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\PackageAccommodationRawFetcher;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\RoomRawFetcher;
use MBH\Bundle\SearchBundle\Services\Data\PackageAccommodationFetcher;
use MBH\Bundle\SearchBundle\Services\Data\RoomFetcher;

class AccommodationRoomSearcher
{

    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * AccommodationRoomSearcher constructor.
     * @param DataManager $dataManager
     */
    public function __construct(DataManager $dataManager)
    {
        $this->dataManager = $dataManager;
    }


    public function search(SearchQuery $searchQuery): array
    {
//        $packageAccommodationQuery = PackageAccommodationFetchQuery::createInstanceFromSearchQuery($searchQuery);
//        $packageAccommodations = $this->packageAccommodationFetcher->fetchNecessaryDataSet($packageAccommodationQuery);

        $packageAccommodations = $this->dataManager->fetchData($searchQuery, PackageAccommodationRawFetcher::NAME);

        $rooms = array_column($packageAccommodations, 'accommodation');
        $busyRoomIds = array_map('\strval', array_column($rooms, '$id'));


//        $roomQuery = RoomFetchQuery::createInstanceFromSearchQuery($searchQuery);
//        $allRooms = $this->roomFetcher->fetchNecessaryDataSet($roomQuery);

        $allRooms = $this->dataManager->fetchData($searchQuery, RoomRawFetcher::NAME);


        $allRoomsIds = array_map('\strval', array_column($allRooms, '_id'));
        $accommodationRoomsIds = array_diff($allRoomsIds, $busyRoomIds);
        $accommodationRooms = array_filter($allRooms, function ($room) use ($accommodationRoomsIds) {
            return \in_array((string)$room['_id'], $accommodationRoomsIds, true);
        });

        return $accommodationRooms;

    }

}