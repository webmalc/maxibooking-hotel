<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
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

    /** @var SearchResultComposer */
    private $resultComposer;

    /** @var ValidatorInterface  */
    private $validator;

    /** @var DataHolder */
    private $dataHolder;

    public function __construct(
        DocumentManager $dm,
        RestrictionsCheckerService $restrictionsChecker,
        SearchLimitChecker $limitChecker,
        SearchResultComposer $resultComposer,
        ValidatorInterface $validator,
        DataHolder $dataHolder
)
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searchLimitChecker = $limitChecker;
        $this->resultComposer = $resultComposer;
        $this->validator = $validator;
        $this->dataHolder = $dataHolder;
    }


    /**
     * @param SearchQuery $searchQuery
     * @return SearchResult
     * @throws SearchException
     * @throws SearcherException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerServiceException
     */
    public function search(SearchQuery $searchQuery): SearchResult
    {

        $errors = $this->validator->validate($searchQuery);
        if (\count($errors)) {
            throw new SearcherException('There is a problem in SearchQuery. '. (string)$errors);
        }
        $currentTariff = $this->getCurrentTariff($searchQuery->getTariffId());
        $currentRoomType = $this->getCurrentRoomType($searchQuery->getRoomTypeId());
        $this->preFilter($searchQuery);
        $this->checkRestrictions($searchQuery);
        $this->checkTariffDates($currentTariff);
        $this->checkTariffConditions($currentTariff, $searchQuery);
        $this->checkRoomTypePopulationLimit($currentRoomType, $searchQuery);
        $roomCaches = $this->getRoomCaches($searchQuery);
        $this->checkRoomCacheLimit($roomCaches, $searchQuery);
        $result = $this->composeResult($searchQuery, $currentRoomType, $currentTariff, $roomCaches);
        $this->checkWindows($result);

        return $result;
    }

    private function getCurrentTariff(string $tariffId): Tariff
    {
        $tariff = $this->dataHolder->getFetchedTariff($tariffId);
        if (!$tariff) {
            $tariff = $this->dm->find(Tariff::class, $tariffId);
        }

        return $tariff;
    }

    private function getCurrentRoomType(string $roomTypeId): RoomType
    {
        $roomType = $this->dataHolder->getFetchedRoomType($roomTypeId);
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
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerServiceException
     */
    private function checkRestrictions(SearchQuery $searchQuery): void
    {
        $checked = $this->restrictionChecker->check($searchQuery);
        if (!$checked) {
            throw new SearchException('Violation in restriction.');
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


    private function checkRoomCacheLimit(array $rawRoomCaches, SearchQuery $searchQuery): void
    {
        $tariff = $this->dataHolder->getFetchedTariff($searchQuery->getTariffId());
        if (!$tariff) {
            throw new SearcherException('Can not get hydrated tariff in checkRoomCacheLimit method');
        }
        $this->searchLimitChecker->checkRoomCacheLimit($rawRoomCaches, $tariff, $searchQuery->getDuration());
    }

    private function composeResult(SearchQuery $searchQuery, RoomType $roomType, Tariff $tariff, array $roomCaches): SearchResult
    {
        $searchResult = new SearchResult();

        return $this->resultComposer->composeResult($searchResult, $searchQuery, $roomType, $tariff, $roomCaches);
    }

    private function getRoomCaches(SearchQuery $searchQuery): array
    {
        return $this->dataHolder->getNecessaryRoomCaches($searchQuery);
    }
}