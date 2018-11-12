<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Lib\Data\RestrictionsFetchQuery;
use Symfony\Component\Cache\Simple\AbstractCache;

/**
 * Class RestrictionsFetcher
 * @package MBH\Bundle\SearchBundle\Services\Data
 */
class RestrictionsFetcher extends AbstractDataFetcher
{

    /** @var RestrictionRepository */
    protected $restrictionRepository;
    /**
     * @var RoomTypeManager
     */
    private $manager;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, RestrictionRepository $restrictionRepository, AbstractCache $cache, RoomTypeManager $manager)
    {
        $this->restrictionRepository = $restrictionRepository;
        $this->manager = $manager;
        parent::__construct($holder, $sharedDataFetcher, $cache);
    }


    /**
     * @param DataFetchQueryInterface|RestrictionsFetchQuery $fetchQuery
     * @return array
     */
    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->restrictionRepository->getAllSearchPeriod($fetchQuery->getConditions(), $this->manager->useCategories);
    }

}