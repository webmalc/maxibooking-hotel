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
        'range'
    ];

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
    )
    {
        $this->dm = $dm;
        $this->fieldsManager = $fieldsManager;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->search = $search;
        $this->roomTypeManager = $roomTypeManager;
    }

    /**
     * @param SearchQuery $query
     * @return SearchResultCacheItem
     */
    public function searchByQuery(SearchQuery $query)
    {
        $searchCriteriaArray = $this->fieldsManager
            ->fillByDocumentFieldsWithFieldNameKeys($query, self::SEARCH_CRITERIA_FIELDS);

        $searchCriteriaArray = array_merge(
            ['tariff.id' => $query->tariff, 'roomTypeId' => $query->roomTypes[0]],
            $searchCriteriaArray);

        return $this->dm
            ->getRepository('MBHPackageBundle:SearchResultCacheItem')
            ->findOneBy($searchCriteriaArray);
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
        $searchResultsList = $byRoomTypes ? $this->getSearchResultsFromGroupedArray($results) : $results;

        foreach ($queryTariffs as $tariff) {
            foreach ($queryRoomTypesIds as $roomTypeId) {
                $searchResultsByTariffAndRoomType = array_filter(
                    $searchResultsList,
                    function (SearchResult $result) use ($tariff, $roomTypeId) {
                        return $result->getTariff()->getId() === $tariff->getId()
                            && $result->getRoomType()->getId() === $roomTypeId;
                    }
                );

                $serializedResult = empty($searchResultsByTariffAndRoomType)
                    ? null
                    : json_encode($this->serializer->normalize(current($searchResultsByTariffAndRoomType)));

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

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $tariffsIds
     * @param array $roomTypesIds
     * @return
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function clearCache(\DateTime $begin, \DateTime $end, $tariffsIds = [], $roomTypesIds = [])
    {
        /** @var DocumentRepository $cacheRepo */
        $cacheRepo = $this->dm->getRepository('MBHPackageBundle:SearchResultCacheItem');
        $result = $cacheRepo
            ->createQueryBuilder()
            ->remove()
            ->field('begin')->lte($end)
            ->field('end')->gt($begin)
            ->field('tariff.id')->in($tariffsIds)
            ->field('roomTypeId')->in($roomTypesIds)
            ->getQuery()
            ->execute();

        return $result['n'];
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param int $maxLength
     * @param int $minLength
     * @param array $roomTypes
     * @param array $tariffs
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function warmUpCache(\DateTime $begin, \DateTime $end, int $maxLength, int $minLength, array $roomTypes, array $tariffs)
    {
        $periodsGenerator = $this->helper->getDatePeriodsGenerator($begin, $end, $maxLength, $minLength);
        foreach ($periodsGenerator as $datePeriod) {
            foreach ($roomTypes as $roomType) {
                foreach ($tariffs as $tariff) {
                    //TODO: Тут будет запуск поисков с указанными датами, тафрифами и типами комнат
                }
            }
        }
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
     * @return array
     */
    private function getSearchResultsFromGroupedArray($results): array
    {
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