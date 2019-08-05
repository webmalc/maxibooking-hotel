<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\MongoDB\Query\Builder;
use Gedmo\Loggable\Document\LogEntry;
use Gedmo\Loggable\Document\Repository\LogEntryRepository;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PriceCacheRepositoryFilter;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  Calculation service
 */
class Calculation
{
    const ACCOUNTS_PAYABLE_CASH_SUM = 'kreditDebtCash';
    const ACCOUNTS_PAYABLE_CASHLESS_SUM = 'kreditDebtCashless';
    const NOT_PAID_RECEIVABLES_SUM = 'debitNotPaid';
    const PARTLY_PAID_RECEIVABLES_SUM = 'debitPartlyPaid';
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    protected $dm;

    /**
     * @var RoomTypeManager
     */
    private $manager;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    private $helper;

    /**
     * @var PriceCacheRepositoryFilter
     */
    private $priceCacheFilter;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb.odm.default_document_manager');
        $this->manager = $container->get('mbh.hotel.room_type_manager');
        $this->helper = $container->get('mbh.helper');
        $this->priceCacheFilter = $container->get('mbh.price_cache_repository_filter');
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
            if (!$cash->getIsPaid() || !empty($cash->getDeletedAt()) || in_array($cash->getId(), $ids)) {
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

        if ($package->getId()) {
            $services = $this->dm
                ->getRepository('MBHPackageBundle:PackageService')
                ->findBy(['package.id' => $package->getId()]);
        } else {
            $services = $package->getServices();
        }

        if ($services instanceof \Traversable) {
            $services = iterator_to_array($services);
        }

        if ($newDoc && !in_array($newDoc, $services)) {
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
     * @param Special|null $special
     * @param bool $useDuration
     * @param bool $useMemcached
     * @return array|bool
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
    ) {
        $originTariff = $tariff;
        $prices = [];
        $memcached = $this->container->get('mbh.cache');
        $places = $roomType->getPlaces();
        $hotel = $roomType->getHotel();
        $useCategories ? $isChildPrices = $roomType->getCategory()->getIsChildPrices() : $isChildPrices = $roomType->getIsChildPrices();
        $useCategories ? $isIndividualAdditionalPrices = $roomType->getCategory()->getIsIndividualAdditionalPrices() : $isIndividualAdditionalPrices = $roomType->getIsIndividualAdditionalPrices();
        $endPlus = clone $end;
        $endPlus->modify('+1 day');

        if ($this->manager->getIsUseCategories()) {
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
        $priceCachesCallback = function () use ($begin, $end, $hotel, $roomTypeId, $tariffId, $memcached) {
            $filtered = $this->priceCacheFilter->filterFetch(
                $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                    $begin,
                    $end,
                    $hotel,
                    [$roomTypeId],
                    [$tariffId],
                    true,
                    $this->manager->getIsUseCategories(),
                    $memcached
                )
            );
            return $filtered;
        };

        $priceCaches = $this->helper->getFilteredResult($this->dm, $priceCachesCallback);

        if (!isset($priceCaches[$roomTypeId][$tariffId])) {
            return false;
        }

        if (!$tariff->getIsDefault()) {
            $defaultTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel, null, $memcached);
            if (!$defaultTariff) {
                return false;
            }
            $defaultPriceCachesCallback = function () use ($begin, $end, $hotel, $roomTypeId, $defaultTariff, $memcached) {
                $filtered = $this->priceCacheFilter->filterFetch(
                    $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                        $begin,
                        $end,
                        $hotel,
                        [$roomTypeId],
                        [$defaultTariff->getId()],
                        true,
                        $this->manager->getIsUseCategories(),
                        $memcached
                    )
                );
                return $filtered;
            };
            $defaultPriceCaches = $this->helper->getFilteredResult($this->dm, $defaultPriceCachesCallback);
        } else {
            $defaultPriceCaches = $priceCaches;
            $defaultTariff = $tariff;
        }

