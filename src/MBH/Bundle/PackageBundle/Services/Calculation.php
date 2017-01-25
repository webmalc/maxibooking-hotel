<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\RoomCacheOverwrite;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  Calculation service
 */
class Calculation
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    /**
     * @var RoomTypeManager
     */
    private $manager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->manager = $container->get('mbh.hotel.room_type_manager');
        $this->mergingTariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')->getMergingTariffs();
    }

    /**
     * @param Order $order
     * @param CashDocument $newDoc
     * @param CashDocument $removeDoc
     * @return Order
     */
    public function setPaid(Order $order, CashDocument $newDoc = null, CashDocument $removeDoc = null)
    {
        $total = 0;
        $ids = [];

        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }
        $cashes = $order->getCashDocuments();

        if ($newDoc) {
            $cashes[] = $newDoc;
        }
        foreach ($cashes as $cash) {

            if (!$cash->getIsPaid() || in_array($cash->getId(), $ids)) {
                continue;
            }
            $ids[] = $cash->getId();

            if ($removeDoc && $removeDoc->getId() == $cash->getId()) {
                continue;
            }
            if ($cash->getOperation() == 'out') {
                $total -= $cash->getTotal();
            } elseif ($cash->getOperation() == 'in') {
                $total += $cash->getTotal();
            }
        }

        $order->setPaid($total);
        $order->checkPaid();

        return $order;
    }

    public function setServicesPrice(Package $package, PackageService $newDoc = null, PackageService $removeDoc = null)
    {
        $total = 0;

        $services = $package->getServices();
        if ($services instanceof \Traversable) {
            $services = iterator_to_array($services);
        }
        if ($newDoc) {
            $services[] = $newDoc;
        }
        foreach ($services as $service) {

            if (!empty($service->getDeletedAt())) {
                continue;
            }
            if ($removeDoc && $removeDoc->getId() == $service->getId()) {
                continue;
            }
            $total += $service->getTotal();
        }

        $package->setServicesPrice($total);

        return $package;
    }


    /**
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param int $adults
     * @param int $children
     * @param Promotion|null $promotion
     * @param bool $useCategories
     * @param bool $useDuration
     * @param Special|null $special
     * @return array
     */
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
        $useDuration = true
    )
    {
//        dump($special);exit;
        $prices = [];
        $memcached = $this->container->get('mbh.cache');
        $places = $roomType->getPlaces();
        $hotel = $roomType->getHotel();
        $useCategories ? $isChildPrices = $roomType->getCategory()->getIsChildPrices() : $isChildPrices = $roomType->getIsChildPrices();
        $useCategories ? $isIndividualAdditionalPrices = $roomType->getCategory()->getIsIndividualAdditionalPrices() : $isIndividualAdditionalPrices = $roomType->getIsIndividualAdditionalPrices();
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
        $duration = $end->diff($begin)->format('%a') + 1;
        $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')
            ->fetch($begin, $end, $hotel, [$roomTypeId], [$tariffId], true, $this->manager->useCategories, $memcached);

        if (!$tariff->getIsDefault()) {
            $defaultTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel);
            if (!$defaultTariff) {
                return false;
            }
            $defaultPriceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')
                ->fetch($begin, $end, $hotel, [$roomTypeId], [$defaultTariff->getId()], true, $this->manager->useCategories, $memcached);

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
                    $begin, $end, $hotel, [$roomTypeId],
                    $ids,
                    true, $this->manager->useCategories, $memcached
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
                        $caches[$cacheDayStr] = $mergingTariffsPrices[$roomTypeId][$mergingTariff->getId()][$cacheDayStr];
                        break;
                    }
                }
            }

            if (empty($caches[$cacheDayStr]) && isset($defaultPriceCaches[$roomTypeId][$defaultTariff->getId()][$cacheDayStr])) {
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
            $combinations = [0 => ['adults' => $adults, 'children' => $children]];
        }

        foreach ($combinations as $combination) {
            $total = 0;
            $dayPrices = $packagePrices = [];
            foreach ($caches as $day => $cache) {
                $promoConditions = PromotionConditionFactory::checkConditions(
                    $promotion, $duration, $combination['adults'], $combination['children']
                );

                if ($cache->getTariff()->getId() != $tariff->getId()) {
                    $promoConditions = false;
                }

                $totalChildren = $combination['children'];
                $totalAdults = $combination['adults'];

                if ($promoConditions) {
                    $totalChildren -= (int)$promotion->getFreeChildrenQuantity();
                    $totalAdults -= (int)$promotion->getFreeAdultsQuantity();
                    $totalAdults = $totalAdults >= 1 ? $totalAdults : 1;
                    $totalChildren = $totalChildren >= 0 ? $totalChildren : 0;
                    $childrenDiscount = $promotion->getChildrenDiscount();
                }

                $all = $totalAdults + $totalChildren;
                $adds = $all - $places;

                if ($all > $places) {

                    if ($totalAdults >= $places) {
                        $mainAdults = $places;
                        $mainChildren = 0;
                    } else {
                        $mainAdults = $totalAdults;
                        $mainChildren = $places - $totalAdults;
                    }

                    if ($adds > $totalChildren) {
                        $addsChildren = $totalChildren;
                        $addsAdults = $adds - $addsChildren;
                    } else {
                        $addsChildren = $adds;
                        $addsAdults = 0;
                    }
                } else {
                    $mainAdults = $totalAdults;
                    $mainChildren = $totalChildren;
                    $addsAdults = 0;
                    $addsChildren = 0;
                }

                $dayPrice = 0;

                if ($cache->getSinglePrice() !== null &&
                    $all == 1 &&
                    !$cache->getCategoryOrRoomType($this->manager->useCategories)->getIsHostel()
                ) {
                    $dayPrice += $cache->getSinglePrice();
                } elseif ($cache->getIsPersonPrice()) {
                    if ($isChildPrices && $cache->getChildPrice() !== null) {
                        $childrenPrice = $mainChildren * $cache->getChildPrice();
                    } else {
                        $childrenPrice = $mainChildren * $cache->getPrice();
                    }
                    if ($promoConditions && $childrenDiscount) {
                        $childrenPrice = $childrenPrice * (100 - $childrenDiscount) / 100;
                    }
                    $dayPrice += $mainAdults * $cache->getPrice() + $childrenPrice;
                } else {
                    $dayPrice += $cache->getPrice();
                }

                //calc adds
                if ($addsChildren && $cache->getAdditionalChildrenPrice() === null) {
                    continue 2;
                }
                if ($addsAdults && $cache->getAdditionalPrice() === null) {
                    continue 2;
                }

                if ($isIndividualAdditionalPrices and ($addsChildren + $addsAdults) > 1) {
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

                    $addsPrice += $additionalCalc($addsAdults, $cache->getAdditionalPrices(), $cache->getAdditionalPrice());
                    $addsChildrenPrice = $additionalCalc($addsChildren, $cache->getAdditionalChildrenPrices(), $cache->getAdditionalChildrenPrice(), $addsAdults);

                    if ($promoConditions && $childrenDiscount) {
                        $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
                    }
                    $addsPrice += $addsChildrenPrice;
                } else {
                    $addsChildrenPrice = $addsChildren * $cache->getAdditionalChildrenPrice();

                    if ($promoConditions && $childrenDiscount) {
                        $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
                    }

                    $addsPrice = $addsAdults * $cache->getAdditionalPrice() + $addsChildrenPrice;
                }

                $dayPrice += $addsPrice;

                // calc promotion discount
                if ($promoConditions) {
                    $dayPrice -= PromotionConditionFactory::calcDiscount($promotion, $dayPrice, true);
                }

                $packagePrice = $this->getPackagePrice($dayPrice, $cache->getDate(), $tariff, $special);
                $dayPrice = $packagePrice->getPrice();
                $dayPrices[str_replace('.', '_', $day)] = $dayPrice;

                if ($promoConditions) {
                    $packagePrice->setPromotion($promotion);
                }
                $packagePrices[] = $packagePrice;
                $total += $dayPrice;
            }

            $promoConditions = PromotionConditionFactory::checkConditions(
                $promotion, $duration, $combination['adults'], $combination['children']
            );

            if ($promoConditions) {
                $total -= PromotionConditionFactory::calcDiscount($promotion, $total, false);
            }

            $prices[$combination['adults'] . '_' . $combination['children']] = [
                'adults' => $combination['adults'],
                'children' => $combination['children'],
                'total' => $total,
                'prices' => $dayPrices,
                'packagePrices' => $packagePrices,
            ];
        }

        return $prices;
    }

    /**
     * @param $price
     * @param \DateTime $date
     * @param Tariff $tariff
     * @param Special|null $special
     * @return PackagePrice
     */
    public function getPackagePrice($price, \DateTime $date, Tariff $tariff, Special $special = null): PackagePrice
    {
        $packagePrice = new PackagePrice($date, $price > 0 ? $price : 0, $tariff);
        if ($special && $date >= $special->getBegin() && $date <= $special->getEnd()) {
            $price = $special->isIsPercent() ? $price - $price * $special->getDiscount() / 100 : $price - $special->getDiscount();
            $packagePrice->setPrice($price)->setSpecial($special);
        }
        return $packagePrice;
    }

}
