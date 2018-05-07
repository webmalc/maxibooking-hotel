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

    /** @var RoomCacheSearchProvider */
    private $roomCacheLimitChecker;

    /** @var  */
    private $tariffLimitChecker;

    /** @var DocumentManager */
    private $dm;

    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    public function __construct(RoomCacheSearchProvider $roomCacheChecker, DocumentManager $dm, TariffLimitChecker $limitCheckertariffLimitChecker, RestrictionsCheckerService $restrictionsChecker)
    {
        $this->roomCacheLimitChecker = $roomCacheChecker;
        $this->dm = $dm;
        $this->tariffLimitChecker = $limitCheckertariffLimitChecker;
        $this->restrictionChecker = $restrictionsChecker;

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

        $currentRoomType = $this->dm->find(RoomType::class, $searchQuery->getRoomTypeId());
        // RoomCache limit check
        $this->fetchAndCheckRoomCaches($searchQuery, $currentRoomType, $currentTariff);

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
    private function checkRestrictions(SearchQuery $searchQuery)
    {
        $errors = $this->restrictionChecker->check($searchQuery);
        if (count($errors)) {
            throw new SearchException('Error in restriction');
        }
    }

    private function checkRoomTypePopulationLimit(RoomType $roomType, SearchQuery $searchQuery): void
    {
        $totalPlaces = $searchQuery->getTotalPlaces();
        $infants = $searchQuery->getInfants();
        //** TODO: Подумать как сюда закинуть настройки макс возможно бесплатных инфантов */
        $freeInfants = 100;
        if (($payInfants = $freeInfants - $infants) < 0) {
            $payInfants = abs($payInfants);
        } else {
            $payInfants = 0;
        }
        $roomTypeTotalPlaces = $roomType->getTotalPlaces() + $payInfants;
        if ($roomTypeTotalPlaces < $totalPlaces) {
            throw new SearchException('RoomType total place less than need in query');
        }
    }

    /**
     * @param SearchQuery $searchQuery
     * @param RoomType $currentRoomType
     * @param Tariff $currentTariff
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException
     */
    private function fetchAndCheckRoomCaches(SearchQuery $searchQuery, RoomType $currentRoomType, Tariff $currentTariff): array
    {
        return $this->roomCacheLimitChecker->fetchAndCheck($searchQuery->getBegin(), $searchQuery->getEnd(), $currentRoomType, $currentTariff);
    }

    private function checkTariffDates(Tariff $tariff): void
    {
        $this->tariffLimitChecker->check($tariff);
    }
}