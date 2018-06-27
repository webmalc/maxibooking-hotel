<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\PriceBundle\Document\RestrictionRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Lib\Data\RestrictionsFetchQuery;

/**
 * Class RestrictionsFetcher
 * @package MBH\Bundle\SearchBundle\Services\Data
 */
class RestrictionsFetcher extends AbstractDataFetcher
{

    /** @var RestrictionRepository */
    protected $restrictionRepository;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, RestrictionRepository $restrictionRepository)
    {
        $this->restrictionRepository = $restrictionRepository;
        parent::__construct($holder, $sharedDataFetcher);
    }


    /**
     * @param DataFetchQueryInterface|RestrictionsFetchQuery $fetchQuery
     * @return array
     */
    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->restrictionRepository->getAllSearchPeriod($fetchQuery->getConditions());
    }

}