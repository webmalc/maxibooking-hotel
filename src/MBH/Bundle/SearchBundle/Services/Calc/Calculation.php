<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalcHelperException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationAdditionalPriceException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;

class Calculation
{

    /** @var PriceCachesMerger */
    private $priceCacheMerger;
    /**
     * @var SharedDataFetcher
     */
    private $sharedDataFetcher;

    /** @var int */
    private $priceRoundSign;


    /**
     * Calculation constructor.
     * @param PriceCachesMerger $merger
     * @param SharedDataFetcher $sharedDataFetcher
     */
    public function __construct(PriceCachesMerger $merger, SharedDataFetcher $sharedDataFetcher, int $priceRoundSign)
    {
        $this->priceCacheMerger = $merger;
        $this->sharedDataFetcher = $sharedDataFetcher;
        $this->priceRoundSign = $priceRoundSign;
    }


    /**
     * @param CalcQuery $calcQuery
     * @return array
     * @throws CalcHelperException
     * @throws CalculationException
     * @throws SharedFetcherException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException
     */
    public function calcPrices(CalcQuery $calcQuery): array
    {
        $priceCaches = $this->priceCacheMerger->getMergedPriceCaches($calcQuery);

        return $this->getPrices($priceCaches, $calcQuery);
    }

    /**
     * @param array $priceCaches
     * @param CalcQuery $calcQuery
     * @return array
     * @throws CalcHelperException
     * @throws CalculationException
     * @throws SharedFetcherException
     */
    private function getPrices(array $priceCaches, CalcQuery $calcQuery): array
    {
        $prices = [];
        foreach ($calcQuery->getCombinations() as $combination) {
            try {
                $combinationPrices = $this->getPriceForCombination($combination, $priceCaches, $calcQuery);
                $prices[$combination['adults'] . '_' . $combination['children']] = [
                    'adults' => $combination['adults'],
                    'children' => $combination['children'],
                    'total' => $combinationPrices['total'],
                    'prices' => $combinationPrices['prices'],
                    'packagePrices' => $combinationPrices['packagePrices']
                ];
            } catch (CalculationAdditionalPriceException $e) {
                continue;
            }
        }

        if (!\count($prices)) {
            throw new CalculationException('No prices at this time, sorry.');
        }

        return $prices;

    }


    /**
     * @param array $combination
     * @param array $priceCaches
     * @param CalcQuery $calcQuery
     * @return array
     * @throws CalculationAdditionalPriceException
     * @throws CalculationException
     * @throws CalcHelperException
     * @throws SharedFetcherException
     */
    private function getPriceForCombination(array $combination, array $priceCaches, CalcQuery $calcQuery): array
    {
        $total = 0;
        $packagePrices = $dayPrices = [];

        /** TODO: В итоге откуда брать Promotion? Из PriceCache на каждый день? */
        $rawPromotion = $calcQuery->getPromotion();
        if (null === $rawPromotion) {
            $rawPromotion = $calcQuery->getTariff()->getDefaultPromotion();
        }
        $isPromoCanApply = $this->checkPromoConditions($rawPromotion, $calcQuery->getDuration(), $combination['adults'], $combination['children']);
        $promotion = $isPromoCanApply ? $rawPromotion : null;


        $sortedTourists = $this->getSortedTourists(
            $combination['adults'],
            $combination['children'],
            $calcQuery->getRoomType()->getPlaces(),
            $promotion
        );

        $mainChildren = $sortedTourists['mainChildren'];
        $mainAdults = $sortedTourists['mainAdults'];
        $addsChildren = $sortedTourists['addsChildren'];
        $addsAdults = $sortedTourists['addsAdults'];
        $all = $sortedTourists['all'];


        foreach ($priceCaches as $cacheData) {
            $rawPriceCache = $cacheData['data'];
            $multiPrices = ($addsAdults + $addsChildren) > 1;
            $isIndividualPrices = $calcQuery->isIndividualAdditionalPrices();

            $mainAdultPrice = $this->getMainAdultsPrice($rawPriceCache, $calcQuery, $mainAdults, $all);
            $mainChildrenPrice = $this->getMainChildrenPrice($rawPriceCache, $calcQuery, $mainChildren, $all, $promotion);
            $additionalAdultPrice = $this->getAdditionalAdultsPrice($rawPriceCache, $addsAdults, $multiPrices, $isIndividualPrices);
            $additionalChildrenPrice = $this->getAdditionalChildrenPrice($rawPriceCache, $addsChildren, $multiPrices, $addsAdults, $isIndividualPrices, $promotion);

            $dayPrice = $mainAdultPrice + $mainChildrenPrice + $additionalAdultPrice + $additionalChildrenPrice;

            $dayPrice -= PromotionConditionFactory::calcDiscount($promotion, $dayPrice, true);

            $rawPriceDate = Helper::convertMongoDateToDate($rawPriceCache['date']);
            /** @var Tariff $tariff */
            $tariff = $this->sharedDataFetcher->getFetchedTariff($cacheData['searchTariffId']);
            $packagePrice = $this->getPackagePrice($dayPrice, $rawPriceDate, $tariff, $calcQuery->getRoomType(), $promotion, $calcQuery->getSpecial());
            $dayPrices[$rawPriceDate->format('d_m_Y')] = $dayPrice;
            $packagePrices[] = $packagePrice;
            $total += $dayPrice;
        }

        return [
            'total' => round($total, $this->priceRoundSign),
            'prices' => $dayPrices,
            'packagePrices' => $packagePrices,
        ];
    }


