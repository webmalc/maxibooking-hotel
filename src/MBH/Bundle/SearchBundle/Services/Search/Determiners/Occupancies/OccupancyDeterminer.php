<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;

use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyDeterminerInterface;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OccupancyDeterminer
{

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var OccupancyDeterminerInterface */
    private $commonDeterminer;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;

    /**
     * ActualAgesDeterminer constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param OccupancyDeterminerInterface $commonDeterminer
     * @param SharedDataFetcher $dataFetcher
     */
    public function __construct(EventDispatcherInterface $dispatcher, OccupancyDeterminerInterface $commonDeterminer, SharedDataFetcher $dataFetcher)
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