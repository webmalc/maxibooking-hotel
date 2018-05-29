<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\HotelContentHolder;
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

    /** @var HotelContentHolder */
    private $hotelContentHolder;

    public function __construct(
        DocumentManager $dm,
        RestrictionsCheckerService $restrictionsChecker,
        SearchLimitChecker $limitChecker,
        RoomCacheSearchProvider $roomCacheSearchProvider,
        SearchResultComposer $resultComposer,
        ValidatorInterface $validator,
        HotelContentHolder $contentHolder
)
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searchLimitChecker = $limitChecker;
        $this->roomCacheSearchProvider = $roomCacheSearchProvider;
        $this->resultComposer = $resultComposer;
        $this->validator = $validator;
        $this->hotelContentHolder = $contentHolder;
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
        $currentTariff = $this->getCurrentTariff($searchQuery->getTariffId());
        $currentRoomType = $this->getCurrentRoomType($searchQuery->getRoomTypeId());
        $this->preFilter($searchQuery);
        $this->checkRestrictions($searchQuery);
        $this->checkTariffDates($currentTariff);
        $this->checkTariffConditions($currentTariff, $searchQuery);
        $this->checkRoomTypePopulationLimit($currentRoomType, $searchQuery);
        $roomCaches = $this->checkRoomCacheLimitAndReturnActual($searchQuery, $currentRoomType, $currentTariff);
        $result = $this->composeResult($searchQuery, $currentRoomType, $currentTariff, $roomCaches);
        $this->checkWindows($result);

        return $result;
    }

    private function getCurrentTariff(string $tariffId): Tariff
    {
        $tariff = $this->hotelContentHolder->getFetchedTariff($tariffId);
        if (!$tariff) {
            $tariff = $this->dm->find(Tariff::class, $tariffId);
        }

        return $tariff;
    }

    private function getCurrentRoomType(string $roomTypeId): RoomType
    {
        $roomType = $this->hotelContentHolder->getFetchedRoomType($roomTypeId);
        if (!$roomType) {
            $roomType = $this->dm->find(RoomType::class, $roomTypeId);
        }

        return $roomType;
    }

    /**
     *
     * @param SearchQuery $searchQuery
     * @throws SearchException
     */
    private function preFilter(SearchQuery $searchQuery): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $mustFields = [
            'begin',
            'end',
            'tariffId',
            'roomTypeId'
        ];
        foreach ($mustFields as $mustField) {
            if (!$accessor->getValue($searchQuery, $mustField)) {
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
        $this->restrictionChecker->setConditions($searchQuery->getSearchConditions());
        $checked = $this->restrictionChecker->check($searchQuery);
        if (!$checked) {
            throw new SearchException('Error when check restriction.');
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

    private function checkWindows(SearchResult $searchResult): void
    {
        $this->searchLimitChecker->checkWindows($searchResult);
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

    private function composeResult(SearchQuery $searchQuery, RoomType $roomType, Tariff $tariff, array $roomCaches): SearchResult
    {
        $searchResult = new SearchResult();

        return $this->resultComposer->composeResult($searchResult, $searchQuery, $roomType, $tariff, $roomCaches);
    }
}