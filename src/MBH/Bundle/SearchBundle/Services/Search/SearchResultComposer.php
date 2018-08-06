<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Data\RoomCacheFetchQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoom;
use MBH\Bundle\SearchBundle\Lib\Result\ResultConditions;
use MBH\Bundle\SearchBundle\Lib\Result\ResultDayPrice;
use MBH\Bundle\SearchBundle\Lib\Result\ResultPrice;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;
use MBH\Bundle\SearchBundle\Lib\Result\ResultTariff;
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


    public function composeResult(SearchQuery $searchQuery): Result
    {
        $roomType = $this->sharedDataFetcher->getFetchedRoomType($searchQuery->getRoomTypeId());
        $tariff = $this->sharedDataFetcher->getFetchedTariff($searchQuery->getTariffId());
        if (!$roomType || !$tariff) {
            throw new SearchResultComposerException('Can not get Tariff or RoomType');
        }

        $resultRoomType = ResultRoomType::createInstance($roomType);
        $resultTariff = ResultTariff::createInstance($tariff);

        $actualAdults = $searchQuery->getActualAdults();
        $actualChildren = $searchQuery->getActualChildren();
        $infants = $searchQuery->getInfants();

        //** TODO: Цены вынести выше в поиске
        // В дальнейшем цены могут содержать разное кол-во детей и взрослых (инфантов)
        //
        //*/
        $prices = $this->getPrices($searchQuery, $roomType, $tariff, $actualAdults, $actualChildren);
        $combinations = array_keys($prices);
        $resultPrices = [];
        foreach ($combinations as $combination) {
            [$adults, $children] = explode('_', $combination);
            $currentPrice = $prices[$combination];
            $resultPrice = ResultPrice::createInstance(
                $adults,
                $children ?? 0,
                $currentPrice['total']);
            $packagePrices = $currentPrice['packagePrices'];
            foreach ($packagePrices as $packagePrice) {
                /** @var PackagePrice $packagePrice */
                $dayTariff = ResultTariff::createInstance($packagePrice->getTariff());
                $dayPrice = ResultDayPrice::createInstance(
                    $packagePrice->getDate(),
                    $adults,
                    $children,
                    $infants,
                    $packagePrice->getPrice(),
                    $dayTariff);
                $resultPrice->addDayPrice($dayPrice);
            }
            $resultPrices[] = $resultPrice;
        }

        $conditions = $searchQuery->getSearchConditions();
        if (!$conditions || null === $conditions->getId()) {
            throw new SearchResultComposerException('No conditions or conditions id in SearchQuery. Critical search error');
        }
        $resultConditions = ResultConditions::createInstance($conditions);

        $accommodationRooms = $this->accommodationRoomSearcher->search($searchQuery);
        $resultAccommodationRooms = [];
        if (!\count($accommodationRooms)) {
            foreach ($accommodationRooms as $accommodationRoom) {
                $resultAccommodationRoom = new ResultRoom();
                $resultAccommodationRoom
                    ->setId((string)$accommodationRoom['id'])
                    ->setName($accommodationRoom['fullTitle'] ?? $accommodationRoom['title'] ?? '');
                $resultAccommodationRooms[] = $resultAccommodationRoom;
            }
        }
        $minRoomsCount = $this->getMinCacheValue($searchQuery);

        $result = Result::createInstance(
            $searchQuery->getBegin(),
            $searchQuery->getEnd(),
            $resultConditions,
            $resultTariff,
            $resultRoomType,
            $resultPrices,
            $minRoomsCount,
            $resultAccommodationRooms)
        ;

        return $result;
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
                ->setConditionHash($conditions->getSearchHash());
        }


        return $this->calculation->calcPrices($calcQuery);
    }


    private function getMinCacheValue(SearchQuery $searchQuery): int
    {
        $roomCacheFetchQuery = RoomCacheFetchQuery::createInstanceFromSearchQuery($searchQuery);
        $roomCaches = $this->roomCacheFetcher->fetchNecessaryDataSet($roomCacheFetchQuery);

        //** TODO: Когда станет понятно на каком этапе отсекать лимиты, тут переделать. */
        $mainRoomCaches = array_filter(
            $roomCaches,
            function ($roomCache) {
                $isMainRoomCache = !array_key_exists('tariff', $roomCache) || null === $roomCache['tariff'];

                return $isMainRoomCache && $roomCache['leftRooms'] > 0;
            }
        );


        $min = min(array_column($mainRoomCaches, 'leftRooms'));

        $duration = $searchQuery->getDuration();
        if ($min < 1 || \count($mainRoomCaches) !== $duration) {
            throw new SearchResultComposerException('Error! RoomCaches count not equal duration.');
        }

        return $min;

    }
}