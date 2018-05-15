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


    public function calcPrices(CalcHelper $calcHelper): array
    {
        $caches = $this->getPriceCaches($calcHelper);
        $this->checkPriceCaches($caches, $calcHelper->getDuration(), $calcHelper->isStrictDuration());
        $combinations = $this->getCombinations($calcHelper->getRoomType(), $calcHelper->getActualAdults(), $calcHelper->getActualChildren(), $calcHelper->isUseCategory());

        return $this->getPrices($caches, $combinations, $calcHelper);
    }

    private function getPriceCaches(CalcHelper $calcHelper): array
    {

        $caches = $this->dm
            ->getRepository(PriceCache::class)
            ->fetchRaw(
                $calcHelper->getSearchBegin(),
                $calcHelper->getPriceCacheEnd(),
                $calcHelper->getRoomType()->getId(),
                $calcHelper->getPriceTariffId(),
                $calcHelper->isUseCategory());

        return $caches;
    }

    private function checkPriceCaches(array $priceCaches, int $duration, bool $isStrictDuration): void
    {
        if ($isStrictDuration && $duration !== \count($priceCaches)) {
            throw new CalculationException('Duration not equal priceCaches count');
        }

    }


    private function getPrices(array $priceCaches, array $combinations, CalcHelper $calcHelper): array
    {
        $prices = [];
        foreach ($combinations as $combination) {

            try {
                $rawPrices = $this->getPriceForCombination($combination, $priceCaches, $calcHelper);
                $prices[$combination['adults'] . '_' . $combination['children']] = [
                    'adults' => $combination['adults'],
                    'children' => $combination['children'],
                    'total' => $rawPrices['total'],
                    'prices' => $rawPrices['prices'],
                    'packagePrices' => $rawPrices['packagePrices']
                ];
            } catch (CalculationAdditionalPriceException $e) {
                continue;
            }
        }

        return $prices;

    }

    //** TODO: Надобно придумать как декоратором сделать подсчет цены, чтоб можно было навешивать. */
    private function getPriceForCombination(array $combination, array $priceCaches, CalcHelper $calcHelper): array
    {
        $total = 0;
        foreach ($priceCaches as $priceCache) {
            $promoConditions = $this->checkPromoConditions($priceCache, $calcHelper, $combination['adults'], $combination['children']);
            $actualTourists = $this->getActualTourists($combination['adults'], $combination['children'], $promoConditions, $calcHelper);
            $isChildPrices = $calcHelper->isChildPrices();

            $all = $actualTourists['all'];
            $mainChildren = $actualTourists['mainChildren'];
            $mainAdults = $actualTourists['mainAdults'];
            $addsChildren = $actualTourists['addsChildren'];
            $addsAdults = $actualTourists['addsAdults'];

            $dayPrice = 0;



            if ($all === 1 &&$priceCache->getSinglePrice() !== null && !$priceCache->getCategoryOrRoomType($calcHelper->isUseCategory())->getIsHostel()) {
                $dayPrice += $priceCache->getSinglePrice();
            } elseif ($priceCache->getIsPersonPrice()) {
                if ($isChildPrices && $priceCache->getChildPrice() !== null) {
                    $childrenPrice = $mainChildren * $priceCache->getChildPrice();
                } else {
                    $childrenPrice = $mainChildren * $priceCache->getPrice();
                }
                if ($promoConditions && $childrenDiscount = $calcHelper->getPromotion()->getChildrenDiscount()) {
                    $childrenPrice = $childrenPrice * (100 - $childrenDiscount) / 100;
                }
                $dayPrice += $mainAdults * $priceCache->getPrice() + $childrenPrice;
            } else {
                $dayPrice += $priceCache->getPrice();
            }



            //calc adds
            if ($addsAdults && $priceCache->getAdditionalPrice() === null) {
                throw new CalculationAdditionalPriceException('There is additional adult, but no additional price');
            }

            if ($addsChildren && $priceCache->getAdditionalChildrenPrice() === null) {
                throw new CalculationAdditionalPriceException('There is additional children, but no additional price');
            }



            if ($calcHelper->isIndividualAdditionalPrices() and ($addsChildren + $addsAdults) > 1) {
                $addsPrice = 0;
                $additionalCalc = function ($num, $prices, $price, $offset = 0) {
                    $result = 0;
                    for ($i = 0; $i < $num; $i++) {
                        if (isset($prices[$i + $offset]) && $prices[$i + $offset] !== null) {
                            $result += $prices[$i + $offset];
                        } else {
                            $result += $price;
                        }
                    }

                    return $result;
                };

                $addsPrice += $additionalCalc($addsAdults, $priceCache->getAdditionalPrices(), $priceCache->getAdditionalPrice());
                $addsChildrenPrice = $additionalCalc($addsChildren, $priceCache->getAdditionalChildrenPrices(), $priceCache->getAdditionalChildrenPrice(), $addsAdults);

                if ($promoConditions && $childrenDiscount) {
                    $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
                }
                $addsPrice += $addsChildrenPrice;
            } else {
                $addsChildrenPrice = $addsChildren * $priceCache->getAdditionalChildrenPrice();

                if ($promoConditions && $childrenDiscount) {
                    $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
                }

                $addsPrice = $addsAdults * $priceCache->getAdditionalPrice() + $addsChildrenPrice;
            }




            $dayPrice += $addsPrice;




            // calc promotion discount
            if ($promoConditions) {
                $dayPrice -= PromotionConditionFactory::calcDiscount($calcHelper->getPromotion(), $dayPrice, true);
            }

            $packagePrice = $this->getPackagePrice($dayPrice, $priceCache->getDate(), $calcHelper->getTariff(), $calcHelper->getRoomType(), $calcHelper->getSpecial());
            $dayPrice = $packagePrice->getPrice();
            /** @var PriceCache $priceCache */
            $dayPrices[$priceCache->getDate()->format('d_m_Y')] = $dayPrice;

            if ($promoConditions) {
                $packagePrice->setPromotion($calcHelper->getPromotion());
            }

            $packagePrices[] = $packagePrice;
            $total += $dayPrice;

        }

        return [
            'total' => $total,
            'prices' => $dayPrices,
            'packagePrices' => $packagePrices,
        ];
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
    private function getActualTourists(int $adults, int $children, bool $isPromoConditions, CalcHelper $calcHelper): array
    {
        if ($isPromoConditions && $promotion = $calcHelper->getPromotion()) {
            $children -= (int)$promotion->getFreeChildrenQuantity();
            $adults -= (int)$promotion->getFreeAdultsQuantity();
            $adults = $adults >= 1 ? $adults : 1;
            $children = $children >= 0 ? $children : 0;
        }

        $allPeople = $adults + $children;
        $places = $calcHelper->getRoomType()->getPlaces();
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

    private function checkPromoConditions(PriceCache $priceCache, CalcHelper $calcHelper, int $adultsCombination, int $childrenCombination): bool
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