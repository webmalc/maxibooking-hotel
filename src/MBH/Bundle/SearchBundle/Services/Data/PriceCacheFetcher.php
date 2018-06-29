<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\PriceBundle\Document\PriceCacheRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use Symfony\Component\Cache\Simple\AbstractCache;

class PriceCacheFetcher extends AbstractDataFetcher
{

    /** @var PriceCacheRepository */
    private $priceCacheRepository;

    /** @var bool */
    private $isUseCategory;

    /**
     * PriceCacheFetcher constructor.
     * @param DataHolderInterface $holder
     * @param SharedDataFetcherInterface $sharedDataFetcher
     * @param PriceCacheRepository $repository
     * @param ClientConfigRepository $configRepository
     */
    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, PriceCacheRepository $repository, ClientConfigRepository $configRepository, AbstractCache $cache)
    {
        $this->isUseCategory = $configRepository->fetchConfig()->getUseRoomTypeCategory();
        $this->priceCacheRepository = $repository;
        parent::__construct($holder, $sharedDataFetcher, $cache);
    }


    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->priceCacheRepository->fetchRawPeriod($fetchQuery->getMaxBegin(), $fetchQuery->getMaxEnd(), [], [], $this->isUseCategory);
    }

}