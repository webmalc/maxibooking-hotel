<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 02.03.17
 * Time: 14:37
 */

namespace MBH\Bundle\PackageBundle\Services;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;

class MagicCalculation extends Calculation
{

    public function calcPrices(
        RoomType $roomType,
        Tariff $tariff,
        \DateTime $begin,
        \DateTime $end,
        $adults = 0,
        $children = 0,
        Promotion $promotion = null,
        $useCategories = false,
        Special $special = null,
        $useDuration = true,
        array $ages = null
    ) {
        $prices = [];
        $memcached = $this->container->get('mbh.cache');
        $places = $roomType->getPlaces();
        $hotel = $roomType->getHotel();
        $useCategories ? $isChildPrices = $roomType->getCategory()->getIsChildPrices(
        ) : $isChildPrices = $roomType->getIsChildPrices();
        $useCategories ? $isIndividualAdditionalPrices = $roomType->getCategory()->getIsIndividualAdditionalPrices(
        ) : $isIndividualAdditionalPrices = $roomType->getIsIndividualAdditionalPrices();
        $endPlus = clone $end;
        $endPlus->modify('+1 day');

        if ($this->manager->useCategories) {
            if (!$roomType->getCategory()) {
                return false;
            }
            $roomTypeId = $roomType->getCategory()->getId();
        } else {
            $roomTypeId = $roomType->getId();
        }
        if ($tariff->getParent() && $tariff->getChildOptions()->isInheritPrices()) {
            $tariff = $tariff->getParent();
        }
        $tariffId = $tariff->getId();
        $duration = (int) $end->diff($begin)->format('%a') + 1;
        $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')
            ->fetch($begin, $end, $hotel, [$roomTypeId], [$tariffId], true, $this->manager->useCategories, $memcached);

        if (!$tariff->getIsDefault()) {
            $defaultTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel);
            if (!$defaultTariff) {
                return false;
            }
            $defaultPriceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')
                ->fetch(
                    $begin,
                    $end,
                    $hotel,
                    [$roomTypeId],
                    [$defaultTariff->getId()],
                    true,
                    $this->manager->useCategories,
                    $memcached
                );

        } else {
            $defaultPriceCaches = $priceCaches;
            $defaultTariff = $tariff;
        }


        $mergingTariffsPrices = [];
        foreach ($this->mergingTariffs as $mergingTariff) {
            if ($mergingTariff->getParent() && $mergingTariff->getChildOptions()->isInheritPrices()) {
                $ids = [$mergingTariff->getParent()->getId()];
            } else {
                $ids = [$mergingTariff->getId()];
            }
            $mergingTariff = $this->dm->getRepository('MBHPriceBundle:PriceCache')
                ->fetch(
                    $begin,
                    $end,
                    $hotel,
                    [$roomTypeId],
                    $ids,
                    true,
                    $this->manager->useCategories,
                    $memcached
                );

            if ($mergingTariff) {
                $mergingTariffsPrices += $mergingTariff;
            }
        }

        if (!isset($priceCaches[$roomTypeId][$tariffId])) {
            return false;
        }

