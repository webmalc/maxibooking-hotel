<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchLimitCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearchLimitChecker
{

    /** @var RoomCacheSearchProvider  */
    private $roomCacheLimitChecker;

    public function __construct(RoomCacheSearchProvider $roomCacheChecker)
    {
        $this->roomCacheLimitChecker = $roomCacheChecker;
    }

    public function checkDateLimit(Tariff $tariff): void
    {
        $tariffBegin = $tariff->getBegin();
        $tariffEnd = $tariff->getEnd();
        $now = new \DateTime("now midnight");
        $isTariffNotYetStarted = $tariffBegin > $now;
        $isTariffAlreadyEnded = $tariffEnd < $now;

        if ($isTariffNotYetStarted || $isTariffAlreadyEnded) {
            throw new SearchLimitCheckerException('Tariff time limit violated');
        }
    }

    public function checkTariffConditions(Tariff $tariff, SearchQuery $searchQuery)
    {
        //** TODO: Уточнить у сергея, тут должны быть приведенные значения взрослых-детей или из запроса ибо в поиске из запрсоа. */
        $duration = $searchQuery->getEnd()->diff($searchQuery->getBegin())->format('%a');
        $checkResult = PromotionConditionFactory::checkConditions(
            $tariff,
            $duration,
            $searchQuery->getActualAdults(),
            $searchQuery->getActualChildren()
        );

        if (!$checkResult) {
            throw new SearchLimitCheckerException('Tariff conditions are violated');
        }
    }

    public function checkRoomCacheLimit(SearchQuery $searchQuery, RoomType $currentRoomType, Tariff $currentTariff): void
    {
        $this->roomCacheLimitChecker->fetchAndCheck($searchQuery->getBegin(), $searchQuery->getEnd(), $currentRoomType, $currentTariff);
    }

    public function checkRoomTypePopulationLimit(RoomType $roomType, SearchQuery $searchQuery): void
    {
        $searchTotalPlaces = $searchQuery->getSearchTotalPlaces();
        $roomTypeTotalPlaces = $roomType->getTotalPlaces();

        $searchInfants = $searchQuery->getInfants();
        $roomTypeMaxInfants = $roomType->getMaxInfants();

        if ($searchTotalPlaces > $roomTypeTotalPlaces || $searchInfants > $roomTypeMaxInfants) {
            throw new SearchLimitCheckerException('RoomType total place less than need in query');
        }
    }
}