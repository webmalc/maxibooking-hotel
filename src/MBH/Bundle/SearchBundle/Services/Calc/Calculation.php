<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use Doctrine\ODM\MongoDB\DocumentManager;
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

class Calculation
{


    /** @var DocumentManager */
    private $dm;

    /**
     * Calculation constructor.
     * @param RoomTypeManager $roomTypeManager
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    public function calcPrices(CalcQuery $calcQuery): array
    {
        $caches = $this->getPriceCaches($calcQuery);
        $combinations = $this->getCombinations($calcQuery->getRoomType(), $calcQuery->getActualAdults(), $calcQuery->getActualChildren(), $calcQuery->isUseCategory());

        return $this->getPrices($caches, $combinations, $calcQuery);
    }

    private function getPriceCaches(CalcQuery $calcQuery): array
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


        return $priceTariffCaches;
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


    private function getPriceForCombination(array $combination, array $priceCaches, CalcQuery $calcQuery): array
    {
        $total = 0;
        $packagePrices = $dayPrices = [];
        foreach ($priceCaches as $priceCache) {
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

            $mainAdultPrice = $this->getMainAdultsPrice($priceCache, $calcQuery, $mainAdults, $all);
            $mainChildrenPrice = $this->getMainChildrenPrice($priceCache, $calcQuery, $mainChildren, $promotion);
            $multiPrices = ($addsAdults + $addsChildren) > 1;
            $additionalAdultPrice = $this->getAdditionalAdultsPrice($priceCache, $addsAdults, $multiPrices);
            $additionalChildrenPrice = $this->getAdditionalChildrenPrice($priceCache, $calcQuery, $addsChildren, $multiPrices, $addsAdults, $promotion);

            $dayPrice = $mainAdultPrice + $mainChildrenPrice + $additionalAdultPrice + $additionalChildrenPrice;

//            $isChildPrices = $calcQuery->isChildPrices();
//            if ($all === 1 && $priceCache->getSinglePrice() !== null && !$priceCache->getCategoryOrRoomType($calcQuery->isUseCategory())->getIsHostel()) {
//                $dayPrice += $priceCache->getSinglePrice();
//            } elseif ($priceCache->getIsPersonPrice()) {
//                if ($isChildPrices && $priceCache->getChildPrice() !== null) {
//                    $childrenPrice = $mainChildren * $priceCache->getChildPrice();
//                } else {
//                    $childrenPrice = $mainChildren * $priceCache->getPrice();
//                }
//                if ($promoConditions && $childrenDiscount = $calcQuery->getPromotion()->getChildrenDiscount()) {
//                    $childrenPrice = $childrenPrice * (100 - $childrenDiscount) / 100;
//                }
//                $dayPrice += $mainAdults * $priceCache->getPrice() + $childrenPrice;
//            } else {
//                $dayPrice += $priceCache->getPrice();
//            }
//
//
//            //calc adds
//            if ($addsAdults && $priceCache->getAdditionalPrice() === null) {
//                throw new CalculationAdditionalPriceException('There is additional adult, but no additional price');
//            }
//
//            if ($addsChildren && $priceCache->getAdditionalChildrenPrice() === null) {
//                throw new CalculationAdditionalPriceException('There is additional children, but no additional price');
//            }
//
//
//            if ($calcQuery->isIndividualAdditionalPrices() and ($addsChildren + $addsAdults) > 1) {
//                $addsPrice = 0;
//                $additionalCalc = function ($num, $prices, $price, $offset = 0) {
//                    $result = 0;
//                    for ($i = 0; $i < $num; $i++) {
//                        if (isset($prices[$i + $offset]) && $prices[$i + $offset] !== null) {
//                            $result += $prices[$i + $offset];
//                        } else {
//                            $result += $price;
//                        }
//                    }
//
//                    return $result;
//                };
//
//                $addsPrice += $additionalCalc($addsAdults, $priceCache->getAdditionalPrices(), $priceCache->getAdditionalPrice());
//                $addsChildrenPrice = $additionalCalc($addsChildren, $priceCache->getAdditionalChildrenPrices(), $priceCache->getAdditionalChildrenPrice(), $addsAdults);
//
//                if ($promoConditions && $childrenDiscount) {
//                    $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
//                }
//                $addsPrice += $addsChildrenPrice;
//            } else {
//                $addsChildrenPrice = $addsChildren * $priceCache->getAdditionalChildrenPrice();
//
//                if ($promoConditions && $childrenDiscount) {
//                    $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
//                }
//
//                $addsPrice = $addsAdults * $priceCache->getAdditionalPrice() + $addsChildrenPrice;
//            }
//            $dayPrice += $addsPrice;
            // calc promotion discount
            if ($promotion) {
                $dayPrice -= PromotionConditionFactory::calcDiscount($promotion, $dayPrice, true);
            }

            $packagePrice = $this->getPackagePrice($dayPrice, $priceCache->getDate(), $calcQuery->getTariff(), $calcQuery->getRoomType(), $calcQuery->getSpecial());

            /** @var PriceCache $priceCache */
            $dayPrices[$priceCache->getDate()->format('d_m_Y')] = $dayPrice;