    private function getMainAdultsPrice(array $rawPriceCache, CalcQuery $calcQuery, int $mainAdults, int $all): int
    {
        $adultPrice = $rawPriceCache['price'];
        $price = $adultPrice;

        if ($all === 1 && null !== ($rawPriceCache['singlePrice'] ?? null) && !$calcQuery->getRoomType()->getIsHostel()) {
            $price = $rawPriceCache['singlePrice'];
        }
        if ($all !== 1 && $rawPriceCache['isPersonPrice']) {
            $price =  $mainAdults * $adultPrice;
        }

        return $price;
    }

    /**
     * @param array $rawPriceCache
     * @param CalcQuery $calcQuery
     * @param int $mainChildren
     * @param int $all
     * @param Promotion|null $promotion
     * @return float|int
     * @throws CalcHelperException
     * @throws CalculationException
     */
    private function getMainChildrenPrice(array $rawPriceCache, CalcQuery $calcQuery, int $mainChildren, int $all, Promotion $promotion = null)

    {
        $price = 0;
        $childPrice = $rawPriceCache['price'];
        if (($rawPriceCache['childPrice'] ?? null) && $calcQuery->isChildPrices()) {
            $childPrice = $rawPriceCache['childPrice'];
        }
        if ($promotion && $childrenDiscount = $promotion->getChildrenDiscount()) {
            $childPrice = $childPrice * (100 - $childrenDiscount) / 100;
        }

        if ($rawPriceCache['isPersonPrice']) {
            $price = $mainChildren * $childPrice;
        }

        return $price;
    }


    /**
     * @param array $rawPriceCache
     * @param int $addsAdults
     * @param bool $multiPrices
     * @return float|int
     * @throws CalculationAdditionalPriceException
     */
    private function getAdditionalAdultsPrice(array $rawPriceCache, int $addsAdults, bool $multiPrices, bool $isIndividualPrices)
    {
        $addsAdultsPrice = 0;
        if (!$addsAdults) {
            return $addsAdultsPrice;
        }

        $additionalPrice = $rawPriceCache['additionalPrice'] ?? $rawPriceCache['price'] ?? null;

        if ($addsAdults && $additionalPrice === null) {
            throw new CalculationAdditionalPriceException('There is additional adult, but no additional price');
        }

        if ((!$multiPrices && $addsAdults) || ($multiPrices && !$isIndividualPrices)) {
            $addsAdultsPrice = $addsAdults * $additionalPrice;
        }
        if ($multiPrices && $isIndividualPrices) {
            $addsAdultsPrice += $this->multiAdditionalPricesCalc($addsAdults, $rawPriceCache['additionalPrices'], $additionalPrice);
        }

        return $addsAdultsPrice;
    }

