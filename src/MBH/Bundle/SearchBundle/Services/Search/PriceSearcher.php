<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQueryInterface;
use MBH\Bundle\SearchBundle\Services\Calc\Calculation;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminer;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminerEvent;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;

class PriceSearcher
{
    /**
     * @var Calculation
     */
    private $calculation;
    /**
     * @var OccupancyDeterminer
     */
    private $occupancyDeterminer;


    /**
     * PriceSearcher constructor.
     * @param Calculation $calculation
     * @param OccupancyDeterminer $occupancyDeterminer
     */
    public function __construct(Calculation $calculation, OccupancyDeterminer $occupancyDeterminer)
    {
        $this->calculation = $calculation;
        $this->occupancyDeterminer = $occupancyDeterminer;
    }

    public function searchPrice(SearchQuery $searchQuery): array
    {
        $occupancy = $this->occupancyDeterminer->determine($searchQuery, OccupancyDeterminerEvent::OCCUPANCY_DETERMINER_EVENT_CALCULATION);

        return $this->getPricesForOccupancy($searchQuery, $occupancy);
    }

    public function getPricesForOccupancy(CalcQueryInterface $calcQuery, OccupancyInterface $occupancy): array
    {
        $adults = $occupancy->getAdults();
        $children = $occupancy->getChildren();

        return $this->calculation->calcPrices($calcQuery, $adults, $children);
    }
}