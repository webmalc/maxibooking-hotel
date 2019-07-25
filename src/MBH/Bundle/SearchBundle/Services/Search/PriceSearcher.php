<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Lib\SearchCalculateEvent;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalcHelperException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQueryInterface;
use MBH\Bundle\SearchBundle\Services\Calc\Calculation;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcherInterface;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminer;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminerEvent;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class PriceSearcher
 * @package MBH\Bundle\SearchBundle\Services\Search
 */
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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /**
     * @var RoomTypeManager
     */
    private $roomTypeManager;
    /**
     * @var SharedDataFetcherInterface
     */
    private $dataFetcher;


    /**
     * PriceSearcher constructor.
     * @param Calculation $calculation
     * @param OccupancyDeterminer $occupancyDeterminer
     * @param EventDispatcherInterface $eventDispatcher
     * @param RoomTypeManager $roomTypeManager
     * @param SharedDataFetcherInterface $dataFetcher
     */
    public function __construct(Calculation $calculation, OccupancyDeterminer $occupancyDeterminer, EventDispatcherInterface $eventDispatcher, RoomTypeManager $roomTypeManager, SharedDataFetcherInterface $dataFetcher)
    {
        $this->calculation = $calculation;
        $this->occupancyDeterminer = $occupancyDeterminer;
        $this->eventDispatcher = $eventDispatcher;
        $this->roomTypeManager = $roomTypeManager;
        $this->dataFetcher = $dataFetcher;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return array
     * @throws CalculationException
     * @throws CalcHelperException
     * @throws PriceCachesMergerException
     * @throws SharedFetcherException
     */
    public function searchPrice(SearchQuery $searchQuery): array
    {
        $occupancy = $this->occupancyDeterminer->determine($searchQuery, OccupancyDeterminerEvent::OCCUPANCY_DETERMINER_EVENT_CALCULATION);

        $prices = $this->getEventPrices($searchQuery, $occupancy);

        if (null !== $prices) {
            if (false === $prices) {
                throw new CalculationException('No price for subscriber');
            }

            return $prices;
        }

        return $this->getPricesForOccupancy($searchQuery, $occupancy);
    }

    /**
     * @param CalcQueryInterface $calcQuery
     * @param OccupancyInterface $occupancy
     * @return array
     * @throws CalculationException
     * @throws CalcHelperException
     * @throws PriceCachesMergerException
     * @throws SharedFetcherException
     */
    private function getPricesForOccupancy(CalcQueryInterface $calcQuery, OccupancyInterface $occupancy): array
    {
        $adults = $occupancy->getAdults();
        $children = $occupancy->getChildren();

        return $this->calculation->calcPrices($calcQuery, $adults, $children);
    }

    /**
     * @param SearchQuery $searchQuery
     * @param OccupancyInterface $occupancy
     * @return mixed
     * @throws SharedFetcherException
     */
    private function getEventPrices(SearchQuery $searchQuery, OccupancyInterface $occupancy)
    {
        /** TODO: Тут надо бы рефакторить и убрать этот бардак в пользу roomTypeId, однако потом. */
        /** TODO: Так же решить вопрос с occupancy , по идее в event data надо вообще передавать searchQuery или что то схожее, но не этот мрак */

        $roomType = $this->dataFetcher->getFetchedRoomType($searchQuery->getRoomTypeId());
        $tariff = $this->dataFetcher->getFetchedTariff($searchQuery->getTariffId());

        $event = new SearchCalculateEvent();
        $eventData = [
            'roomType' => $roomType,
            'tariff' => $tariff,
            'begin' => $searchQuery->getBegin(),
            'end' => (clone $searchQuery->getEnd())->modify('-1 day'),
            'adults' => $occupancy->getAdults(),
            'children' => $occupancy->getChildren(),
            'promotion' => null,
            'special' => null,
            'isUseCategory' => $this->roomTypeManager->useCategories,
            'childrenAges' => $searchQuery->getChildrenAges()
        ];
        $event->setEventData($eventData);
        $this->eventDispatcher->dispatch(SearchCalculateEvent::SEARCH_CALCULATION_NAME, $event);

        return $event->getPrices();

    }

}