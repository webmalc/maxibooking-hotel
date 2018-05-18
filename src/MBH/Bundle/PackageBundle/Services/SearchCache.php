<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\BaseBundle\Document\SearchResultCacheItem;

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
        'limit'
    ];
    const DATE_FORMAT = 'd.m.Y';

    private $dm;
    private $fieldsManager;
    private $serializer;

    public function __construct(DocumentManager $dm, DocumentFieldsManager $fieldsManager, MBHSerializer $serializer)
    {
        $this->dm = $dm;
        $this->fieldsManager = $fieldsManager;
        $this->serializer = $serializer;
    }

    /**
     * @param SearchQuery $query
     * @return SearchResultCacheItem|null
     */
    public function searchByQuery(SearchQuery $query)
    {
        $searchCriteriaArray = $this->fieldsManager
            ->fillByDocumentFieldsWithFieldNameKeys($query, self::SEARCH_CRITERIA_FIELDS);

        return $this->dm
            ->getRepository('MBHBaseBundle:SearchResultCacheItem')
            ->findOneBy($searchCriteriaArray);
    }

    /**
     * @param SearchQuery $query
     * @param $results
     * @throws \ReflectionException
     */
    public function saveToCache(SearchQuery $query, $results)
    {
        $serializedResults = [];
        if ($query->grouped || true) {
            foreach ($results as $resultsByRoomType) {
                $roomTypeResults = [];
                /** @var SearchResult $searchResult */
                foreach ($resultsByRoomType['results'] as $searchResult) {
                    $roomTypeResults[] = $this->serializer->normalize($searchResult);
                }

                /** @var RoomType $roomType */
                $roomType = $resultsByRoomType['roomType'];
                $serializedResults[] = [
                    'roomTypeId' => $roomType->getId(),
                    'results' => $roomTypeResults
                ];
            }
        }
        $sdf = 123;
    }
}