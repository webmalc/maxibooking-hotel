<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Lib\Data\PriceCacheFetchQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException;
use MBH\Bundle\SearchBundle\Services\Data\PriceCacheFetcher;

class PriceCachesMerger
{
    /** @var TariffRepository */
    private $tariffRepository;

    /** @var PriceCacheFetcher */
    private $priceCacheFetcher;


    public function __construct(TariffRepository $tariffRepository, PriceCacheFetcher $priceCacheFetcher)
    {
        $this->tariffRepository = $tariffRepository;
        $this->priceCacheFetcher = $priceCacheFetcher;
    }


    public function getMergedPriceCaches(CalcQuery $calcQuery): array
    {
        $rawPriceTariffCaches = $this->getPriceTariffPriceCaches($calcQuery);
        $priceTariffCaches = $this->refactorCacheArray($rawPriceTariffCaches);
        if (!\count($priceTariffCaches)) {
            throw new PriceCachesMergerException('No one priceCache for tariff ' . $calcQuery->getTariff()->getName(). ' RoomType '. $calcQuery->getRoomType()->getFullTitle() );
        }
        if ($this->checkCachesCount($priceTariffCaches, $calcQuery->getDuration())) {
            return $priceTariffCaches;
        }

        $rawMergingPriceCaches = $this->getMergingTariffPriceCaches($calcQuery);
        $mergingPriceCaches = $this->refactorCacheArray($rawMergingPriceCaches);
        $mergedPriceCaches = $this->mergePriceCaches($priceTariffCaches, $mergingPriceCaches);
        if ($this->checkCachesCount($mergedPriceCaches, $calcQuery->getDuration())) {
            return $mergedPriceCaches;
        }

        $rawBaseTariffPriceCaches = $this->getBaseTariffPriceCaches($calcQuery);
        $baseTariffPriceCaches = $this->refactorCacheArray($rawBaseTariffPriceCaches);
        $lastMergedPriceCaches = $this->mergePriceCaches($mergedPriceCaches, $baseTariffPriceCaches);
        if ($this->checkCachesCount($lastMergedPriceCaches, $calcQuery->getDuration())) {
            return $lastMergedPriceCaches;
        }

        throw new PriceCachesMergerException('There is not enough price caches even after merging.'. $calcQuery->getTariff()->getName().' '.$calcQuery->getRoomType()->getFullTitle());
    }

    private function checkCachesCount(array $priceCaches, int $duration): bool
    {
        if (\count($priceCaches) > $duration) {
            throw new PriceCachesMergerException('PriceCaches merging problem. Num of RoomCaches more than duration!');
        }

        return \count($priceCaches) === $duration;
    }

    private function mergePriceCaches(array $mainCaches, array $auxiliaryCaches): array
    {
        $merged = $mainCaches + $auxiliaryCaches;
        uasort($merged, function ($cache1, $cache2) {
            return $cache1['data']['date'] <=> $cache2['data']['date'];
        });

        return $merged;
    }

    private function refactorCacheArray(array $rawCaches): array
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


    private function getPriceTariffPriceCaches(CalcQuery $calcQuery): array
    {
        $priceCaches = $this->getRawPriceCaches($calcQuery, $calcQuery->getPriceTariffId());

        return $this->preparePriceCacheReturn($priceCaches, $calcQuery->getTariff()->getId());
    }


    private function getMergingTariffPriceCaches(CalcQuery $calcQuery): array
    {
        if ($mergingTariffId = $calcQuery->getMergingTariffId()) {
            $priceCaches = $this->getRawPriceCaches($calcQuery, $mergingTariffId);

            return $this->preparePriceCacheReturn($priceCaches, $mergingTariffId);
        }

        return [];
    }

    private function getBaseTariffPriceCaches(CalcQuery $calcQuery): array
    {
        if (!$calcQuery->getTariff()->getIsDefault()) {
            $hotelId = $calcQuery->getTariff()->getHotel()->getId();
            $rawBaseTariffArray = $this->tariffRepository->fetchRawBaseTariffId($hotelId);
            $baseTariffId = (string)reset($rawBaseTariffArray)['_id'];
            if ($baseTariffId) {
                $priceCaches = $this->getRawPriceCaches($calcQuery, $baseTariffId);

                return $this->preparePriceCacheReturn($priceCaches, $baseTariffId);
            }
        }

        return [];
    }

    private function getRawPriceCaches(CalcQuery $calcQuery, string $searchingTariffId): array
    {
//        //** TODO тут проверить можно лимит на тариф, но! Взрослые дети ? Возможно стоит создавать несколько CalcQuery ? тогда не понять как делать проверку на restrictions */
        $fetchQuery = PriceCacheFetchQuery::createInstanceFromCalcQuery($calcQuery);
        $fetchQuery->setTariffId($searchingTariffId);
        return $this->priceCacheFetcher->fetchNecessaryDataSet($fetchQuery);
    }

    private function preparePriceCacheReturn(array $caches, string $tariffId): array
    {
        return [
            'searchTariffId' => $tariffId,
            'caches' => $caches
        ];
    }
}