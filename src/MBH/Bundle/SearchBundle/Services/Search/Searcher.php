<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Searcher
{

    /** @var DocumentManager */
    private $dm;

    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var  SearchLimitChecker*/
    private $searchLimitChecker;

    /** @var RoomCacheSearchProvider */
    private $roomCacheSearchProvider;

    /** @var SearchResultComposer */
    private $resultComposer;

    /** @var ValidatorInterface  */
    private $validator;

    public function __construct(
        DocumentManager $dm,
        RestrictionsCheckerService $restrictionsChecker,
        SearchLimitChecker $limitChecker,
        RoomCacheSearchProvider $roomCacheSearchProvider,
        SearchResultComposer $resultComposer,
        ValidatorInterface $validator
)
    {
        $this->dm = $dm;
        $this->restrictionChecker = $restrictionsChecker;
        $this->searchLimitChecker = $limitChecker;
        $this->roomCacheSearchProvider = $roomCacheSearchProvider;
        $this->resultComposer = $resultComposer;
        $this->validator = $validator;
    }


    /**
     * @param SearchQuery $searchQuery
     * @return SearchResult|null
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException
     * @throws SearchException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function search(SearchQuery $searchQuery): SearchResult
    {
        $errors = $this->validator->validate($searchQuery);
        if (\count($errors)) {
            throw new SearcherException('There is a problem in SearchQuery. '. $errors);
        }
        $currentTariff = $this->dm->find(Tariff::class, $searchQuery->getTariffId());
        $currentRoomType = $this->dm->find(RoomType::class, $searchQuery->getRoomTypeId());
        $this->preFilter($searchQuery);
        $this->checkRestrictions($searchQuery);
        $this->checkTariffDates($currentTariff);
        $this->checkTariffConditions($currentTariff, $searchQuery);
        $this->checkRoomTypePopulationLimit($currentRoomType, $searchQuery);
        $roomCaches = $this->checkRoomCacheLimitAndReturnActual($searchQuery, $currentRoomType, $currentTariff);

        return $this->searchResultCompose($searchQuery, $currentRoomType, $currentTariff, $roomCaches);
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
        $this->restrictionChecker->setConditions($searchQuery->getSearchCondition());
        $errors = $this->restrictionChecker->check($searchQuery);
        if (\count($errors)) {
            throw new SearchException('Error in restriction. '. implode(';', $errors));
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

    private function checkRoomTypePopulationLimit(RoomType $roomType, SearchQuery $searchQuery): void
    {
        $this->searchLimitChecker->checkRoomTypePopulationLimit($roomType, $searchQuery);
    }

    /**
     * @param SearchQuery $searchQuery
     * @param RoomType $currentRoomType
     * @param Tariff $currentTariff
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException
     */
    private function checkRoomCacheLimitAndReturnActual(SearchQuery $searchQuery, RoomType $currentRoomType, Tariff $currentTariff): array
    {
        return $this->roomCacheSearchProvider->fetchAndCheck($searchQuery->getBegin(), $searchQuery->getEnd(), $currentRoomType, $currentTariff);
    }

    private function searchResultCompose(SearchQuery $searchQuery, RoomType $roomType, Tariff $tariff, array $roomCaches): SearchResult
    {
        $searchResult = new SearchResult();

        return $this->resultComposer->composeResult($searchResult, $searchQuery, $roomType, $tariff, $roomCaches);
    }
}