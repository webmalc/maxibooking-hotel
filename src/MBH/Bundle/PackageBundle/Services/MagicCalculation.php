<?php

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
        $hotel = $roomType->getHotel();
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

        $priceCachesCallback = function () use ($begin, $end, $hotel, $roomTypeId, $tariffId, $memcached) {
            return $this->dm->getRepository('MBHPriceBundle:PriceCache')
                ->fetch($begin, $end, $hotel, [$roomTypeId], [$tariffId], false, $this->manager->useCategories, $memcached);
        };

        $priceCaches = $this->helper->getFilteredResult($this->dm, $priceCachesCallback);


        if ($useDuration && (\count($priceCaches) !== $duration)) {
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
                            $tempResult = $age > 7;

                            return $tempResult;
                        }
                        return false;
                    });
            $childAsAdult = $adults == 1 && \count($ages) >= 2 && $asAdultAge;
            if ($childAsAdult) {
                $adults ++;
                $ages = array_diff_key($ages, $asAdultAge);
            }

            $table = $this->getMagicTable($adults);
            $ageGroups = $this->getAgeGroups($ages);

            /**
             *
             * @var PriceCache $cache
             */
            foreach ($priceCaches as $cache) {
                $promotion = $promotion ?? $cache->getTariff()->getDefaultPromotion();
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
                foreach ($ageGroups as $groupName => $groupAges) {
                    foreach ($groupAges as $index => $age) {
                        $discountPercent = $table[($index+1).$groupName]['discount'];
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
                $dayPrices[$cache->getDate()->format('d_m_Y')] = $dayPrice;
            }


            $prices[$combination['adults'].'_'.$combination['children']] = [
                'adults' => $combination['adults'],
                'children' => $combination['children'],
                'childrenAges' => $ages,
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