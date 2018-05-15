<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
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

    public function __construct(DocumentManager $dm, DocumentFieldsManager $fieldsManager)
    {
        $this->dm = $dm;
        $this->fieldsManager = $fieldsManager;
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

    public function saveToCache(SearchQuery $query, $results)
    {
        $serializedResults = [];
        if ($query->grouped) {
            foreach ($results as $resultsByRoomType) {
                $roomTypeResults = [];
                /** @var SearchResult $searchResult */
                foreach ($resultsByRoomType['results'] as $searchResult) {
                    $packagePrices = $searchResult->getPackagePrices($searchResult->getAdults(), $searchResult->getChildren());
                    $serializedPackagePrices = array_walk($packagePrices, function (PackagePrice $packagePrice) {
                        return [
                            'date' => $packagePrice->getDate()->format(self::DATE_FORMAT),
                            'price' => $packagePrice->getPrice(),
                            'tariff' => $packagePrice->getTariff()->getId(),
                            'promotion' => $packagePrice->getPromotion() ? $packagePrice->getPromotion()->getId() : null
                        ];
                    });

                    $roomTypeResults[] = [
                        'begin' => $searchResult->getBegin()->format(self::DATE_FORMAT),
                        'end' => $searchResult->getEnd()->format(self::DATE_FORMAT),
                        'adults' => $searchResult->getAdults(),
                        'children' => $searchResult->getChildren(),
                        'roomType' => $searchResult->getRoomType()->getId(),
                        'tariff' => $searchResult->getTariff()->getId(),
                        'price' => $searchResult->getPrice($searchResult->getAdults(), $searchResult->getChildren()),
                        'prices' => $searchResult->getPrices(),
                        'packagePrices' => $serializedPackagePrices,
                        'roomsCount' => $searchResult->getRoomsCount(),
                        'nights' => (int)$searchResult->getNights()
                    ];
                }

                $serializedResults[] = [
                    'roomTypeId' => $resultsByRoomType['roomType']->getId(),
                    'results' => $roomTypeResults
                ];
            }
        }
    }
}