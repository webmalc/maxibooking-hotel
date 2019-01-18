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

    /** @var OccupancyDeterminerFactory */
    private $occupancyDeterminerFactory;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;

    /**
     * ActualAgesDeterminer constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param OccupancyDeterminerFactory $commonDeterminer
     * @param SharedDataFetcher $dataFetcher
     */
    public function __construct(EventDispatcherInterface $dispatcher, OccupancyDeterminerFactory $commonDeterminer, SharedDataFetcher $dataFetcher)
    {
        $this->dispatcher = $dispatcher;
        $this->occupancyDeterminerFactory = $commonDeterminer;
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
        //** TODO: Need tests */
        if ($searchQuery->isWarmUp()) {
            $determiner = $this->occupancyDeterminerFactory->create(OccupancyDeterminerFactory::WARM_UP_DETERMINER);
        } else {
            $determiner = $this->occupancyDeterminerFactory->create(OccupancyDeterminerFactory::COMMON_DETERMINER);
        }

        return $determiner->determine($searchOccupancy, $tariff,  $roomType);
    }

}