<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Normalization\DocumentFieldType;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Document\SearchResultCacheItem;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

class SearchCache
{
    const SEARCH_CRITERIA_FIELDS = [
        'begin',
        'end',
        'adults',
        'children',
        'childrenAges',
        'isOnline',
        'roomTypes',
        'forceRoomTypes',
        'excludeRoomTypes',
        'promotion',
        'special',
        'tariff',
        'range',
        'availableRoomTypes',
        'forceBooking',
        'infants',
    ];
    const DATE_FORMAT = 'd.m.Y';

    private $dm;
    private $fieldsManager;
    private $serializer;
    private $helper;

    public function __construct(DocumentManager $dm, DocumentFieldsManager $fieldsManager, MBHSerializer $serializer, Helper $helper)
    {
        $this->dm = $dm;
        $this->fieldsManager = $fieldsManager;
        $this->serializer = $serializer;
        $this->helper = $helper;
    }

    /**
     * @param SearchQuery $query
     * @return array|null
     */
    public function searchByQuery(SearchQuery $query)
    {
        $searchCriteriaArray = $this->fieldsManager
            ->fillByDocumentFieldsWithFieldNameKeys($query, self::SEARCH_CRITERIA_FIELDS);

        $cacheItem = $this->dm
            ->getRepository('MBHPackageBundle:SearchResultCacheItem')
            ->findOneBy($searchCriteriaArray);

        return is_null($cacheItem) ? null : $this->deserializeCachedResults($cacheItem);
    }

    public function deserializeCachedResults(SearchResultCacheItem $cacheItem)
    {
        $results = json_decode($cacheItem->getSerializedSearchResults(), true);
        if ($cacheItem->isByRoomTypes()) {
            $denormalizedResults = [];
            foreach ($results as $resultsByRoomType) {
                $denormalizedResults[] = [
                    'roomType' => $this->serializer
                        ->denormalizeByFieldType($resultsByRoomType['roomType'], new DocumentFieldType(RoomType::class)),
                    'results' => $this->serializer->denormalizeArrayOfObjects($resultsByRoomType['results'], SearchResult::class)
                ];
            }
        } else {
            $denormalizedResults = $this->serializer->normalizeArrayOfDocuments($results);
        }

        return $denormalizedResults;
    }

    /**
     * @param SearchQuery $query
     * @param $results
     * @param bool $byRoomTypes
     */
    public function saveToCache(SearchQuery $query, $results, $byRoomTypes = true)
    {
        $normalizedResults = [];
        if ($byRoomTypes) {
            foreach ($results as $resultsByRoomType) {
                $normalizedResults[] = [
                    'roomType' => $this->serializer
                        ->normalizeByFieldType($resultsByRoomType['roomType'], new DocumentFieldType(RoomType::class)),
                    'results' => $this->serializer->normalizeArrayOfDocuments($resultsByRoomType['results'])
                ];
            }
        } else {
            $normalizedResults = $this->serializer->normalizeArrayOfDocuments($results);
        }

        $serializedResults = json_encode($normalizedResults);

        $searchCacheItem = (new SearchResultCacheItem())
            ->setSerializedSearchResults($serializedResults)
            ->setByRoomTypes($byRoomTypes)
        ;

        $queryFields = $this->helper->getArrayWithSameKeysAndValues(self::SEARCH_CRITERIA_FIELDS);

        $this->fieldsManager->fillDocumentByAnotherDocumentFields($query, $searchCacheItem, $queryFields);

        $this->dm->persist($searchCacheItem);
        $this->dm->flush();
    }
}