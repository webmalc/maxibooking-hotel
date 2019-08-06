<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoom;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Search\FinalSearchResultsAnswerManager;
use MBH\Bundle\SearchBundle\Services\Search\Search;

class OnlineSearchAdapter
{
    /** @var Search */
    private $search;
    /**
     * @var DocumentManager
     */
    private $dm;

    /** @var SharedDataFetcher */
    private $dataFetcher;

    /**
     * @var SearchFactory
     */
    private $factory;
    /**
     * @var FinalSearchResultsAnswerManager
     */
    private $answerManager;

    /**
     * OnlineSearchAdapter constructor.
     * @param Search $search
     */
    public function __construct(
        Search $search,
        DocumentManager $dm,
        SearchFactory $factory,
        SharedDataFetcher $dataFetcher,
        FinalSearchResultsAnswerManager $answerManager
    ) {
        $this->search = $search;
        $this->dm = $dm;
        $this->factory = $factory;
        $this->dataFetcher = $dataFetcher;
        $this->answerManager = $answerManager;
    }


    public function search(SearchQuery $query): array
    {
        $data = $this->searchDataAdaptive($query);
        $searchResults = $this->search->searchSync($data, null, false, false, false);
        $searchResults = $this->filterAzovskyOnline($searchResults);
        /** Костыль для азовского. Нужно из поиска выносить формирование результатов и делать класс для результатов с методами создания различных вариантов */
        $searchResults = $this->answerManager->createAnswer($searchResults, 0, false, false, 'roomTypeCategory');

        return $this->adaptResults($searchResults);
    }

    private function adaptResults(array $searchResults): array
    {
        $searchResults = $searchResults['success'];
        $adaptedResults = [];
        if (count($searchResults)) {
            foreach ($searchResults as $roomTypeCategoryId => $results) {
                $roomTypeCategory = $this->dataFetcher->getFetchedCategory($roomTypeCategoryId);
                $adaptedResults[] = [
                    'roomType' => $roomTypeCategory,
                    'results' => $this->convertNewResultsToOld($results),
                ];
            }
        }

        return $adaptedResults;
    }

    private function convertNewResultsToOld(array $newResults): array
    {
        $oldResults = [];
        foreach ($newResults['results'] as $currentResults) {
            if (\count($currentResults)) {
                $currentResults = $this->sortResultByPrice($currentResults);
            }
            foreach ($currentResults as $currentResult) {
                $searchResult = new SearchResult();
                $searchResult->setBegin(new \DateTime($currentResult['begin']));
                $searchResult->setEnd(new \DateTime($currentResult['end']));
                $adults = $currentResult['adults'];
                $children = $currentResult['children'];
                $searchResult->setAdults($adults);
                $searchResult->setChildren($children);
                $searchResult->setRoomType($this->dataFetcher->getFetchedRoomType($currentResult['roomType']));
                $searchResult->setVirtualRoom($this->adaptVirtualRoom($currentResult));
                $tariff = $this->dataFetcher->getFetchedTariff($currentResult['tariff']);
                $searchResult->setTariff($tariff);
                $newResultPrices = reset($currentResult['prices']);
                $priceAdults = $newResultPrices['adults'];
                $priceChildren = $newResultPrices['children'];
                $prices = [
                    //** HotFix when adapt with childrenAge difference */
//                     $newResultPrices['searchAdults'].'_'.$newResultPrices['searchChildren'] => $newResultPrices['total']
                    $priceAdults.'_'.$priceChildren => $newResultPrices['total'],

                ];
                $searchResult->setPrices($prices);
                $dayPrices = $newResultPrices['priceByDay'];
                $packagePrices = [];
                foreach ($dayPrices as $dayPrice) {
                    $packagePrices[] = new PackagePrice(
                        new \DateTime($dayPrice['date']),
                        $dayPrice['total'],
                        $this->dm->find(Tariff::class, $dayPrice['tariff'])
                    );
                }
                $searchResult->setPackagePrices($packagePrices, $priceAdults, $priceChildren);

                $oldResults[] = $searchResult;
            }
        }

        return $oldResults;
    }

    private function adaptVirtualRoom($currentResult)
    {
        $currentVirtualRoomId = $currentResult['virtualRoom'] ?? ResultRoom::FAKE_VIRTUAL_ROOM_ID;
        if ($currentVirtualRoomId !== ResultRoom::FAKE_VIRTUAL_ROOM_ID) {
            return $this->dm->find(Room::class, $currentVirtualRoomId);
        }

        $room = new Room();
        $room->setId(ResultRoom::FAKE_VIRTUAL_ROOM_ID)->setTitle($currentResult['virtualRoom']['name'] ?? 'errorName');

        return $room;

    }


    private function searchDataAdaptive(SearchQuery $searchQuery): array
    {
        $data = [
            'children' => $searchQuery->children,
            'adults' => $searchQuery->adults,
            'childrenAges' => $searchQuery->childrenAges,
            'begin' => $searchQuery->begin->format('d.m.Y'),
            'end' => $searchQuery->end->format('d.m.Y'),
            'isOnline' => $searchQuery->isOnline,
            'roomTypes' => $searchQuery->roomTypes,
            'additionalBegin' => $searchQuery->range,
            'additionalEnd' => $searchQuery->range,
            'isUseCache' => false,
        ];

        return $data;
    }

    private function sortResultByPrice(array $results): array
    {
        usort(
            $results,
            static function ($resultA, $resultB) {
                return (int)$resultA['prices'][0]['total'] <=> (int)$resultB['prices'][0]['total'];
            }
        );

        return $results;
    }

    private function filterAzovskyOnline(array $results): array
    {
        $dated = [];
        foreach ($results as $result) {
            $begin = $result['begin'];
            $end = $result['end'];
            $tariff = $result['tariff'];
            $category = $result['roomTypeCategory'];
            $key = implode('_', [$begin, $end, $tariff, $category]);
            $dated[$key][] = $result;
        }

        $excludeKeys = [];
        foreach ($dated as $key => $grouped) {
            if (count($grouped) > 1) {
                $roomTypes = array_column($grouped, 'roomType');
                $exclude = $this->getRoomTypeForExclude($roomTypes);
                foreach ($exclude as $roomTypeKey) {
                    $excludeKeys[] = $key.'_'.$roomTypeKey;
                }
            }
        }

        $filtered = array_filter(
            $results,
            static function ($result) use ($excludeKeys){
                $begin = $result['begin'];
                $end = $result['end'];
                $tariff = $result['tariff'];
                $category = $result['roomTypeCategory'];
                $roomType = $result['roomType'];
                $key = implode('_', [$begin, $end, $tariff, $category, $roomType]);

                return !in_array($key, $excludeKeys, true);
            }
        );


        return $filtered;
    }

    private function getRoomTypeForExclude(array $roomTypeIds): array
    {
        $roomTypes = [];
        foreach ($roomTypeIds as $roomTypeId) {
            $roomTypes[] = $this->dataFetcher->getFetchedRoomType($roomTypeId);
        }
        usort(
            $roomTypes,
            static function ($roomType1, $roomType2) {
                /** @var RoomType $roomType1 */
                /** @var RoomType $roomType2 */
                return $roomType1->getTotalPlaces() <=> $roomType2->getTotalPlaces();
            }
        );

        array_shift($roomTypes);

        $exclude = [];
        foreach ($roomTypes as $roomType) {
            $exclude[] = $roomType->getId();
        }

        return $exclude;
    }


}