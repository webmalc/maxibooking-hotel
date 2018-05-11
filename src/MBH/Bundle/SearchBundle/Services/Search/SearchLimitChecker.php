<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchLimitCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearchLimitChecker
{

    /**
     * @param Tariff $tariff
     * @throws SearchLimitCheckerException
     */
    public function checkDateLimit(Tariff $tariff): void
    {
        $tariffBegin = $tariff->getBegin();
        $tariffEnd = $tariff->getEnd();
        $now = new \DateTime("now midnight");
        $isTariffNotYetStarted = $isTariffAlreadyEnded = false;
        if (null !== $tariffBegin) {
            $isTariffNotYetStarted = $tariffBegin > $now;
        }

        if (null !== $tariffEnd) {
            $isTariffAlreadyEnded = $tariffEnd < $now;
        }

        if ($isTariffNotYetStarted || $isTariffAlreadyEnded) {
            throw new SearchLimitCheckerException('Tariff time limit violated');
        }
    }

    /**
     * @param Tariff $tariff
     * @param SearchQuery $searchQuery
     * @throws SearchLimitCheckerException
     */
    public function checkTariffConditions(Tariff $tariff, SearchQuery $searchQuery): void
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

    /**
     * @param RoomType $roomType
     * @param SearchQuery $searchQuery
     * @throws SearchLimitCheckerException
     */
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