        $mergingTariffsPrices = [];
        if ($tariff->getMergingTariff()) {
            if ($tariff->getMergingTariff()->getParent() && $tariff->getMergingTariff()->getChildOptions()->isInheritPrices()) {
                $ids = [$tariff->getMergingTariff()->getParent()->getId()];
            } else {
                $ids = [$tariff->getMergingTariff()->getId()];
            }

            $mergingTariffCallback = function () use ($begin, $end, $hotel, $roomTypeId, $ids, $memcached) {
                $filtered = $this->priceCacheFilter->filterFetch(
                    $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                        $begin,
                        $end,
                        $hotel,
                        [$roomTypeId],
                        $ids,
                        true,
                        $this->manager->getIsUseCategories(),
                        $memcached
                    )
                );
                return $filtered;
            };
            $mergingTariff = $this->helper->getFilteredResult($this->dm, $mergingTariffCallback);

            if ($mergingTariff) {
                $mergingTariffsPrices += $mergingTariff;
            }
        }

        $caches = [];
        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $endPlus) as $cacheDay) {
            $cacheDayStr = $cacheDay->format('d.m.Y');

            if (isset($priceCaches[$roomTypeId][$tariffId][$cacheDayStr])) {
                $caches[$cacheDayStr] = $priceCaches[$roomTypeId][$tariffId][$cacheDayStr];
            } elseif ($tariff->getMergingTariff()
                && isset($mergingTariffsPrices[$roomTypeId][$tariff->getMergingTariff()->getId()][$cacheDayStr])) {
                $caches[$cacheDayStr] = $mergingTariffsPrices[$roomTypeId][$tariff->getMergingTariff()->getId()][$cacheDayStr];
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
                    $promotion,
                    $duration,
                    $combination['adults'],
                    $combination['children']
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
                    !$cache->getCategoryOrRoomType($this->manager->getIsUseCategories())->getIsHostel()
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

                //workaround solution caused by doctrine bug
                if ($cache->getTariff()->getId() === $originTariff) {
                    $packagePriceTariff = $originTariff;
                } else {
                    $packagePriceTariff = $this->dm->find('MBHPriceBundle:Tariff', $cache->getTariff()->getId());
                }
                $packagePrice = $this->getPackagePrice($dayPrice, $cache->getDate(), $packagePriceTariff, $roomType, $special);
                $dayPrice = $packagePrice->getPrice();
                $dayPrices[str_replace('.', '_', $day)] = $dayPrice;

                if ($promoConditions) {
                    $packagePrice->setPromotion($promotion);
                }
                $packagePrices[] = $packagePrice;
                $total += $dayPrice;
            }

            $promoConditions = PromotionConditionFactory::checkConditions(
                $promotion,
                $duration,
                $combination['adults'],
                $combination['children']
            );

            if ($promoConditions) {
                $total -= PromotionConditionFactory::calcDiscount($promotion, $total, false);
            }

            $prices[$combination['adults'] . '_' . $combination['children']] = [
                'adults' => $combination['adults'],
                'children' => $combination['children'],
                'total' => $this->getTotalPrice($total),
                'prices' => $dayPrices,
                'packagePrices' => $packagePrices,
            ];
        }

        return $prices;
    }

    /**
     * Returns raw total price or rounded if method overriden.
     * @param $total
     * @return mixed
     */
    protected function getTotalPrice($total)
    {
        return $total;
    }

    /**
     * @param $price
     * @param Tariff $tariff
     * @return float|int
     */
    public function getPriceWithTariffPromotionDiscount(float $price, Tariff $tariff)
    {
        $tariffPromotion = $tariff->getDefaultPromotion();
        if (empty($tariffPromotion)) {
            return $price;
        }

        return $tariffPromotion->getIsPercentDiscount()
            ? $price * (100 - $tariffPromotion->getDiscount()) / 100
            : $price - $tariffPromotion->getDiscount();
    }

    /**
     * @param $price
     * @param \DateTime $date
     * @param Tariff $tariff
     * @param RoomType $roomType
     * @param Special|null $special
     * @return PackagePrice
     */
    public function getPackagePrice($price, \DateTime $date, Tariff $tariff, RoomType $roomType, Special $special = null): PackagePrice
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

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel[] $hotels
     * @param $calculationBegin
     * @param $calculationEnd
     * @return array
     */
    public function getDebtsByDays(
        \DateTime $begin,
        \DateTime $end,
        array $hotels = [],
        \DateTime $calculationBegin,
        \DateTime $calculationEnd
    ) {
        $relatedRoomTypesIds = [];
        foreach ($hotels as $hotel) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                $relatedRoomTypesIds[] = $roomType->getId();
            }
        }

        /** @var Builder $packagesInCalcPeriodQB */
        $packagesInCalcPeriodQB = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder();
        $ordersIds = $packagesInCalcPeriodQB
            ->field('roomType.id')->in($relatedRoomTypesIds)
            ->field('begin')->gte($calculationBegin)
            ->field('end')->lte($calculationEnd)
            ->count()
            ->distinct('order.id')
            ->getQuery()
            ->execute()
            ->toArray();
        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->getUnpaidOrOverpaidOnDate($begin, $ordersIds)
            ->toArray();

        $ordersByIds = $this->helper->sortByValue($orders);
        $ordersIds = array_keys($ordersByIds);

        /** @var CashDocument[] $cashDocs */
        $cashDocs = $this->dm
            ->getRepository('MBHCashBundle:CashDocument')
            ->createQueryBuilder()
            ->field('isPaid')->equals(true)
            ->field('order.id')->in($ordersIds)
            ->getQuery()
            ->execute()
            ->toArray();

        /**
         * @param CashDocument|Package $document
         * @return mixed
         */
        $getOrderIdCallback = function ($document) {
            return $document->getOrder()->getId();
        };
        $cashDocsByOrderId = $this->helper->sortByValueByCallback($cashDocs, $getOrderIdCallback, true);

        /** @var Builder $packagesQB */
        $packagesQB = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder();

        /** @var Package[] $packages */
        $packages = $packagesQB
            ->field('order.id')->in($ordersIds)
            ->field('roomType.id')->in($relatedRoomTypesIds)
            ->getQuery()
            ->execute()
            ->toArray();

        $packagesByOrderIds = $this->helper->sortByValueByCallback($packages, $getOrderIdCallback, true);
        $packagesPricesByDates = $this->calcDailyPackagePrices($packages, $begin, $end);

        $periodEnd = (clone $end)->add(new \DateInterval('P1D'));
        $period = new \DatePeriod($begin, new \DateInterval('P1D'), $periodEnd);

        $initialHotelData = [];
        /** @var \DateTime $date */
        foreach ($period as $date) {
            $dateString = $date->format('d.m.Y');
            foreach ($hotels as $hotel) {
                $initialHotelData[$dateString][$hotel->getId()] = 0;
            }
        }

        $kreditDebtCash = $kreditDebtCashless = $debitPartlyPaid = $debitNotPaid = $initialHotelData;

        /** @var \DateTime $date */
        foreach ($period as $date) {
            $dateString = $date->format('d.m.Y');
            $dateToCompare = (clone $date)->add(new \DateInterval('P1D'));
            /** @var Order $order */
            foreach ($ordersByIds as $orderId => $order) {
                if ($order->getCreatedAt() > $dateToCompare) {
                    continue;
                }
                /** @var CashDocument[] $cashDocuments */
                $cashDocuments = isset($cashDocsByOrderId[$orderId]) ? $cashDocsByOrderId[$orderId] : [];
                $packages = isset($packagesByOrderIds[$orderId]) ? $packagesByOrderIds[$orderId] : [];

                $cashIncoming = 0;
                $cashlessIncoming = 0;
                $refunds = 0;
                foreach ($cashDocuments as $cashDocument) {
                    if ($cashDocument->getPaidDate() < $dateToCompare) {
                        if ($cashDocument->getOperation() == 'in') {
                            $cashDocument->getMethod() == 'cash'
                                ? $cashIncoming += $cashDocument->getTotal()
                                : $cashlessIncoming += $cashDocument->getTotal();
                        } elseif ($cashDocument->getOperation() == 'out') {
                            $refunds += $cashDocument->getTotal();
                        }
                    }
                }

                list($priceOfAllOrderPackages, $priceOfNotDeletedPackages)
                    = $this->getOrderPackagesPrices($packages, $packagesPricesByDates, $date);

                $packagesPricesOnDate = isset($packagesPricesByDates[$dateString]) ? $packagesPricesByDates[$dateString] : [];
                $priceFractionsByHotels
                    = $this->getOrderPriceFractionsByHotels($packages, $hotels, $priceOfNotDeletedPackages, $priceOfAllOrderPackages, $packagesPricesOnDate);

                $incomingSum = $cashIncoming + $cashlessIncoming;
                $sumBalance = $incomingSum - $refunds;

                $isOrderNotPaid = $incomingSum == 0;
                $isOrderPartlyPaid = !$isOrderNotPaid && $sumBalance < $priceOfNotDeletedPackages;

                if ($isOrderNotPaid || $isOrderPartlyPaid) {
                    $notPaidSum = $priceOfNotDeletedPackages - $sumBalance;
                    foreach ($hotels as $hotel) {
                        $hotelId = $hotel->getId();
                        $priceFraction = $priceFractionsByHotels[$hotelId]['notDeleted'];
                        $notPaidHotelSum = $notPaidSum * $priceFraction;
                        if ($isOrderNotPaid) {
                            $debitNotPaid[$dateString][$hotelId] += $notPaidHotelSum;
                        } else {
                            $debitPartlyPaid[$dateString][$hotelId] += $notPaidHotelSum;
                        }
                    }
                } elseif ($sumBalance > $priceOfNotDeletedPackages) {
                    $cashFraction = $cashIncoming / $incomingSum;
                    $cashlessFraction = $cashlessIncoming / $incomingSum;
                    $overPaidSum = $sumBalance - $priceOfNotDeletedPackages;
                    foreach ($hotels as $hotel) {
                        $hotelId = $hotel->getId();
                        $priceFraction = $priceFractionsByHotels[$hotelId]['all'];
                        $overPaidHotelPrice = $overPaidSum * $priceFraction;

                        $kreditDebtCash[$dateString][$hotelId] += $overPaidHotelPrice * $cashFraction;
                        $kreditDebtCashless[$dateString][$hotelId] += $overPaidHotelPrice * $cashlessFraction;
                    }
                }
            }
        }

        return [
            self::ACCOUNTS_PAYABLE_CASH_SUM => $kreditDebtCash,
            self::ACCOUNTS_PAYABLE_CASHLESS_SUM => $kreditDebtCashless,
            self::NOT_PAID_RECEIVABLES_SUM => $debitNotPaid,
            self::PARTLY_PAID_RECEIVABLES_SUM => $debitPartlyPaid
        ];
    }

    /**
     * @param Package[] $packages
     * @param $packagePricesByDates
     * @param \DateTime $date
     * @return array
     */
    private function getOrderPackagesPrices($packages, $packagePricesByDates, $date)
    {
        $priceOfAllOrderPackages = 0;
        $priceOfNotDeletedPackages = 0;
        $dateString = $date->format('d.m.Y');
        foreach ($packages as $package) {
            if (isset($packagePricesByDates[$dateString][$package->getId()])) {
                $datePackagePrice = $packagePricesByDates[$dateString][$package->getId()];
                $priceOfAllOrderPackages += $datePackagePrice;
                if (empty($package->getDeletedAt()) || $package->getDeletedAt() > $date) {
                    $priceOfNotDeletedPackages += $datePackagePrice;
                }
            }
        }

        return [$priceOfAllOrderPackages, $priceOfNotDeletedPackages];
    }

    /**
     * @param Package[] $packages
     * @param Hotel[] $hotels
     * @param $priceOfNotDeletedPackages
     * @param $priceOfAllOrderPackages
     * @param $packagePricesOnDate
     * @return array
     */
    private function getOrderPriceFractionsByHotels($packages, $hotels, $priceOfNotDeletedPackages, $priceOfAllOrderPackages, $packagePricesOnDate)
    {
        $hotelsPriceFractions = [];
        foreach ($hotels as $hotel) {
            $hotelsPriceFractions[$hotel->getId()] = ['all' => 0, 'notDeleted' => 0];
        }

        /** @var Package $package */
        foreach ($packages as $package) {
            if (isset($packagePricesOnDate[$package->getId()])) {
                $packagePrice = $packagePricesOnDate[$package->getId()];
                $hotelId = $package->getHotel()->getId();
                $priceFractionFromNotDeleted = $priceOfNotDeletedPackages != 0
                    ? $packagePrice / $priceOfNotDeletedPackages
                    : 0;
                $priceFractionFromAllPackagesPrice = $priceOfAllOrderPackages != 0
                    ? $packagePrice / $priceOfAllOrderPackages
                    : 0;

                $hotelsPriceFractions[$hotelId]['all'] += $priceFractionFromAllPackagesPrice;
                $hotelsPriceFractions[$hotelId]['notDeleted'] += $priceFractionFromNotDeleted;
            }
        }

        return $hotelsPriceFractions;
    }

    /**
     * @param Package[] $packages
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param bool $asCalculatedPrice
     * @return array
     */
    public function calcDailyPackagePrices($packages, \DateTime $begin, \DateTime $end, $asCalculatedPrice = true)
    {
        $earliestCreationDate = null;
        foreach ($packages as $package) {
            if (is_null($earliestCreationDate) || $package->getCreatedAt() < $earliestCreationDate) {
                $earliestCreationDate = $package->getCreatedAt();
            }
        }

        /** @var LogEntryRepository $logEntryRepo */
        $logEntryRepo = $this->dm->getRepository('GedmoLoggable:LogEntry');
        $packageIds = $this->helper->toIds($packages);

        /** @var LogEntry[] $packagesRawLogs */
        $packagesRawLogs = $logEntryRepo
            ->createQueryBuilder()
            ->field('objectId')->in($packageIds)
            ->field('objectClass')->equals('MBH\Bundle\PackageBundle\Document\Package')
            ->field('loggedAt')->gte($earliestCreationDate)
            ->field('loggedAt')->lte($end)
            ->where('function() {return this.data && (this.data.price || this.data.totalOverwrite || this.data.servicesPrice || this.data.isPercentDiscount)}')
            ->hydrate(false)
            ->select(['loggedAt', 'objectId', 'data'])
            ->sort('loggedAt')
            ->getQuery()
            ->execute()
            ->toArray();

        $sortedLogData = [];
        foreach ($packagesRawLogs as $log) {
            /** @var \MongoDate $loggedAt */
            $loggedAt = $log['loggedAt'];
            $logDate = $loggedAt->toDateTime();
            $loggedDate = $logDate < $begin ? $begin : $logDate;
            $packageId = $log['objectId'];
            $dateString = $loggedDate->format('d.m.Y');
            $logData = $log['data'];
            !isset($logData['price']) ?: $sortedLogData[$packageId][$dateString]['price'] = $logData['price'];
            !isset($logData['totalOverwrite']) ?: $sortedLogData[$packageId][$dateString]['totalOverwrite'] = $logData['totalOverwrite'];
            !isset($logData['servicesPrice']) ?: $sortedLogData[$packageId][$dateString]['servicesPrice'] = $logData['servicesPrice'];
            !isset($logData['isPercentDiscount']) ?: $sortedLogData[$packageId][$dateString]['isPercentDiscount'] = $logData['isPercentDiscount'];
            !isset($logData['discount']) ?: $sortedLogData[$packageId][$dateString]['discount'] = $logData['discount'];
        }
        $getDailyValue = function ($dataName, $packageId, $dateString, $previousValue) use ($sortedLogData) {
            return isset($sortedLogData[$packageId][$dateString][$dataName])
                ? $sortedLogData[$packageId][$dateString][$dataName]
                : $previousValue[$dataName];
        };

        $prices = [];
        $currentDateString = (new \DateTime())->format('d.m.Y');
        foreach ($packages as $package) {
            $periodBegin = $begin < $package->getCreatedAt() ? $package->getCreatedAt() : $begin;
            $periodEnd = (clone $end)->add(new \DateInterval('P1D'));
            $previousValue = null;
            /** @var \DateTime $date */
            foreach (new \DatePeriod($periodBegin, new \DateInterval('P1D'), $periodEnd) as $date) {
                $dateString = $date->format('d.m.Y');
                $packageId = $package->getId();
                $isCurrentDate = $dateString === $currentDateString;

                if ($isCurrentDate || !isset($sortedLogData[$packageId])) {
                    $price = $package->getPackagePrice();
                    $totalOverWrite = $package->getTotalOverwrite();
                    $servicesPrice = $package->getServicesPrice();
                    $isPercentDiscount = $package->getIsPercentDiscount();
                    $discount = $package->getDiscount();
                } else {
                    $price = $getDailyValue('price', $packageId, $dateString, $previousValue);
                    $totalOverWrite = $getDailyValue('totalOverwrite', $packageId, $dateString, $previousValue);
                    $servicesPrice = $getDailyValue('servicesPrice', $packageId, $dateString, $previousValue);
                    $isPercentDiscount = $getDailyValue('isPercentDiscount', $packageId, $dateString, $previousValue);
                    $discount = $getDailyValue('discount', $packageId, $dateString, $previousValue);
                }

                $priceData = [
                    'price' => $price,
                    'totalOverwrite' => $totalOverWrite,
                    'servicesPrice' => $servicesPrice,
                    'isPercentDiscount' => $isPercentDiscount,
                    'discount' => $discount
                ];

                $prices[$dateString][$packageId] = $asCalculatedPrice
                    ? $this->calcPackagePrice($price, $totalOverWrite, $servicesPrice, $discount, $isPercentDiscount)
                    : $priceData;

                $previousValue = $priceData;
            }

        }

        return $prices;
    }

    /**
     * @param $price
     * @param $totalOverwrite
     * @param $servicesPrice
     * @param $discount
     * @param $isPercentDiscount
     * @return float|int
     */
    public function calcPackagePrice($price, $totalOverwrite, $servicesPrice, $discount, $isPercentDiscount)
    {
        if (!empty($totalOverwrite)) {
            $packagePrice = $totalOverwrite;
        } else {
            $packagePrice = $price;

            if (!empty($servicesPrice)) {
                $packagePrice += $servicesPrice;
            }
            if (!empty($discount)) {
                $packageDiscount = $isPercentDiscount === true
                    ? $price * $discount / 100 : $discount;
                $packagePrice -= $packageDiscount;
            }
        }

        return $packagePrice;
    }

    /**
     * @param array $roomTypes
     * @param array $tariffsIds
     * @param int $periodLengthInDays
     * @return array
     */
    public function getMinPricesForRooms(array $roomTypes, array $tariffsIds, int $periodLengthInDays)
    {
        $minPrices = [];
        foreach ($roomTypes as $roomType) {
            $begin = new \DateTime('midnight');
            $end = new \DateTime('midnight +' . $periodLengthInDays . 'days');
            /** @var PriceCache $priceCacheWithMinPrice */
            $priceCacheWithMinPrice = $this->dm
                ->getRepository('MBHPriceBundle:PriceCache')
                ->getWithMinPrice($roomType, $begin, $end, $tariffsIds);

            $priceCacheWithMinPrice = $this->priceCacheFilter->filterGetWithMinPrice($priceCacheWithMinPrice);

            if (is_null($priceCacheWithMinPrice)) {
                $minPrices[$roomType->getId()] = ['hasPrices' => false];
            } else {
                $minPriceDate = $priceCacheWithMinPrice->getDate();
                $tariff = $priceCacheWithMinPrice->getTariff();
                $priceForSingle = $this->calcPrices($roomType, $tariff, $minPriceDate, $minPriceDate, 1, 0, $tariff->getDefaultPromotion());
                if (!$priceForSingle || !isset($priceForSingle['1_0'])) {
                    $minPrices[$roomType->getId()] = ['hasPrices' => false];
                } else {
                    $minPrices[$roomType->getId()] = ['hasPrices' => true, 'price' => $priceForSingle['1_0']['total']];
                }
            }
        }

        return $minPrices;
    }
}
