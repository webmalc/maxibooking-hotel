<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\PackageBundle\Document\PackageAccommodationRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;

class PackageAccommodationFetcher extends AbstractDataFetcher
{

    /**
     * @var PackageAccommodationRepository
     */
    private $repository;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, PackageAccommodationRepository $repository)
    {

        parent::__construct($holder, $sharedDataFetcher);
        $this->repository = $repository;
    }


    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->repository->getRawAccommodationByPeriod($fetchQuery->getMaxBegin(), $fetchQuery->getMaxEnd());
    }

}