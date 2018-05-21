<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\PriceBundle\Document\PriceCacheRepository;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException;

class PriceCachesMerger
{

    /** @var PriceCacheRepository */
    private $priceCacheRepository;

    /** @var TariffRepository */
    private $tariffRepository;


    public function __construct(PriceCacheRepository $repository, TariffRepository $tariffRepository)
    {

        $this->priceCacheRepository = $repository;
        $this->tariffRepository = $tariffRepository;
    }


    public function getMergedPriceCaches(CalcQuery $calcQuery): array
    {
        $priceTariffCaches = $this->getPriceTariffPriceCaches($calcQuery);
        if (!\count($priceTariffCaches)) {
            throw new PriceCachesMergerException('No one priceCache for tariff ' . $calcQuery->getTariff()->getName());
        }
        if ($this->checkCachesCount($priceTariffCaches, $calcQuery->getDuration())) {
            return $priceTariffCaches;
        }

        $mergingPriceCaches = $this->getMergingTariffPriceCaches($calcQuery);
        $mergedPriceCaches = $this->mergePriceCaches($priceTariffCaches, $mergingPriceCaches);
        if ($this->checkCachesCount($mergedPriceCaches, $calcQuery->getDuration())) {
            return $mergedPriceCaches;
        }

        $baseTariffPriceCaches = $this->getBaseTariffPriceCaches($calcQuery);
        $lastMergedPriceCaches = $this->mergePriceCaches($mergedPriceCaches, $baseTariffPriceCaches);
        if ($this->checkCachesCount($lastMergedPriceCaches, $calcQuery->getDuration())) {
            return $lastMergedPriceCaches;
        }

        throw new PriceCachesMergerException('There is not enough price caches even after merging.');
    }

    private function checkCachesCount(array $roomCaches, int $duration): bool
    {
        return \count($roomCaches) === $duration;
    }

    private function mergePriceCaches(array $mainCaches, array $auxiliaryCaches): array
    {
        $main = $this->keyWithDate($mainCaches);
        $auxiliary = $this->keyWithDate($auxiliaryCaches);
        $merged = $main + $auxiliary;
        uasort($merged, function ($cache1, $cache2) {
            return $cache1['date'] <=> $cache2['date'];
        });

        return $merged;
    }

    private function keyWithDate(array $caches): array
    {
        $result = [];
        foreach ($caches as $cache) {
            $key = $cache['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d_m_Y');
            $result[$key] = $cache;
        }

        return $result;
    }


    private function getBaseTariffPriceCaches(CalcQuery $calcQuery): array
    {
        if (!$calcQuery->getTariff()->getIsDefault()) {
            $hotelId = $calcQuery->getTariff()->getHotel()->getId();
            $rawBaseTariffArray = $this->tariffRepository->fetchRawBaseTariffId($hotelId);
            $baseTariffId = (string)reset($rawBaseTariffArray)['_id'];
            if ($baseTariffId) {
                return $this->getRawPriceCaches($calcQuery, $baseTariffId);
            }
        }

        return [];
    }


    private function getMergingTariffPriceCaches(CalcQuery $calcQuery): array
    {
        if ($mergingTariffId = $calcQuery->getMergingTariffId()) {
            return $this->getRawPriceCaches($calcQuery, $mergingTariffId);
        }

        return [];
    }

    private function getPriceTariffPriceCaches(CalcQuery $calcQuery): array
    {
        return $this->getRawPriceCaches($calcQuery, $calcQuery->getPriceTariffId());
    }

    private function getRawPriceCaches(CalcQuery $calcQuery, string $searchingTariffId): array
    {
        return $this->priceCacheRepository
            ->fetchRaw(
                $calcQuery->getSearchBegin(),
                $calcQuery->getPriceCacheEnd(),
                $calcQuery->getPriceRoomTypeId(),
                $searchingTariffId,
                $calcQuery->isUseCategory()
            );
    }
}