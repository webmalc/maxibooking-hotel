<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Document\SearchResultCacheItem;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

class SearchCache
{
    const SEARCH_CRITERIA_FIELDS = [
        'begin',
        'end',
        'adults',
        'children',
        'childrenAges',
        'isOnline',
        'forceRoomTypes',
        'excludeRoomTypes',
        'promotion',
        'special',
        'availableRoomTypes',
        'forceBooking',
        'infants',
    ];
    const DATE_FORMAT = 'd.m.Y';

    private $dm;
    private $fieldsManager;
    private $serializer;
    private $helper;
    private $search;
    private $roomTypeManager;

    public function __construct(
        DocumentManager $dm,
        DocumentFieldsManager $fieldsManager,
        MBHSerializer $serializer,
        Helper $helper,
        SearchFactory $search,
        RoomTypeManager $roomTypeManager
    ) {
        $this->dm = $dm;
        $this->fieldsManager = $fieldsManager;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->search = $search;
        $this->roomTypeManager = $roomTypeManager;
    }

    /**
     * @param SearchQuery $query
     * @return array|null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function searchByQuery(SearchQuery $query)
    {
        $searchCriteriaArray = $this->fieldsManager
            ->fillByDocumentFieldsWithFieldNameKeys($query, self::SEARCH_CRITERIA_FIELDS);

        /** @var DocumentRepository $repo */
        $repo = $this->dm->getRepository('MBHPackageBundle:SearchResultCacheItem');
        $cacheItem = $repo
            ->createQueryBuilder()
            ->setQueryArray($searchCriteriaArray)
            ->field('tariff.id')->in($this->helper->toIds($this->getQueryTariffs($query)))
            ->field('roomTypeId')->in($this->getQueryRoomTypesIds($query))
            ->getQuery()
            ->execute()
            ->toArray();

        return empty($cacheItem) ? null : $this->getGroupedDeserializedResults($cacheItem);
    }

    /**
     * @param SearchQuery $query
     * @param $results
     * @param bool $byRoomTypes
     * @throws \ReflectionException
     */
    public function saveToCache(SearchQuery $query, $results, $byRoomTypes = true)
    {
        $queryRoomTypesIds = $this->getQueryRoomTypesIds($query);
        $queryTariffs = $this->getQueryTariffs($query);
        $searchResultsList = $this->getSearchResults($results, $byRoomTypes);

        foreach ($queryTariffs as $tariff) {
            foreach ($queryRoomTypesIds as $roomTypeId) {
                $searchResultsByTariffAndRoomType = array_filter(
                    $searchResultsList,
                    function (SearchResult $result) use ($tariff, $roomTypeId) {
                        return $result->getTariff()->getId() === $tariff->getId()
                            && $result->getRoomType()->getId() === $roomTypeId;
                    }
                );

                $normalizedResult = empty($searchResultsByTariffAndRoomType)
                    ? []
                    : $this->serializer->normalize(current($searchResultsByTariffAndRoomType));

                $serializedResult = json_encode($normalizedResult);

                $searchCacheItem = (new SearchResultCacheItem())
                    ->setTariff($tariff)
                    ->setRoomTypeId($roomTypeId)
                    ->setSerializedSearchResult($serializedResult);

                $queryFields = $this->helper->getArrayWithSameKeysAndValues(self::SEARCH_CRITERIA_FIELDS);
                $this->fieldsManager->fillDocumentByAnotherDocumentFields($query, $searchCacheItem, $queryFields);
                $this->dm->persist($searchCacheItem);
            }
        }

        $this->dm->flush();
    }

    public function clearCache(\DateTime $begin, \DateTime $end, $tariffs = [], $roomTypes = [])
    {

    }

    /**
     * @param SearchResultCacheItem[] $cacheItems
     * @param bool $byRoomTypes
     * @return array
     */
    private function getGroupedDeserializedResults(array $cacheItems, $byRoomTypes = true)
    {
        $deserializedResults = array_map(function(SearchResultCacheItem $cacheItem) {
            $deserializedResult = json_decode($cacheItem->getSerializedSearchResult(), true);

            return $this->serializer->denormalize($deserializedResult, SearchResult::class);
        }, $cacheItems);

        if (!$byRoomTypes) {
            return $deserializedResults;
        }

        $resultsByRoomTypes = [];
        /** @var SearchResult $searchResult */
        foreach ($deserializedResults as $searchResult) {
            $roomTypeId = $searchResult->getRoomType()->getId();
            if (isset($resultsByRoomTypes[$roomTypeId])) {
                $resultsByRoomTypes[$roomTypeId]['results'][] = $searchResult;
            } else {
                $resultsByRoomTypes[$roomTypeId] = [
                    'results' => [$searchResult],
                    'roomType' => $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId)
                ];
            }
        }
        $resultsByRoomTypes = array_values($resultsByRoomTypes);

        return $resultsByRoomTypes;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    private function getQueryRoomTypesIds(SearchQuery $query): array
    {
        if (count($query->roomTypes) > 0) {
            $queryRoomTypesIds = $query->roomTypes;
        } else {
            $queryRoomTypesIds = $this->helper->toIds($this->roomTypeManager->getRooms());
        }

        return $queryRoomTypesIds;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    private function getQueryTariffs(SearchQuery $query): array
    {
        if (is_null($query->tariff)) {
            $queryTariffs = $this->search->searchTariffs($query);
        } else {
            $queryTariffs = [$query->tariff];
        }

        return $queryTariffs;
    }

    /**
     * @param $results
     * @param $byRoomTypes
     * @return array
     */
    private function getSearchResults($results, $byRoomTypes): array
    {
        if (!$byRoomTypes) {
            return $results;
        }

        $searchResultsList = [];
        foreach ($results as $resultsByRoomType) {
            /** @var SearchResult $searchResultByTariffAndRoomType */
            foreach ($resultsByRoomType['results'] as $searchResultByTariffAndRoomType) {
                $searchResultsList[] = $searchResultByTariffAndRoomType;
            }
        };

        return $searchResultsList;
    }
}