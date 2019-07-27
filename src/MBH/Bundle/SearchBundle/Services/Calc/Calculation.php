<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use DateTime;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\SpecialRepository;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalcHelperException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationAdditionalPriceException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\Price;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\DayPriceBuilder;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\DayPriceDirector;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\PriceBuilder;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\PriceDirector;

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

    /** @var CalculationHelper */
    private $calculationHelper;

    /** @var SpecialRepository */
    private $specialRepository;


    /**
     * Calculation constructor.
     * @param PriceCachesMerger $merger
     * @param SharedDataFetcher $sharedDataFetcher
     * @param CalculationHelper $calculationHelper
     * @param int $priceRoundSign
     * @param SpecialRepository $specialRepository
     */
    public function __construct(PriceCachesMerger $merger, SharedDataFetcher $sharedDataFetcher, CalculationHelper $calculationHelper, int $priceRoundSign, SpecialRepository $specialRepository)
    {
        $this->priceCacheMerger = $merger;
        $this->sharedDataFetcher = $sharedDataFetcher;
        $this->priceRoundSign = $priceRoundSign;
        $this->calculationHelper = $calculationHelper;
        $this->specialRepository = $specialRepository;
    }


    /**
     * @param CalcQueryInterface $calcQuery
     * @param int $adults
     * @param int $children
     * @return array
     * @throws CalcHelperException
     * @throws CalculationException
     * @throws SharedFetcherException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException
     */
    public function calcPrices(CalcQueryInterface $calcQuery, int $adults = null, int $children = null): array
    {
        $priceCaches = $this->priceCacheMerger->getMergedPriceCaches($calcQuery);

        return $this->getPrices($priceCaches, $calcQuery, $adults, $children);
    }

    /**
     * @param array $priceCaches
     * @param CalcQueryInterface $calcQuery
     * @param int $adults
     * @param int $children
     * @return array
     * @throws CalcHelperException
     * @throws CalculationException
     * @throws SharedFetcherException
     */
    private function getPrices(array $priceCaches, CalcQueryInterface $calcQuery, ?int $adults, ?int $children): array
    {
        $prices = [];
        $combinations = $this->calculationHelper
            ->getCombinations(
                $adults ?? $calcQuery->getAdults(),
                $children ?? $calcQuery->getChildren(),
                $calcQuery->getRoomTypeId()
            );

        foreach ($combinations as $combination) {
            try {
                $prices[] = $this->getPriceForCombination($combination, $priceCaches, $calcQuery);
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
     * @param CalcQueryInterface $calcQuery
     * @return Price
     * @throws CalcHelperException
     * @throws CalculationAdditionalPriceException
     * @throws SharedFetcherException
     */
    private function getPriceForCombination(array $combination, array $priceCaches, CalcQueryInterface $calcQuery): Price
    {
        $searchAdults = $combination['adults'];
        $searchChildren = $combination['children'];

        $duration = $this->getDuration($calcQuery);
        $promotion = $this->getTariffPromotion($calcQuery->getTariffId(), $searchAdults, $searchChildren, $duration);

        /** TODO: We have roomTypeId and TariffId in priceCaches! No need get it from calcQuery! Fix it! */
        $roomType = $this->sharedDataFetcher->getFetchedRoomType($calcQuery->getRoomTypeId());
        $places = $roomType->getPlaces();

        $sortedTourists = $this->getSortedTourists(
            $searchAdults,
            $searchChildren,
            $places,
            $promotion
        );

        $mainChildren = $sortedTourists['mainChildren'];
        $mainAdults = $sortedTourists['mainAdults'];
        $addsChildren = $sortedTourists['addsChildren'];
        $addsAdults = $sortedTourists['addsAdults'];
        $all = $sortedTourists['all'];

        $pricesByDay = [];
        foreach ($priceCaches as $cacheData) {
            /** Move promotion here */
            $rawPriceCache = $cacheData['data'];
            $originalTariffId = $cacheData['searchTariffId'];
            $originalTariff = $this->sharedDataFetcher->getFetchedTariff($originalTariffId);

            $multiPrices = ($addsAdults + $addsChildren) > 1;
            $isIndividualPrices = $this->calculationHelper->isIndividualAdditionalPrices($roomType);

            $mainChildrenPrice = $this->getMainChildrenPrice($rawPriceCache, $roomType, $mainChildren, $promotion);
            $mainAdultPrice = $this->getMainAdultsPrice($rawPriceCache, $roomType, $mainAdults, $all);
            $additionalAdultPrice = $this->getAdditionalAdultsPrice($rawPriceCache, $addsAdults, $multiPrices, $isIndividualPrices);
            $additionalChildrenPrice = $this->getAdditionalChildrenPrice($rawPriceCache, $addsChildren, $multiPrices, $addsAdults, $isIndividualPrices, $promotion);

            $dayPrice = $mainAdultPrice + $mainChildrenPrice + $additionalAdultPrice + $additionalChildrenPrice;


            $date = Helper::convertMongoDateToDate($rawPriceCache['date']);

            $dayPriceBuilder = new DayPriceBuilder();
            $dayPriceDirector = new DayPriceDirector($dayPriceBuilder);

            $specialId = $calcQuery->getSpecialId();
            $specialDiscount = null;
            if (null !== $specialId) {
                /** @var Special $special */
                $special = $this->specialRepository->find($specialId);
                $specialDiscount = $this->calcSpecialDiscount($date, $special, $roomType->getId(), $originalTariffId, $dayPrice);
            }

            if ($specialDiscount) {
                /** @var Special $special */
                $dayPrice -= $specialDiscount;
                $priceByDay = $dayPriceDirector->createDayPriceSpecial($date, $sortedTourists, $roomType, $originalTariff, $dayPrice, $special);
            } elseif (($promotion = $this->getTariffPromotion($originalTariffId, $searchAdults, $searchChildren, $duration)) && ($promotion instanceof Promotion)) {
                $promotionDiscount = PromotionConditionFactory::calcDiscount($promotion, $dayPrice, true);
                $dayPrice -= $promotionDiscount;
                $priceByDay = $dayPriceDirector->createDayPricePromotion($date, $sortedTourists, $roomType, $originalTariff, $dayPrice, $promotion);
            } else {
                $priceByDay = $dayPriceDirector->createDayPrice($date, $sortedTourists, $roomType, $originalTariff, $dayPrice);
            }

            $pricesByDay[] = $priceByDay;
        }


        return $this->createPrice($calcQuery, $pricesByDay);
    }

    private function createPrice(CalcQueryInterface $calcQuery, array $dayPrices): Price
    {
        $priceBuilder = new PriceBuilder();
        $priceDirector = new PriceDirector($priceBuilder);

        return $priceDirector->createPrice($calcQuery, $dayPrices);
    }

    private function calcSpecialDiscount(DateTime $date, Special $special, string $roomTypeId, string $tariffId, float $price): ?float
    {
        $tariff = $this->sharedDataFetcher->getFetchedTariff($tariffId);
        $roomType = $this->sharedDataFetcher->getFetchedRoomType($roomTypeId);
        $discount = null;
        if ($special
            && $date >= $special->getBegin()
            && $date <= $special->getEnd()
            && $special->check($roomType)
            && $special->check($tariff)
        ) {
            $discount = $special->isIsPercent() ? $price * $special->getDiscount() / 100 : $special->getDiscount();
        }

        return $discount;
    }

    private function getMainAdultsPrice(array $rawPriceCache, RoomType $roomType, int $mainAdults, int $all): float
    {
        $adultPrice = $rawPriceCache['price'];
        $price = $adultPrice;

        if ($all === 1 && null !== ($rawPriceCache['singlePrice'] ?? null) && !$roomType->getIsHostel()) {
            $price = $rawPriceCache['singlePrice'];
        }
        if ($all !== 1 && $rawPriceCache['isPersonPrice']) {
            $price = $mainAdults * $adultPrice;
        }

        return $price;
    }

    /**
     * @param array $rawPriceCache
     * @param RoomType $roomType
     * @param int $mainChildren
     * @param int $all
     * @param Promotion|null $promotion
     * @return float|int
     * @throws CalcHelperException
     */
    private function getMainChildrenPrice(array $rawPriceCache, RoomType $roomType, int $mainChildren, ?Promotion $promotion = null): float

    {
        $price = 0;
        $childPrice = $rawPriceCache['price'];
        if (($rawPriceCache['childPrice'] ?? null) && $this->calculationHelper->isChildPrices($roomType)) {
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
    private function getAdditionalAdultsPrice(array $rawPriceCache, int $addsAdults, bool $multiPrices, bool $isIndividualPrices): float
    {
        $addsAdultsPrices = 0;
        if (!$addsAdults) {
            return $addsAdultsPrices;
        }

        $additionalPrice = $rawPriceCache['additionalPrice'] ?? $rawPriceCache['price'] ?? null;

        if ($addsAdults && $additionalPrice === null) {
            throw new CalculationAdditionalPriceException('There is additional adult, but no additional price');
        }

        if ((!$multiPrices && $addsAdults) || ($multiPrices && !$isIndividualPrices)) {
            $addsAdultsPrices = $addsAdults * $additionalPrice;
        }
        if ($multiPrices && $isIndividualPrices) {
            $addsAdultsPrices += $this->multiAdditionalPricesCalc($addsAdults, $rawPriceCache['additionalPrices'], $additionalPrice);
        }

        return $addsAdultsPrices;
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
    private function getAdditionalChildrenPrice(array $rawPriceCache, int $addsChildren, bool $multiPrices, int $addsAdults, bool $isIndividualPrices, Promotion $promotion = null): float
    {
        $addsChildrenPrice = 0;
        if (!$addsChildren) {
            return $addsChildrenPrice;
        }
        $additionalChildrenPrice = $rawPriceCache['additionalChildrenPrice'] ?? null;

        if ($addsChildren && null === $additionalChildrenPrice) {
            throw new CalculationAdditionalPriceException('There is additional additional child, but no additional price for children');
        }


        if ((!$multiPrices && $addsChildren) || ($multiPrices && !$isIndividualPrices)) {
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

    private function multiAdditionalPricesCalc($addsAdults, $additionalPrices, $additionalPrice, $offset = 0): float
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
        return $promoConditions/* && ($priceCache->getTariff()->getId() !== $calcHelper->getPriceTariffId())*/ ;
    }

    private function getDuration(CalcQueryInterface $calcQuery): int
    {
        return (int)$calcQuery->getEnd()->diff($calcQuery->getBegin())->format('%a');
    }

    private function getTariffPromotion(string $tariffId, int $adults, int $children, int $duration): ?Promotion
    {
        /** TODO: В итоге откуда брать Promotion? Из PriceCache на каждый день? Тогда проблемы в getSortedTourists */
        $tariff = $this->sharedDataFetcher->getFetchedTariff($tariffId);
        /** TODO: Move promotion inside priceCachesIteration */
        $rawPromotion = $tariff->getDefaultPromotion();
        $isPromoCanApply = false;
        if ($rawPromotion) {
            $isPromoCanApply = $this->checkPromoConditions($rawPromotion, $duration, $adults, $children);
        }

        return $isPromoCanApply ? $rawPromotion : null;
    }


}