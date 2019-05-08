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
use MBH\Bundle\SearchBundle\Services\Search\Search;

class OnlineSearchAdapter
{
    /** @var Search */
    private $search;
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var SearchFactory
     */
    private $factory;

    /**
     * OnlineSearchAdapter constructor.
     * @param Search $search
     */
    public function __construct(Search $search, DocumentManager $dm, SearchFactory $factory)
    {
        $this->search = $search;
        $this->dm = $dm;
        $this->factory = $factory;
    }


    public function search(SearchQuery $query): array
    {
        $data = $this->searchDataAdaptive($query);
        $searchResults = $this->search->searchSync($data, 'roomTypeCategory');

        return  $this->adaptResults($searchResults);
    }

    private function adaptResults(array $searchResults): array
    {
        $searchResults = $searchResults['success'];
        $adaptedResults = [];
        if (count($searchResults)) {
            foreach ($searchResults as $roomTypeCategoryId => $results) {
                $roomTypeCategory = $this->dm->find(RoomTypeCategory::class, $roomTypeCategoryId);
                $adaptedResults[] = [
                    'roomType' => $roomTypeCategory,
                    'results' => $this->convertNewResultsToOld($results)
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
                $adults = $currentResult['resultConditions']['adults'];
                $children = $currentResult['resultConditions']['children'];
                $searchResult->setAdults($adults);
                $searchResult->setChildren($children);
                $searchResult->setRoomType($this->dm->find(RoomType::class, $currentResult['resultRoomType']['id']));
                $searchResult->setVirtualRoom($this->adaptVirtualRoom($currentResult));
                $tariff = $this->dm->find(Tariff::class, $currentResult['resultTariff']['id']);
                $searchResult->setTariff($tariff);
                $newResultPrices = reset($currentResult['prices']);
                $prices = [
                    //** HotFix when adapt with childrenAge difference */
//                     $newResultPrices['searchAdults'].'_'.$newResultPrices['searchChildren'] => $newResultPrices['total']
                    $adults.'_'.$children => $newResultPrices['total']

                ];
                $searchResult->setPrices($prices);
                $dayPrices = $newResultPrices['dayPrices'];
                $packagePrices = [];
                foreach ($dayPrices as $dayPrice) {
                    $packagePrices[] = new PackagePrice(
                        new \DateTime($dayPrice['date']),
                        $dayPrice['price'],
                        $this->dm->find(Tariff::class, $dayPrice['tariff']['id'])
                    );
                }
                $searchResult->setPackagePrices($packagePrices, $adults, $children);

                $oldResults[] = $searchResult;
            }
        }

        return $oldResults;
    }

    private function adaptVirtualRoom($currentResult)
    {
        $currentVirtualRoomId = $currentResult['virtualRoom']['id'];
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
            'isUseCache' => false
        ];

        return $data;
    }

    private function sortResultByPrice(array $results): array
    {
        usort(
            $results,
            function ($resultA, $resultB) {
                return (int)$resultA['prices'][0]['total'] <=> (int)$resultB['prices'][0]['total'];
            }
        );

        return $results;
    }
}