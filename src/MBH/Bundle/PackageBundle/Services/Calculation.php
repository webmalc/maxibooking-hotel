<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\RoomCacheOverwrite;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\CashBundle\Document\CashDocument;

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
     * @var string 
     */
    protected $console;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
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

        $this->dm->getFilterCollection()->enable('softdeleteable');
        $cashes = $order->getCashDocuments();

        if ($newDoc) {
            $cashes[] = $newDoc;
        }
        foreach ($cashes as $cash) {

            if (in_array($cash->getId(), $ids)) {
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

    public function overwritePrices($prices, RoomCacheOverwrite $overwrite = null)
    {
        if (!is_array($prices)) {
            return $prices;
        }

        $map = [
            'main' => 'price',
            'adults' => 'additionalAdultPrice',
            'children' => 'additionalChildPrice'
        ];

        foreach ($map as $key => $value) {
            if ($overwrite && $overwrite->getPrice($value) !== null && isset($prices[$key])) {
                $prices[$key] = $overwrite->getPrice($value);
            }
        }
        return $prices;
    }

    /**
     * Calculate day price
     * @param mixed $tariff
     * @param mixed $roomType
     * @param \DateTime $date
     * @param int $adults
     * @param int $children
     * @param \MBH\Bundle\PackageBundle\Document\RoomCacheOverwrite $overwrite
     * @return int|null
     */
    public function getDayPrice($tariff, $roomType, \DateTime $date, $adults, $children, RoomCacheOverwrite $overwrite = null)
    {
        if (!$tariff instanceof Tariff) {
            $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->find($tariff);
            if (!$tariff) {
                return null;
            }
        }
        if (!$roomType instanceof RoomType) {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($roomType);
            if (!$roomType) {
                return null;
            }
        }
        
        $prices = $this->overwritePrices($this->getRoomPrices($tariff, $roomType, $date), $overwrite);

        $all = $adults + $children;
        $places = $roomType->getPlaces();
        $price = null;
        
        if($prices['main'] === null) {
            return null;
        }
        
        // perRoom calculation
        if($roomType->getCalculationType()  == 'perRoom') {
            $price = $prices['main'];
        }
        // customPrices calculation
        if($roomType->getCalculationType()  == 'customPrices') {
            ($all > $places) ? $main = $places : $main = $all;
            $price = $prices['main'] * $main;
        }
        // calc additional places
        if ($all > $places) {
            $adds =  $all - $places;
            
            if ($adds > $children) {
                $addsChildren = $children;
                $addsAdults = $adds - $addsChildren;
            } else {
                $addsChildren = $adds;
                $addsAdults = 0;
            }
            
            if($addsChildren && $prices['children'] === null) {
                return null;
            }
            if($addsAdults && $prices['adults'] === null) {
                return null;
            }
            
            $price += $addsAdults * $prices['adults'] + $addsChildren * $prices['children'];
        }
        
        return $price;
    }

    /**
     * Get RoomPrices from tariff
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param \DateTime $date
     * @return []
     */
    public function getRoomPrices(Tariff $tariff, RoomType $roomType, \DateTime $date)
    {
        // Get default tariff
        if (!$tariff->getIsDefault()) {

            $date->setTime(0, 0, 0);

            $defaultTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('s')
                    ->field('isDefault')->equals(true)
                    ->field('begin')->lte($date)
                    ->field('end')->gte($date)
                    ->field('hotel.id')->equals($roomType->getHotel()->getId())
                    ->limit(1)
                    ->getQuery()
                    ->getSingleResult()
            ;
            
            if (!$defaultTariff) {
                return null;
            }
                      
            // Rate tariff
            if ($tariff->getType() == 'rate') {
                $rate = $tariff->getRate()/100;
                return $this->getPricesFromTariff($defaultTariff, $roomType, $rate);
            }
            
            // Price tariff
            if ($tariff->getType() == 'price') {

                $prices = $this->getPricesFromTariff($tariff, $roomType);
                
                if (!$prices || $prices['main'] === null || $prices['adults'] === null || $prices['children'] === null) {
                    $defaultPrices = $this->getPricesFromTariff($defaultTariff, $roomType);

                    if ($defaultPrices) {
                        
                        if (!$prices) {
                           $prices = ['main' => null, 'adults' => null, 'children' => null];
                        }
                        
                        ($prices['main'] === null) ? $prices['main'] = $defaultPrices['main'] : $prices['main'];
                        ($prices['adults'] === null) ? $prices['adults'] = $defaultPrices['adults'] : $prices['adults'];
                        ($prices['children'] === null) ? $prices['children'] = $defaultPrices['children'] : $prices['children'];
                    }
                }
                return $prices;
            }
        }

        // Default tariff
        return $this->getPricesFromTariff($tariff, $roomType);
    }
    
    /**
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param type $rate
     * @return []
     */
    private function getPricesFromTariff(Tariff $tariff, RoomType $roomType, $rate = 1)
    {
        foreach ($tariff->getRoomPrices() as $price) {
            
            
            if ($price->getRoomType()->getId() != $roomType->getId()) {
                continue;
            }
            
            ($price->getPrice() === null) ? $main = null : $main = (int) round($price->getPrice() * $rate);
            ($price->getAdditionalAdultPrice() === null) ? $adults = null : $adults = (int) round($price->getAdditionalAdultPrice() * $rate);
            ($price->getAdditionalChildPrice() === null) ? $children = null : $children = (int) round($price->getAdditionalChildPrice() * $rate);

            return [
                'main' => $main,
                'adults' => $adults,
                'children' => $children
            ];
        }
        
        return null;
    }

}
