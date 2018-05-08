<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchResult;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Searcher
{

    /** @var DocumentManager */
    private $dm;

    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var  SearchLimitChecker*/
    private $searchLimitChecker;

    public function __construct(DocumentManager $dm,  RestrictionsCheckerService $restrictionsChecker, SearchLimitChecker $limitChecker)
    {
        $this->dm = $dm;
        $this->restrictionChecker = $restrictionsChecker;
        $this->searchLimitChecker = $limitChecker;

    }


    /**
     * @param SearchQuery $searchQuery
     * @return SearchResult|null
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException
     * @throws SearchException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function search(SearchQuery $searchQuery): ?SearchResult
    {
        $searchResult = new SearchResult();

        $this->preFilter($searchQuery);

        $this->checkRestrictions($searchQuery);

        $currentTariff = $this->dm->find(Tariff::class, $searchQuery->getTariffId());
        $this->checkTariffDates($currentTariff);
        $this->checkTariffConditions($currentTariff, $searchQuery);


        $currentRoomType = $this->dm->find(RoomType::class, $searchQuery->getRoomTypeId());
        $this->checkRoomCacheLimit($searchQuery, $currentRoomType, $currentTariff);
        $this->checkRoomTypePopulationLimit($currentRoomType, $searchQuery);

        return $searchResult;
    }

    /**
     *
     * @param SearchQuery $searchQuery
     * @throws SearchException
     */
    private function preFilter(SearchQuery $searchQuery): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $needFields = [
            'begin',
            'end',
            'tariffId',
            'roomTypeId',
            'adults'
        ];
        foreach ($needFields as $needField) {
            if (!$accessor->getValue($searchQuery, $needField)) {
                throw new SearchException('Terminate Search cause error in search query');
            }
        }
    }

    /**
     * @param SearchQuery $searchQuery
     * @throws SearchException
     */
    private function checkRestrictions(SearchQuery $searchQuery): void
    {
        $errors = $this->restrictionChecker->check($searchQuery);
        if (\count($errors)) {
            throw new SearchException('Error in restriction');
        }
    }

    private function checkTariffDates(Tariff $tariff): void
    {
        $this->searchLimitChecker->checkDateLimit($tariff);
    }

    private function checkTariffConditions(Tariff $tariff, SearchQuery $searchQuery): void
    {
        $this->searchLimitChecker->checkTariffConditions($tariff, $searchQuery);
    }

    /**
     * @param SearchQuery $searchQuery
     * @param RoomType $currentRoomType
     * @param Tariff $currentTariff
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException
     */
    private function checkRoomCacheLimit(SearchQuery $searchQuery, RoomType $currentRoomType, Tariff $currentTariff): void
    {
        $this->searchLimitChecker->checkRoomCacheLimit($searchQuery, $currentRoomType, $currentTariff);
    }

    private function checkRoomTypePopulationLimit(RoomType $roomType, SearchQuery $searchQuery): void
    {
        $this->searchLimitChecker->checkRoomTypePopulationLimit($roomType, $searchQuery);
    }
}