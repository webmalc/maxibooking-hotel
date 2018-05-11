<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;


class SearchResultComposer
{

    /** @var DocumentManager */
    private $dm;

    /** @var RoomTypeManager */
    private $roomManager;

    /**
     * SearchResultComposer constructor.
     * @param DocumentManager $dm
     * @param RoomTypeManager $roomManager
     */
    public function __construct(DocumentManager $dm, RoomTypeManager $roomManager)
    {
        $this->dm = $dm;
        $this->roomManager = $roomManager;
    }


    public function composeResult(SearchResult $searchResult, SearchQuery $searchQuery, RoomType $roomType, Tariff $tariff, array $roomCaches): SearchResult
    {

        $minCache = $this->getMinCacheValue($searchQuery, $roomCaches);
        $isUserCategories = $this->roomManager->useCategories;
        $adults = $searchQuery->getActualAdults();
        $children = $searchQuery->getActualChildren();
        $infants = $searchQuery->getInfants();
        $tourists = $roomType->getAdultsChildrenCombination($searchQuery->getActualAdults(), $searchQuery->getActualChildren(), $isUserCategories);
        $accomodationRoom = $this->getAccommodationRooms($searchQuery, $roomType);
        return $searchResult;
    }


    private function getAccommodationRooms(SearchQuery $searchQuery, RoomType $roomType): array
    {
        $begin = $searchQuery->getBegin();
        $end = $searchQuery->getEnd();

        $repo = $this->dm->getRepository(Room::class);

        //** TODO: Здесь не очень понятен момент с размещением ибо при множественном может быть коллизия */
        return $repo->fetchRawAccommodationRooms($begin, $end, $roomType->getId());
    }

    private function getMinCacheValue(SearchQuery $searchQuery, array $roomCaches): int
    {
        $duration = (int) $searchQuery->getEnd()->diff($searchQuery->getBegin())->format('%a');
        $min =  min(array_column($roomCaches, 'leftRooms'));

        if ($min < 1 || \count($roomCaches) !== $duration) {
            throw new SearchResultComposerException('Error! RoomCaches count not equal duration');
        }

        return $min;

    }
}