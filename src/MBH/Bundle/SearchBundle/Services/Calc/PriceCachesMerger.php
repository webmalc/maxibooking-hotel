<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\PriceBundle\Document\PriceCacheRepository;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationException;

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
//        $this->checkPriceCaches($priceTariffCaches, $calcQuery->getDuration(), $calcQuery->isStrictDuration());
        /*if (!$isStrictDuration && $duration !== \count($priceCaches)) {
            throw new CalculationException('Duration not equal priceCaches count');
        }*/
        if (!\count($priceTariffCaches)) {
            throw new CalculationException('No even one priceCache for tariff ' . $calcQuery->getTariff()->getName());
        }
//        if ($calcQuery->getDuration() === \count($priceTariffCaches)) {
//            return $priceTariffCaches;
//        }

        $mergintPriceCaches = $this->getMergingTariffPriceCaches($calcQuery);
        //** TODO: Тут смерджить прайс кэши и если не хватает берем базовый */

        $baseTariffPriceCaches = $this->getBaseTariffPriceCaches($calcQuery);
        //** TODO: Мерджим и если не хватает тогда эксепшн */


        return [];
    }

    private function getBaseTariffPriceCaches(CalcQuery $calcQuery)
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