    /**
     * @param array $rawPriceCache
     * @param int $addsChildren
     * @param bool $multiPrices
     * @param int $addsAdults
     * @param bool $isIndividualPrices
     * @param Promotion|null $promotion
     * @return int
     * @throws CalculationAdditionalPriceException
     */
    private function getAdditionalChildrenPrice(array $rawPriceCache, int $addsChildren, bool $multiPrices, int $addsAdults, bool $isIndividualPrices, Promotion $promotion = null): int
    {
        $addsChildrenPrice = 0;
        if (!$addsChildren) {
            return $addsChildrenPrice;
        }
        $additionalChildrenPrice = $rawPriceCache['additionalChildrenPrice'] ?? null;

        if ($addsChildren && null === $additionalChildrenPrice) {
            throw new CalculationAdditionalPriceException('There is additional additional child, but no additional price for children');
        }


        if ((!$multiPrices && $addsChildren) || ($multiPrices && !$isIndividualPrices) ) {
            $addsChildrenPrice = $addsChildren * $rawPriceCache['additionalChildrenPrice'];
        }
        if ($multiPrices && $isIndividualPrices) {
            $addsChildrenPrice += $this->multiAdditionalPricesCalc($addsChildren, $rawPriceCache['additionalChildrenPrices'], $rawPriceCache['additionalChildrenPrice'], $addsAdults);
        }

        if ($promotion && $promotion->getChildrenDiscount()) {
            $addsChildrenPrice = $addsChildrenPrice * (100 - $promotion->getChildrenDiscount()) / 100;
        }

        return $addsChildrenPrice;
    }

    private function multiAdditionalPricesCalc($addsAdults, $additionalPrices, $additionalPrice, $offset = 0): int
    {
        $result = 0;
        for ($i = 0; $i < $addsAdults; $i++) {
            if (isset($additionalPrices[$i + $offset]) && $additionalPrices[$i + $offset] !== null) {
                $result += $additionalPrices[$i + $offset];
            } else {
                $result += $additionalPrice;
            }
        }

        return $result;
    }


    /**
     * @param $price
     * @param \DateTime $date
     * @param Tariff $tariff
     * @param RoomType $roomType
     * @param Special|null $special
     * @return PackagePrice
     */
    private function getPackagePrice($price, \DateTime $date, Tariff $tariff, RoomType $roomType, Promotion $promotion = null, Special $special = null): PackagePrice
    {
        $packagePrice = new PackagePrice($date, $price > 0 ? $price : 0, $tariff);
        if ($special &&
            $date >= $special->getBegin() && $date <= $special->getEnd() &&
            $special->check($roomType) && $special->check($tariff)
        ) {
            $price = $special->isIsPercent() ? $price - $price * $special->getDiscount() / 100 : $price - $special->getDiscount();
            $packagePrice->setPrice($price)->setSpecial($special);
        }
        if ($promotion) {
            $packagePrice->setPromotion($promotion);
        }

        return $packagePrice;
    }

    //Сортировка кто на основное, кто на доп места.

    /**
     * @param int $adults
     * @param int $children
     * @param int $places
     * @param Promotion|null $promotion
     * @return array
     */
    private function getSortedTourists(int $adults, int $children, int $places, Promotion $promotion = null): array
    {
        if ($promotion) {
            $children -= (int)$promotion->getFreeChildrenQuantity();
            $adults -= (int)$promotion->getFreeAdultsQuantity();
            $adults = $adults >= 1 ? $adults : 1;
            $children = $children >= 0 ? $children : 0;
        }

        $allPeople = $adults + $children;
        $isOverMain = $allPeople > $places;


        if ($isOverMain) {

            if ($adults >= $places) {
                $mainAdults = $places;
                $mainChildren = 0;
            } else {
                $mainAdults = $adults;
                $mainChildren = $places - $adults;
            }

            $overPeopleCount = $allPeople - $places;
            if ($overPeopleCount > $children) {
                $addsChildren = $children;
                $addsAdults = $overPeopleCount - $addsChildren;
            } else {
                $addsChildren = $overPeopleCount;
                $addsAdults = 0;
            }

        } else {
            $mainAdults = $adults;
            $mainChildren = $children;
            $addsAdults = 0;
            $addsChildren = 0;
        }

        return [
            'mainAdults' => $mainAdults,
            'mainChildren' => $mainChildren,
            'addsAdults' => $addsAdults,
            'addsChildren' => $addsChildren,
            'all' => $allPeople
        ];
    }

    /**
     * @param Promotion|null $promotion
     * @param int $duration
     * @param int $adultsCombination
     * @param int $childrenCombination
     * @return bool
     */
    private function checkPromoConditions(Promotion $promotion = null, int $duration, int $adultsCombination, int $childrenCombination): bool
    {
        $promoConditions = (bool)PromotionConditionFactory::checkConditions(
            $promotion,
            $duration,
            $adultsCombination,
            $childrenCombination
        );
        //* TODO: Тут уточнить какой id надо брать тарифа из калкхелпера, родительский или текущий
        // или вообще не нужна эта проверка т.к. у нас тут priceCache берется именно для этого тарифа
        // */
        return $promoConditions/* && ($priceCache->getTariff()->getId() !== $calcHelper->getPriceTariffId())*/;
    }


}