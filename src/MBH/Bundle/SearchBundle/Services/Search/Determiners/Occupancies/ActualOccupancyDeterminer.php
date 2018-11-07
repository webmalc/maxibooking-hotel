<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;

use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\DeterminerInterface;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActualOccupancyDeterminer
{

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var DeterminerInterface */
    private $commonDeterminer;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;

    /**
     * ActualAgesDeterminer constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param DeterminerInterface $commonDeterminer
     * @param SharedDataFetcher $dataFetcher
     */
    public function __construct(EventDispatcherInterface $dispatcher, DeterminerInterface $commonDeterminer, SharedDataFetcher $dataFetcher)
    {
        $this->dispatcher = $dispatcher;
        $this->commonDeterminer = $commonDeterminer;
        $this->sharedDataFetcher = $dataFetcher;
    }


    public function determine(SearchQuery $searchQuery, string $eventName = null): OccupancyInterface
    {
        $searchOccupancy = Occupancy::createInstanceBySearchQuery($searchQuery);
        $tariff = $this->sharedDataFetcher->getFetchedTariff($searchQuery->getTariffId());
        $roomType = $this->sharedDataFetcher->getFetchedRoomType($searchQuery->getRoomTypeId());

        if ($eventName) {
            $event = new OccupancyDeterminerEvent();

            $event->setTariff($tariff)
                ->setRoomType($roomType)
                ->setOccupancies($searchOccupancy)
            ;

            $this->dispatcher->dispatch($eventName, $event);
            $occupancies = $event->getResolvedOccupancies();
            if (null !== $occupancies) {
                return $occupancies;
            }
        }

        return $this->commonDeterminer->determine($searchOccupancy, $tariff,  $roomType);
    }

}