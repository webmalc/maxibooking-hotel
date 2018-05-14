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
use MBH\Bundle\SearchBundle\Services\Calculation;


class SearchResultComposer
{

    /** @var DocumentManager */
    private $dm;

    /** @var RoomTypeManager */
    private $roomManager;

    /** @var Calculation */
    private $calculation;

    /**
     * SearchResultComposer constructor.
     * @param DocumentManager $dm
     * @param RoomTypeManager $roomManager
     * @param Calculation $calculation
     */
    public function __construct(DocumentManager $dm, RoomTypeManager $roomManager, Calculation $calculation)
    {
        $this->dm = $dm;
        $this->roomManager = $roomManager;
        $this->calculation = $calculation;

    }


    public function composeResult(SearchResult $searchResult, SearchQuery $searchQuery, RoomType $roomType, Tariff $tariff, array $roomCaches): SearchResult
    {

        $minCache = $this->getMinCacheValue($searchQuery, $roomCaches);
        $isUseCategories = $this->roomManager->useCategories;
        $adults = $searchQuery->getActualAdults();
        $children = $searchQuery->getActualChildren();
        $infants = $searchQuery->getInfants();
        $accommodationRooms = $this->getAccommodationRooms($searchQuery, $roomType);
        //* TODO: Promotion  */
        //* TODO: check windows */
        $priceEnd = (clone $searchQuery->getEnd())->modify('-1 day');
        $prices = $this->getPrices($searchQuery, $roomType, $tariff, $adults, $children);

        $searchResult
            ->setBegin($searchQuery->getBegin())
            ->setEnd($priceEnd)
            ->setTariff($tariff)
            ->setRoomType($roomType)
            ->setRoomsCount($minCache)
            ->setAdults($adults)
            ->setChildren($children)
            ->setUseCategories($isUseCategories)
            ->setInfants($infants)
            ->setRooms($accommodationRooms)

        ;
        $this->pricePopulate($searchResult, $prices);

        return $searchResult;
    }

    private function pricePopulate(SearchResult $searchResult, array $prices): void

    {
        foreach ($prices as $price) {
            $searchResult->addPrice($price['total'], $price['adults'], $price['children'])
                ->setPricesByDate($price['prices'], $price['adults'], $price['children'])
                ->setPackagePrices($price['packagePrices'], $price['adults'], $price['children'])
            ;
        }
    }

    private function getPrices(SearchQuery $searchQuery, RoomType $roomType, Tariff $tariff, int $adults, int $children): array
    {
        $prices = $this->calculation->calcPrices(
            $roomType,
            $tariff,
            $searchQuery->getBegin(),
            (clone $searchQuery->getEnd())->modify('-1 day'),
            $adults,
            $children

        );

        return $prices;
    }


    /**
     * @param SearchQuery $searchQuery
     * @param RoomType $roomType
     * @return array
     * TODO: Наборосок, нужно внимательно разобраться с темой подбора комнаты для размещения.
     */
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
        $duration = (int)$searchQuery->getEnd()->diff($searchQuery->getBegin())->format('%a');
        $min = min(array_column($roomCaches, 'leftRooms'));

        if ($min < 1 || \count($roomCaches) !== $duration) {
            throw new SearchResultComposerException('Error! RoomCaches count not equal duration');
        }

        return $min;

    }
}