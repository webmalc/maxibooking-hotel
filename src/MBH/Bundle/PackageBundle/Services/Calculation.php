<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\PackageBundle\Document\Order as OrderDoc;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\RoomCacheOverwrite;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;

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
    }

    /**
     * @param OrderDoc $order
     * @param CashDocument $newDoc
     * @param CashDocument $removeDoc
     * @return OrderDoc
     */
    public function setPaid(OrderDoc $order, CashDocument $newDoc = null, CashDocument $removeDoc = null)
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

            if($removeDoc && $removeDoc->getId() == $cash->getId()) {
                continue;
            }
            if ($cash->getOperation() == 'out') {
                $total -= $cash->getTotal();
            } elseif($cash->getOperation() == 'in') {
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
     * @return array|bool
     */
    public function calcPrices(RoomType $roomType, Tariff $tariff, \DateTime $begin, \DateTime $end, $adults = 0, $children = 0)
    {
        $prices = [];
        $places = $roomType->getPlaces();
        $hotel = $roomType->getHotel();

        if ($this->manager->useCategories) {
            if (!$roomType->getCategory()) {
                return false;
            }
            $roomTypeId = $roomType->getCategory()->getId();
        } else {
            $roomTypeId = $roomType->getId();
        }

        $tariffId = $tariff->getId();
        $duration = $end->diff($begin)->format('%a') + 1;
        $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')
            ->fetch($begin, $end, $hotel, [$roomTypeId], [$tariffId], true, $this->manager->useCategories);

        if (!isset($priceCaches[$roomTypeId][$tariffId]) || count($priceCaches[$roomTypeId][$tariffId]) != $duration) {
            return false;
        }

        //places
        if ($adults == 0 & $children == 0) {
            $combinations = $roomType->getAdultsChildrenCombinations();
        } else {
            $combinations = [0 => ['adults' => $adults, 'children' => $children]];
        }

        foreach ($combinations as $combination) {
            $total = 0;
            $all = $combination['adults'] + $combination['children'];
            $adds = $all - $places;
            if ($all > $places) {

                if ($combination['adults'] >= $places) {
                    $mainAdults = $places;
                    $mainChildren = 0;
                } else {
                    $mainAdults = $combination['adults'];
                    $mainChildren = $places - $combination['adults'];
                }

                if ($adds > $combination['children']) {
                    $addsChildren = $combination['children'];
                    $addsAdults = $adds - $addsChildren;
                } else {
                    $addsChildren = $adds;
                    $addsAdults = 0;
                }
            } else {
                $mainAdults = $combination['adults'];
                $mainChildren = $combination['children'];
                $addsAdults = 0;
                $addsChildren = 0;
            }

            $dayPrices = [];
            foreach ($priceCaches[$roomTypeId][$tariffId] as $day => $cache) {
                $dayPrice = 0;

                if ($cache->getSinglePrice() !== null &&
                    $all == 1 &&
                    !$cache->getCategoryOrRoomType($this->manager->useCategories)->getIsHostel()
                ) {
                    $dayPrice += $cache->getSinglePrice();
                } elseif ($cache->getIsPersonPrice()) {
                    if ($roomType->getIsChildPrices() && $cache->getChildPrice() !== null) {
                        $dayPrice += $mainAdults * $cache->getPrice() + $mainChildren * $cache->getChildPrice();
                    } else {
                        $dayPrice += ($mainAdults + $mainChildren) * $cache->getPrice();
                    }
                } else {
                    $dayPrice += $cache->getPrice();
                }

                //calc adds
                if($addsChildren && $cache->getAdditionalChildrenPrice() === null) {
                    continue 2;
                }
                if($addsAdults && $cache->getAdditionalPrice() === null) {
                    continue 2;
                }

                if ($roomType->getIsIndividualAdditionalPrices() and ($addsChildren + $addsAdults) > 1) {
                    $addsPrice = 0;
                    $additionalCalc = function ($num, $prices, $price) {
                        $result = 0;
                        for ($i = 0; $i < $num; $i++) {
                            if (isset($prices[$i]) && $prices[$i] !== null) {
                                $result += $prices[$i];
                            } else {
                                $result += $price;
                            }
                        }

                        return $result;
                    };

                    $addsPrice += $additionalCalc($addsAdults, $cache->getAdditionalPrices(), $cache->getAdditionalPrice());
                    $addsPrice += $additionalCalc($addsChildren, $cache->getAdditionalChildrenPrices(), $cache->getAdditionalChildrenPrice());
                } else {
                    $addsPrice = $addsAdults * $cache->getAdditionalPrice() + $addsChildren * $cache->getAdditionalChildrenPrice();
                }

                $dayPrice += $addsPrice;
                $dayPrices[str_replace('.', '_', $day)] = $dayPrice;
                $total += $dayPrice;
            }

            $prices[$combination['adults'] . '_' . $combination['children']] = [
                'adults' => $combination['adults'],
                'children' => $combination['children'],
                'total' => $total,
                'prices' => $dayPrices
            ];
        }

        return $prices;
    }

}
