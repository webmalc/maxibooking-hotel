<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use Symfony\Component\Cache\Simple\AbstractCache;

class PackageAccommodationFetcher extends AbstractDataFetcher
{

    /**
     * @var PackageRepository
     */
    private $repository;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, PackageRepository $repository, AbstractCache $cache)
    {

        parent::__construct($holder, $sharedDataFetcher, $cache);
        $this->repository = $repository;
    }


    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->repository->getRawAccommodationByPeriod($fetchQuery->getMaxBegin(), $fetchQuery->getMaxEnd());
    }

}