            $packagePrices[] = $packagePrice;
            $total += $dayPrice;
        }

        return [
            'total' => $total,
            'prices' => $dayPrices,
            'packagePrices' => $packagePrices,
        ];
    }


    private function getMainAdultsPrice(PriceCache $priceCache, CalcQuery $calcQuery, int $mainAdults, int $all): int
    {
        $adultPrice = $priceCache->getPrice();
        $price = $adultPrice;

        if ($all === 1 && null !== $priceCache->getSinglePrice() && !$calcQuery->getRoomType()->getIsHostel()) {
            $price = $priceCache->getSinglePrice();
        }
        if ($priceCache->getIsPersonPrice()) {
            $price =  $mainAdults * $adultPrice;
        }

        return $price;
    }

    private function getMainChildrenPrice(PriceCache $priceCache, CalcQuery $calcQuery, int $mainChildren, Promotion $promotion = null)

    {
        $price = 0;
        $childPrice = $priceCache->getPrice();
        if ($calcQuery->getRoomType()->getIsChildPrices()) {
            $childPrice = $priceCache->getChildPrice();
            if (null === $childPrice) {
                throw new CalculationException('No required child price found!');
            }
        }
        if ($promotion && $childrenDiscount = $promotion->getChildrenDiscount()) {
            $childPrice = $childPrice * (100 - $childrenDiscount) / 100;
        }

        if ($priceCache->getIsPersonPrice()) {
            $price = $mainChildren * $childPrice;
        }

        return $price;
    }


    private function getAdditionalAdultsPrice(PriceCache $priceCache, int $addsAdults, bool $multiPrices)
    {
        if ($addsAdults && $priceCache->getAdditionalPrice() === null) {
            throw new CalculationAdditionalPriceException('There is additional adult, but no additional price');
        }
        $addsAdultsPrices = 0;
        if (!$multiPrices && $addsAdults) {
            $addsAdultsPrices = $addsAdults * $priceCache->getAdditionalPrices();
        }
        if ($multiPrices) {
            $addsAdultsPrices += $this->multiAdditionalPricesCalc($addsAdults, $priceCache->getAdditionalPrices(), $priceCache->getAdditionalPrice());
        }

        return $addsAdultsPrices;
    }

    private function getAdditionalChildrenPrice(PriceCache $priceCache, CalcQuery $calcQuery, int $addsChildren, bool $multiPrices, int $addsAdults, Promotion $promotion = null): int
    {
        if ($addsChildren && $priceCache->getAdditionalChildrenPrice() === null) {
            throw new CalculationAdditionalPriceException('There is additional children, but no additional price');
        }

        $addsChildrenPrices = 0;
        if (!$multiPrices && $addsChildren) {
            $addsChildrenPrices = $addsChildren * $priceCache->getAdditionalChildrenPrice();
        }
        if ($multiPrices) {
            $addsChildrenPrices += $this->multiAdditionalPricesCalc($addsChildren, $priceCache->getAdditionalChildrenPrice(), $priceCache->getAdditionalPrice(), $addsAdults);
        }

        if ($promotion && $promotion->getChildrenDiscount()) {
            $addsChildrenPrices = $addsChildrenPrices * (100 - $promotion->getChildrenDiscount()) / 100;
        }

        return $addsChildrenPrices;
    }

    private function multiAdditionalPricesCalc($num, $prices, $price, $offset = 0): int
    {
        $result = 0;
        for ($i = 0; $i < $num; $i++) {
            if (isset($prices[$i + $offset]) && $prices[$i + $offset] !== null) {
                $result += $prices[$i + $offset];
            } else {
                $result += $price;
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


    private function getBaseTariffPriceCaches(CalcQuery $calcQuery)
    {
        if (!$calcQuery->getTariff()->getIsDefault()) {
            $hotelId = $calcQuery->getTariff()->getHotel()->getId();
            $rawBaseTariffArray = $this->dm->getRepository(Tariff::class)->fetchRawBaseTariffId($hotelId);
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
        return $this->dm
            ->getRepository(PriceCache::class)
            ->fetchRaw(
                $calcQuery->getSearchBegin(),
                $calcQuery->getPriceCacheEnd(),
                $calcQuery->getPriceRoomTypeId(),
                $searchingTariffId,
                $calcQuery->isUseCategory()
            );
    }
}