        $caches = [];
        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $endPlus) as $cacheDay) {
            $cacheDayStr = $cacheDay->format('d.m.Y');

            if (isset($priceCaches[$roomTypeId][$tariffId][$cacheDayStr])) {
                $caches[$cacheDayStr] = $priceCaches[$roomTypeId][$tariffId][$cacheDayStr];
            } else {
                foreach ($this->mergingTariffs as $mergingTariff) {
                    if (isset($mergingTariffsPrices[$roomTypeId][$mergingTariff->getId()][$cacheDayStr])) {
                        $caches[$cacheDayStr] = $mergingTariffsPrices[$roomTypeId][$mergingTariff->getId(
                        )][$cacheDayStr];
                        break;
                    }
                }
            }

            if (empty($caches[$cacheDayStr]) && isset(
                    $defaultPriceCaches[$roomTypeId][$defaultTariff->getId()][$cacheDayStr]
                )
            ) {
                $caches[$cacheDayStr] = $defaultPriceCaches[$roomTypeId][$defaultTariff->getId()][$cacheDayStr];
            }
        }

        if ($useDuration && (count($caches) != $duration)) {
            return false;
        }

        //places
        if ($adults == 0 & $children == 0) {
            $combinations = $roomType->getAdultsChildrenCombinations($useCategories);
        } else {
            $combinations = [0 => ['adults' => $adults, 'children' => $children, 'childrenAges' => $ages]];
        }

        foreach ($combinations as $combination) {

            $dayPrices = $packagePrices = [];
            $total = 0;
            /*Если один взорслый и более одного ребенка , то первый от 7 до 14 идет за взрослого удаляясь из childrenage */
            $tempResult = false;
            $asAdultAge = array_filter($ages, function ($age) use (&$tempResult){

                        if (!$tempResult) {
                            $tempResult = (bool)($age > 7);

                            return $tempResult;
                        }
                        return false;
                    });
            $childAsAdult = $adults == 1 && 2 <= count($ages) && $asAdultAge;
            if ($childAsAdult) {
                $adults ++;
                $ages = array_diff_key($ages, $asAdultAge);
            }

            $table = $this->getMagicTable($adults);
            $ageGroups = $this->getAgeGroups($ages);

            /**
             * @var PriceCache $cache
             */
            foreach ($caches as $day => $cache) {
                $dayPrice = 0;
                $bulkPrice = $cache->getPrice();
                $discountPercent = null;
                //count adults price
                for ($i = 0; $i < $adults; $i++) {
                    $discountPercent = $table['discount'][$i+1]??null;
                    if ($discountPercent) {
                        $discount = $bulkPrice * $discountPercent / 100;
                    } else {
                        $discount = 0;
                    }
                    $dayPrice += $bulkPrice - $discount;
                }
                //Условие когда 1+1+1 - ребенок старше 7 идет как взрослый
                //ребенок до 7 считается по таблице с двумя взрослыми.

                //count children price by ageGroups
                $discountPercent = null;
                foreach ($ageGroups as $groupName => $ages) {
                    foreach ($ages as $index => $age) {
                        $discountPercent = $table[(string)(($index+1).$groupName)]['discount'];
                        if ($discountPercent) {
                            $discount = $bulkPrice * $discountPercent / 100;
                        } else {
                            $discount = 0;
                        }
                        $dayPrice += $bulkPrice-$discount;
                    }

                }

                $total += $dayPrice;
                $packagePrices[] = $this->getPackagePrice($dayPrice, $cache->getDate(), $tariff, $roomType, $special);

//                $promoConditions = PromotionConditionFactory::checkConditions(
//                    $promotion, $duration, $combination['adults'], $combination['children']
//                );
//
//                if ($cache->getTariff()->getId() != $tariff->getId()) {
//                    $promoConditions = false;
//                }
//
//                $totalChildren = $combination['children'];
//                $totalAdults = $combination['adults'];
//
//                if ($promoConditions) {
//                    $totalChildren -= (int)$promotion->getFreeChildrenQuantity();
//                    $totalAdults -= (int)$promotion->getFreeAdultsQuantity();
//                    $totalAdults = $totalAdults >= 1 ? $totalAdults : 1;
//                    $totalChildren = $totalChildren >= 0 ? $totalChildren : 0;
//                    $childrenDiscount = $promotion->getChildrenDiscount();
//                }
//
//                $all = $totalAdults + $totalChildren;
//                $adds = $all - $places;
//
//                if ($all > $places) {
//
//                    if ($totalAdults >= $places) {
//                        $mainAdults = $places;
//                        $mainChildren = 0;
//                    } else {
//                        $mainAdults = $totalAdults;
//                        $mainChildren = $places - $totalAdults;
//                    }
//
//                    if ($adds > $totalChildren) {
//                        $addsChildren = $totalChildren;
//                        $addsAdults = $adds - $addsChildren;
//                    } else {
//                        $addsChildren = $adds;
//                        $addsAdults = 0;
//                    }
//                } else {
//                    $mainAdults = $totalAdults;
//                    $mainChildren = $totalChildren;
//                    $addsAdults = 0;
//                    $addsChildren = 0;
//                }
//
//                $dayPrice = 0;
//
//                if ($cache->getSinglePrice() !== null &&
//                    $all == 1 &&
//                    !$cache->getCategoryOrRoomType($this->manager->useCategories)->getIsHostel()
//                ) {
//                    $dayPrice += $cache->getSinglePrice();
//                } elseif ($cache->getIsPersonPrice()) {
//                    if ($isChildPrices && $cache->getChildPrice() !== null) {
//                        $childrenPrice = $mainChildren * $cache->getChildPrice();
//                    } else {
//                        $childrenPrice = $mainChildren * $cache->getPrice();
//                    }
//                    if ($promoConditions && $childrenDiscount) {
//                        $childrenPrice = $childrenPrice * (100 - $childrenDiscount) / 100;
//                    }
//                    $dayPrice += $mainAdults * $cache->getPrice() + $childrenPrice;
//                } else {
//                    $dayPrice += $cache->getPrice();
//                }
//
//                //calc adds
//                if ($addsChildren && $cache->getAdditionalChildrenPrice() === null) {
//                    continue 2;
//                }
//                if ($addsAdults && $cache->getAdditionalPrice() === null) {
//                    continue 2;
//                }
//
//                if ($isIndividualAdditionalPrices and ($addsChildren + $addsAdults) > 1) {
//                    $addsPrice = 0;
//                    $additionalCalc = function ($num, $prices, $price, $offset = 0) {
//                        $result = 0;
//                        for ($i = 0; $i < $num; $i++) {
//                            if (isset($prices[$i + $offset]) && $prices[$i + $offset] !== null) {
//                                $result += $prices[$i + $offset];
//                            } else {
//                                $result += $price;
//                            }
//                        }
//
//                        return $result;
//                    };
//
//                    $addsPrice += $additionalCalc($addsAdults, $cache->getAdditionalPrices(), $cache->getAdditionalPrice());
//                    $addsChildrenPrice = $additionalCalc($addsChildren, $cache->getAdditionalChildrenPrices(), $cache->getAdditionalChildrenPrice(), $addsAdults);
//
//                    if ($promoConditions && $childrenDiscount) {
//                        $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
//                    }
//                    $addsPrice += $addsChildrenPrice;
//                } else {
//                    $addsChildrenPrice = $addsChildren * $cache->getAdditionalChildrenPrice();
//
//                    if ($promoConditions && $childrenDiscount) {
//                        $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
//                    }
//
//                    $addsPrice = $addsAdults * $cache->getAdditionalPrice() + $addsChildrenPrice;
//                }
//
//                $dayPrice += $addsPrice;
//
//                // calc promotion discount
//                if ($promoConditions) {
//                    $dayPrice -= PromotionConditionFactory::calcDiscount($promotion, $dayPrice, true);
//                }
//
//                $packagePrice = $this->getPackagePrice($dayPrice, $cache->getDate(), $tariff, $roomType, $special);
//                $dayPrice = $packagePrice->getPrice();
//                $dayPrices[str_replace('.', '_', $day)] = $dayPrice;
//
//                if ($promoConditions) {
//                    $packagePrice->setPromotion($promotion);
//                }
//                $packagePrices[] = $packagePrice;
//                $total += $dayPrice;
            }

//            $promoConditions = PromotionConditionFactory::checkConditions(
//                $promotion, $duration, $combination['adults'], $combination['children']
//            );
//
//            if ($promoConditions) {
//                $total -= PromotionConditionFactory::calcDiscount($promotion, $total, false);
//            }

            $prices[$combination['adults'].'_'.$combination['children']] = [
                'adults' => $combination['adults'],
                'children' => $combination['children'],
                'total' => $this->getTotalPrice($total),
                'prices' => $dayPrices,
                'packagePrices' => $packagePrices,
            ];
        }

        return $prices;
    }


    private function getAgeGroups($ages): array
    {
        $groups = [];
        array_map(
            function ($age) use (&$groups) {
                if (0 <= $age && $age < 7) {
                    $groups['j'][] = $age;
                } elseif (7 <= $age && $age <= 15) {
                    $groups['t'][] = $age;
                }
            }, $ages);

        return $groups;
    }

    private function getMagicTable(int $adults)
    {

        $table = [
            '1adult' => [
                '1j' => ['discount' => 50],
                '2j' => ['discount' => 50],
                '3j' => ['discount' => 50],
                '4j' => ['discount' => 50],
                '5j' => ['discount' => 50],
                '1t' => ['discount' => 0],
                '2t' => ['discount' => 0],
                '3t' => ['discount' => 0],
                '4t' => ['discount' => 0],
                '5t' => ['discount' => 0],
                'discount' => null,
            ],
            '2adult' => [
                '1j' => ['discount' => 100],
                '2j' => ['discount' => 50],
                '3j' => ['discount' => 50],
                '4j' => ['discount' => 50],
                '5j' => ['discount' => 50],
                '1t' => ['discount' => 30],
                '2t' => ['discount' => 50],
                '3t' => ['discount' => 50],
                '4t' => ['discount' => 50],
                '5t' => ['discount' => 50],
                'discount' => null,
            ],
            '3adult' => [
                '1j' => ['discount' => 100],
                '2j' => ['discount' => 50],
                '3j' => ['discount' => 50],
                '1t' => ['discount' => 30],
                '2t' => ['discount' => 50],
                '3t' => ['discount' => 50],
                'discount' => [
                    3 => 20
                ],
            ],
            '4adult' => [
                '1j' => ['discount' => 100],
                '2j' => ['discount' => 50],
                '1t' => ['discount' => 30],
                '2t' => ['discount' => 50],
                'discount' => [
                    3 => 20,
                    4 => 20
                ],
            ],
        ];

        return $table[$adults.'adult'];

    }

    protected function getTotalPrice($total)
    {
        return $total;
    }

}