<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\Data\RoomCacheFetchQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\AccommodationRoomSearcher;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;
use MBH\Bundle\SearchBundle\Services\Calc\Calculation;
use MBH\Bundle\SearchBundle\Services\Data\RoomCacheFetcher;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;


class SearchResultComposer
{
    /** @var RoomTypeManager */
    private $roomManager;

    /** @var Calculation */
    private $calculation;

    private $limitChecker;

    /** @var RoomCacheFetcher */
    private $roomCacheFetcher;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;
    /**
     * @var AccommodationRoomSearcher
     */
    private $accommodationRoomSearcher;

    /**
     * SearchResultComposer constructor.
     * @param RoomTypeManager $roomManager
     * @param Calculation $calculation
     * @param SearchLimitChecker $limitChecker
     * @param RoomCacheFetcher $roomCacheFetcher
     * @param SharedDataFetcher $sharedDataFetcher
     * @param AccommodationRoomSearcher $roomSearcher
     */
    public function __construct(RoomTypeManager $roomManager, Calculation $calculation, SearchLimitChecker $limitChecker, RoomCacheFetcher $roomCacheFetcher, SharedDataFetcher $sharedDataFetcher, AccommodationRoomSearcher $roomSearcher)
    {
        $this->roomManager = $roomManager;
        $this->calculation = $calculation;
        $this->limitChecker = $limitChecker;
        $this->roomCacheFetcher = $roomCacheFetcher;
        $this->sharedDataFetcher = $sharedDataFetcher;
        $this->accommodationRoomSearcher = $roomSearcher;
    }


    public function composeResult(SearchQuery $searchQuery): SearchResult
    {
        $searchResult = new SearchResult();
        $roomType = $this->sharedDataFetcher->getFetchedRoomType($searchQuery->getRoomTypeId());
        $tariff = $this->sharedDataFetcher->getFetchedTariff($searchQuery->getTariffId());
        if (!$roomType || !$tariff) {
            throw new SearchResultComposerException('Can not get Tariff or RoomType');
        }
        $this->limitChecker->checkTariffConditions($searchQuery);
        $minCache = $this->getMinCacheValue($searchQuery);
        $isUseCategories = $this->roomManager->useCategories;
        $actualAdults = $searchQuery->getActualAdults();
        $actualChildren = $searchQuery->getActualChildren();
        $infants = $searchQuery->getInfants();

        $accommodationRooms = $this->accommodationRoomSearcher->search($searchQuery);

        $prices = $this->getPrices($searchQuery, $roomType, $tariff, $actualAdults, $actualChildren);

        $searchResult
            ->setBegin($searchQuery->getBegin())
            ->setEnd($searchQuery->getEnd())
            ->setTariff($tariff)
            ->setRoomType($roomType)
            ->setRoomsCount($minCache)
            ->setAdults($actualAdults)
            ->setChildren($actualChildren)
            ->setUseCategories($isUseCategories)
            ->setInfants($infants)
            ->setRooms($accommodationRooms)
            ->setQueryId($searchQuery->getSearchConditions()->getId())
            ->setForceBooking($searchQuery->isForceBooking())
        ;
        $this->pricePopulate($searchResult, $prices);

        return $searchResult;
    }


    private function getPrices(SearchQuery $searchQuery, RoomType $roomType, Tariff $tariff, int $actualAdults, int $actualChildren): array
    {
        $conditions = $searchQuery->getSearchConditions();
        $calcQuery = new CalcQuery();
        $calcQuery
            ->setSearchBegin($searchQuery->getBegin())
            ->setSearchEnd($searchQuery->getEnd())
            ->setRoomType($roomType)
            ->setTariff($tariff)
            ->setActualAdults($actualAdults)
            ->setActualChildren($actualChildren)
            ->setIsUseCategory($this->roomManager->useCategories)
            //** TODO: Уточнить по поводу Promotion */
            /*->setPromotion()*/
            /** TODO: Это все необязательные поля, нужны исключительно для dataHolder чтоб получить все данные сразу */
            ;
        if ($conditions) {
            $calcQuery
                ->setConditionTariffs($conditions->getTariffs())
                ->setConditionRoomTypes($conditions->getRoomTypes())
                ->setConditionMaxBegin($conditions->getMaxBegin())
                ->setConditionMaxEnd($conditions->getMaxEnd())
                ->setConditionHash($conditions->getSearchHash())
            ;
        }


        $prices = $this->calculation->calcPrices($calcQuery);
        if (!\count($prices)) {
            throw new SearchResultComposerException('No prices returned from calculation');
        }

        return $prices;
    }

    private function pricePopulate(SearchResult $searchResult, array $prices): void

    {
        foreach ($prices as $price) {
            $searchResult
                ->addPrice($price['total'], $price['adults'], $price['children'])
                ->setPackagePrices($price['packagePrices'], $price['adults'], $price['children']);
        }
    }


    private function getMinCacheValue(SearchQuery $searchQuery): int
    {
        $roomCacheFetchQuery = RoomCacheFetchQuery::createInstanceFromSearchQuery($searchQuery);
        $roomCaches = $this->roomCacheFetcher->fetchNecessaryDataSet($roomCacheFetchQuery);

        $min = min(array_column($roomCaches, 'leftRooms'));
        $duration = $searchQuery->getDuration();
        if ($min < 1 || \count($roomCaches) !== $duration) {
            throw new SearchResultComposerException('Error! RoomCaches count not equal duration');
        }

        return $min;

    }
}