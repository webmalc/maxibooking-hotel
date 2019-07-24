<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use function count;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
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
use MBH\Bundle\SearchBundle\Services\Calc\Calculation;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\RoomCacheRawFetcher;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminer;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminerEvent;


class SearchResultComposer
{
    /** @var Calculation */
    private $calculation;

    /** @var DataManager */
    private $dataManager;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;
    /**
     * @var AccommodationRoomSearcher
     */
    private $accommodationRoomSearcher;
    /**
     * @var OccupancyDeterminer
     */
    private $determiner;

    /**
     * SearchResultComposer constructor.
     * @param Calculation $calculation
     * @param DataManager $dataManager
     * @param SharedDataFetcher $sharedDataFetcher
     * @param AccommodationRoomSearcher $roomSearcher
     * @param OccupancyDeterminer $determiner
     */
    public function __construct(Calculation $calculation, DataManager $dataManager, SharedDataFetcher $sharedDataFetcher, AccommodationRoomSearcher $roomSearcher, OccupancyDeterminer $determiner)
    {
        $this->calculation = $calculation;
        $this->dataManager = $dataManager;
        $this->sharedDataFetcher = $sharedDataFetcher;
        $this->accommodationRoomSearcher = $roomSearcher;
        $this->determiner = $determiner;
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


        $occupancy = $this->determiner->determine($searchQuery, OccupancyDeterminerEvent::OCCUPANCY_DETERMINER_EVENT_CALCULATION);
        $actualAdults = $occupancy->getAdults();
        $actualChildren = $occupancy->getChildren();

        //** TODO: Цены вынести выше в поиске
        // В дальнейшем цены могут содержать разное кол-во детей и взрослых (инфантов)
        //
        //*/
        $prices = $this->calculation->calcPrices($searchQuery, $actualAdults, $actualChildren);
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
                    $occupancy->getInfants(),
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

//        $accommodationRooms = $this->accommodationRoomSearcher->search($searchQuery);
        $accommodationRooms = [];
        $resultAccommodationRooms = [];
        if (count($accommodationRooms)) {
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


    private function getMinCacheValue(SearchQuery $searchQuery): int
    {
        $roomCaches = $this->dataManager->fetchData($searchQuery, RoomCacheRawFetcher::NAME);

        //** TODO: Когда станет понятно на каком этапе отсекать лимиты, тут переделать. */
        $mainRoomCaches = array_filter(
            $roomCaches,
            static function ($roomCache) {
                $isMainRoomCache = !array_key_exists('tariff', $roomCache) || null === $roomCache['tariff'];

                return $isMainRoomCache && $roomCache['leftRooms'] > 0;
            }
        );


        $min = min(array_column($mainRoomCaches, 'leftRooms'));

        $duration = $searchQuery->getDuration();
        if ($min < 1 || count($mainRoomCaches) !== $duration) {
            throw new SearchResultComposerException('Error! RoomCaches count not equal duration.');
        }

        return $min;

    }
}