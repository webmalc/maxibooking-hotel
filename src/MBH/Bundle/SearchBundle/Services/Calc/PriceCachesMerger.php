<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\ActualChildOptionDeterminer;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataQueries\DataQuery;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\PriceCacheRawFetcher;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcherInterface;

class PriceCachesMerger
{
    /** @var TariffRepository */
    private $tariffRepository;

    /** @var DataManager */
    private $dataManager;

    /** @var SharedDataFetcherInterface */
    private $sharedDataFetcher;

    public function __construct(TariffRepository $tariffRepository, DataManager $dataManager, SharedDataFetcherInterface $sharedDataFetcher)
    {
        $this->tariffRepository = $tariffRepository;
        $this->dataManager = $dataManager;
        $this->sharedDataFetcher = $sharedDataFetcher;
    }


    public function getMergedPriceCaches(CalcQueryInterface $calcQuery): array
    {
        $rawPriceTariffCaches = $this->getPriceTariffPriceCaches($calcQuery);
        $priceTariffCaches = $this->groupPriceCachesByDate($rawPriceTariffCaches);
        if (!\count($priceTariffCaches)) {
            throw new PriceCachesMergerException('No one priceCache for tariff ' . $calcQuery->getTariffId(). ' RoomType '. $calcQuery->getRoomTypeId() );
        }
        if ($this->checkCachesCount($priceTariffCaches, $calcQuery)) {
            return $priceTariffCaches;
        }

        $rawMergingPriceCaches = $this->getMergingTariffPriceCaches($calcQuery);
        $mergingPriceCaches = $this->groupPriceCachesByDate($rawMergingPriceCaches);
        $mergedPriceCaches = $this->mergePriceCaches($priceTariffCaches, $mergingPriceCaches);
        if ($this->checkCachesCount($mergedPriceCaches, $calcQuery)) {
            return $mergedPriceCaches;
        }

        $rawBaseTariffPriceCaches = $this->getBaseTariffPriceCaches($calcQuery);
        $baseTariffPriceCaches = $this->groupPriceCachesByDate($rawBaseTariffPriceCaches);
        $lastMergedPriceCaches = $this->mergePriceCaches($mergedPriceCaches, $baseTariffPriceCaches);
        if ($this->checkCachesCount($lastMergedPriceCaches, $calcQuery)) {
            return $lastMergedPriceCaches;
        }

        throw new PriceCachesMergerException('There is not enough price caches even after merging.'. $calcQuery->getTariffId().' '.$calcQuery->getRoomTypeId());
    }

    private function checkCachesCount(array $priceCaches, CalcQueryInterface $calcQuery): bool
    {
        $begin = $calcQuery->getBegin();
        $end = $calcQuery->getEnd();
        $duration = (int)$end->diff($begin)->format('%a');

        if (\count($priceCaches) > $duration) {
            throw new PriceCachesMergerException('PriceCaches merging problem. Num of RoomCaches more than duration!');
        }

        return \count($priceCaches) === $duration;
    }

    private function mergePriceCaches(array $mainCaches, array $auxiliaryCaches): array
    {
        $merged = $mainCaches + $auxiliaryCaches;
        uasort($merged, static function ($cache1, $cache2) {
            return $cache1['data']['date'] <=> $cache2['data']['date'];
        });

        return $merged;
    }

    private function groupPriceCachesByDate(array $rawCaches): array
    {
        $result = [];
        $caches = $rawCaches['caches'] ?? [];
        foreach ($caches as $cache) {
            $key = Helper::convertMongoDateToDate($cache['date'])->format('d_m_Y');
            $result[$key] = [
                'searchTariffId' => $rawCaches['searchTariffId'],
                'data' => $cache
            ];
        }

        return $result;
    }


    private function getPriceTariffPriceCaches(CalcQueryInterface $calcQuery): array
    {
        $currentTariff = $calcQuery->getTariffId();
        $priceCaches = $this->getRawPriceCaches($calcQuery, $currentTariff);

        return $this->preparePriceCacheReturn($priceCaches, $currentTariff);
    }


    private function getMergingTariffPriceCaches(CalcQueryInterface $calcQuery): array
    {
        $tariff = $this->sharedDataFetcher->getFetchedTariff($calcQuery->getTariffId());
        if ($mergedTariff = $tariff->getMergingTariff()) {
            $mergingTariffId = $mergedTariff->getId();
            $priceCaches = $this->getRawPriceCaches($calcQuery, $mergingTariffId);

            return $this->preparePriceCacheReturn($priceCaches, $mergingTariffId);
        }


        return [];
    }

    private function getBaseTariffPriceCaches(CalcQueryInterface $calcQuery): array
    {
        $tariff = $this->sharedDataFetcher->getFetchedTariff($calcQuery->getTariffId());
        if (!$tariff->getIsDefault()) {
            $baseTariff = $this->tariffRepository->fetchRawBaseTariffId($tariff->getHotel()->getId());
            $baseTariffId = reset($baseTariff)['_id'];
            $priceCaches = $this->getRawPriceCaches($calcQuery, $baseTariffId);

            return $this->preparePriceCacheReturn($priceCaches, $baseTariffId);
        }

        return [];
    }

    private function getRawPriceCaches(CalcQueryInterface $calcQuery, string $searchingTariffId): array
    {
        $dataQuery = clone $calcQuery;
        $dataQuery->setTariffId($searchingTariffId);

        return $this->dataManager->fetchData($dataQuery, PriceCacheRawFetcher::NAME);
    }

    private function preparePriceCacheReturn(array $caches, string $tariffId): array
    {
        @trigger_error('There is server determine price tariff exists, so refactor this crap.');
        return [
            'searchTariffId' => $tariffId,
            'caches' => $caches
        ];
    }
}