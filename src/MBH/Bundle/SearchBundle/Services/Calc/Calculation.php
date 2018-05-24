<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationAdditionalPriceException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationException;
use MBH\Bundle\SearchBundle\Lib\HotelContentHolder;

class Calculation
{

    /** @var PriceCachesMerger */
    private $priceCacheMerger;

    /** @var HotelContentHolder */
    private $hotelContentHolder;

    /**
     * Calculation constructor.
     * @param PriceCachesMerger $merger
     * @param HotelContentHolder $contentHolder
     */
    public function __construct(PriceCachesMerger $merger, HotelContentHolder $contentHolder)
    {
        $this->priceCacheMerger = $merger;
        $this->hotelContentHolder = $contentHolder;
    }


    public function calcPrices(CalcQuery $calcQuery): array
    {
        $caches = $this->getPriceCaches($calcQuery);
        $combinations = $this->getCombinations($calcQuery->getRoomType(), $calcQuery->getActualAdults(), $calcQuery->getActualChildren(), $calcQuery->isUseCategory());

        return $this->getPrices($caches, $combinations, $calcQuery);
    }

    private function getPriceCaches(CalcQuery $calcQuery): array
    {
        return $this->priceCacheMerger->getMergedPriceCaches($calcQuery);
    }

    private function getPrices(array $priceCaches, array $combinations, CalcQuery $calcHelper): array
    {
        $prices = [];
        foreach ($combinations as $combination) {
            try {
                $combinationPrices = $this->getPriceForCombination($combination, $priceCaches, $calcHelper);
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

        return $prices;

    }


    /**
     * @param array $combination
     * @param array $priceCaches
     * @param CalcQuery $calcQuery
     * @return array
     * @throws CalculationAdditionalPriceException
     * @throws CalculationException
     */
    private function getPriceForCombination(array $combination, array $priceCaches, CalcQuery $calcQuery): array
    {
        $total = 0;
        $packagePrices = $dayPrices = [];
        foreach ($priceCaches as $rawPriceCache) {
//            $promoConditions = $this->checkPromoConditions($priceCache, $calcHelper, $combination['adults'], $combination['children']);

            $promotion = $calcQuery->getPromotion();
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

            $mainChildrenPrice = $this->getMainChildrenPrice($rawPriceCache, $calcQuery, $mainChildren, $promotion);
            $mainAdultPrice = $this->getMainAdultsPrice($rawPriceCache, $calcQuery, $mainAdults, $all);
            $multiPrices = ($addsAdults + $addsChildren) > 1;
            $isIndividualPrices = $calcQuery->isIndividualAdditionalPrices();
            $additionalAdultPrice = $this->getAdditionalAdultsPrice($rawPriceCache, $addsAdults, $multiPrices, $isIndividualPrices);
            $additionalChildrenPrice = $this->getAdditionalChildrenPrice($rawPriceCache, $addsChildren, $multiPrices, $addsAdults, $isIndividualPrices, $promotion);

            $dayPrice = $mainAdultPrice + $mainChildrenPrice + $additionalAdultPrice + $additionalChildrenPrice;

            if ($promotion) {
                $dayPrice -= PromotionConditionFactory::calcDiscount($promotion, $dayPrice, true);
            }

            $rawPriceDate = Helper::convertMongoDateToDate($rawPriceCache['date']);
            /** @var Tariff $tariff */
            $tariff = $this->hotelContentHolder->getFetchedTariff((string)$rawPriceCache['tariff']['$id']);
            $packagePrice = $this->getPackagePrice($dayPrice, $rawPriceDate, $tariff, $calcQuery->getRoomType(), $calcQuery->getSpecial());
            $dayPrices[$rawPriceDate->format('d_m_Y')] = $dayPrice;
            $packagePrices[] = $packagePrice;
            $total += $dayPrice;
        }

        return [
            'total' => $total,
            'prices' => $dayPrices,
            'packagePrices' => $packagePrices,
        ];
    }


    private function getMainAdultsPrice(array $rawPriceCache, CalcQuery $calcQuery, int $mainAdults, int $all): int
    {
        $adultPrice = $rawPriceCache['price'];
        $price = $adultPrice;

        if ($all === 1 && null !== $rawPriceCache['singlePrice'] && !$calcQuery->getRoomType()->getIsHostel()) {
            $price = $rawPriceCache['singlePrice'];
        }
        if ($all !== 1 && $rawPriceCache['isPersonPrice']) {
            $price =  $mainAdults * $adultPrice;
        }

        return $price;
    }

    private function getMainChildrenPrice(array $rawPriceCache, CalcQuery $calcQuery, int $mainChildren, Promotion $promotion = null)

    {
        $price = 0;
        $childPrice = $rawPriceCache['price'];
        if ($calcQuery->getRoomType()->getIsChildPrices()) {
            $childPrice = $rawPriceCache['childPrice'];
            if (null === $childPrice) {
                throw new CalculationException('No required child price found!');
            }
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
        if ($addsAdults && $rawPriceCache['additionalPrice'] === null) {
            throw new CalculationAdditionalPriceException('There is additional adult, but no additional price');
        }
        $addsAdultsPrices = 0;
        if ((!$multiPrices && $addsAdults) || ($multiPrices && !$isIndividualPrices)) {
            $addsAdultsPrices = $addsAdults * $rawPriceCache['additionalPrice'];
        }
        if ($multiPrices && $isIndividualPrices) {
            $addsAdultsPrices += $this->multiAdditionalPricesCalc($addsAdults, $rawPriceCache['additionalPrices'], $rawPriceCache['additionalPrice']);
        }

        return $addsAdultsPrices;
    }

    private function getAdditionalChildrenPrice(array $rawPriceCache, int $addsChildren, bool $multiPrices, int $addsAdults, bool $isIndividualPrices, Promotion $promotion = null): int
    {
        $additionalChildrenPrice = $rawPriceCache['additionalChildrenPrice'] ?? null;

        if ($addsChildren && null === $additionalChildrenPrice) {
            throw new CalculationAdditionalPriceException('There is additional additional child, but no additional price for children');
        }

        $addsChildrenPrices = 0;
        if ((!$multiPrices && $addsChildren) || ($multiPrices && !$isIndividualPrices) ) {
            $addsChildrenPrices = $addsChildren * $rawPriceCache['additionalChildrenPrice'];
        }
        if ($multiPrices && $isIndividualPrices) {
            $addsChildrenPrices += $this->multiAdditionalPricesCalc($addsChildren, $rawPriceCache['additionalChildrenPrices'], $rawPriceCache['additionalChildrenPrice'], $addsAdults);
        }

        if ($promotion && $promotion->getChildrenDiscount()) {
            $addsChildrenPrices = $addsChildrenPrices * (100 - $promotion->getChildrenDiscount()) / 100;
        }

        return $addsChildrenPrices;
    }

    private function multiAdditionalPricesCalc($num, $additionalPrices, $additionalPrice, $offset = 0): int
    {
        $result = 0;
        for ($i = 0; $i < $num; $i++) {
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
    private function getPackagePrice($price, \DateTime $date, Tariff $tariff, RoomType $roomType, Special $special = null): PackagePrice
    {
        $packagePrice = new PackagePrice($date, $price > 0 ? $price : 0, $tariff);
        if ($special &&
            $date >= $special->getBegin() && $date <= $special->getEnd() &&
            $special->check($roomType) && $special->check($tariff)
        ) {
            $price = $special->isIsPercent() ? $price - $price * $special->getDiscount() / 100 : $price - $special->getDiscount();
            $packagePrice->setPrice($price)->setSpecial($special);
        }

        return $packagePrice;
    }

    //Сортировка кто на основное, кто на доп места.
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

    private function checkPromoConditions(PriceCache $priceCache, CalcQuery $calcHelper, int $adultsCombination, int $childrenCombination): bool
    {
        $promoConditions = (bool)PromotionConditionFactory::checkConditions(
            $calcHelper->getPromotion(),
            $calcHelper->getDuration(),
            $adultsCombination,
            $childrenCombination
        );
        //* TODO: Тут уточнить какой id надо брать тарифа из калкхелпера, родительский или текущий
        // или вообще не нужна эта проверка т.к. у нас тут priceCache берется именно для этого тарифа
        // */
        return $promoConditions && ($priceCache->getTariff()->getId() !== $calcHelper->getPriceTariffId());
    }

    private function getCombinations(RoomType $roomType, int $adults, int $children, bool $isUseCategory): array

    {
        if ($adults === 0 && $children === 0) {
            $combinations = $roomType->getAdultsChildrenCombinations($isUseCategory);
        } else {
            $combinations = [0 => ['adults' => $adults, 'children' => $children]];
        }

        return $combinations;
    }



}