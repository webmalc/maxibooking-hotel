<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

/**
 *  Search service
 */
class Search
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    /**
     * @param \MBH\Bundle\PackageBundle\Lib\SearchQuery $query
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult[]
     */
    public function search(SearchQuery $query)
    {
        $results = $groupedCaches = $deletedCaches = $cachesMin = $tariffMin = [];

        if (empty($query->end) || empty($query->begin) || $query->end <= $query->begin) {
            return $results;
        }

        $calc = $this->container->get('mbh.calculation');
        if (!empty($query->tariff) && !$query->tariff instanceof Tariff) {
            $query->tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->find($query->tariff);
        }
        // dates
        $end = clone $query->end;
        $end->modify('-1 day');
        $duration = $query->end->diff($query->begin)->format('%a');
        $today = new \DateTime('midnight');
        $beforeArrival = $today->diff($query->begin)->format('%a');

        //roomTypes
        if (empty($query->roomTypes)) {
            $query->roomTypes = [];
            $helper = $this->container->get('mbh.helper');
            foreach( $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
                $query->roomTypes = array_merge($helper->toIds($hotel->getRoomTypes()), $query->roomTypes);
            }
        }

        //roomCache with tariffs
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $query->begin, $end, null, $query->roomTypes
        );

        //group caches
        foreach ($roomCaches as $roomCache) {
            if ($roomCache->getTariff()) {

                if ((!empty($query->tariff) && $roomCache->getTariff()->getId() == $query->tariff->getId()) || (empty($query->tariff) && $roomCache->getTariff()->getIsDefault())) {
                    $groupedCaches['tariff'][$roomCache->getHotel()->getId()][$roomCache->getRoomType()->getId()][] = $roomCache;
                }
            } else {
                $skip = false;
                if (in_array($roomCache->getRoomType()->getId(), $query->excludeRoomTypes) && !empty($query->excludeBegin) && !empty($query->excludeEnd) && $roomCache->getDate() >= $query->excludeBegin && $roomCache->getDate() <= $query->excludeEnd) {
                    $skip = true;
                }

                if ($skip || ($roomCache->getLeftRooms() > 0 && $roomCache->getRoomType()->getTotalPlaces() >= $query->getTotalPlaces() && !$roomCache->getIsClosed())) {
                    $groupedCaches['room'][$roomCache->getHotel()->getId()][$roomCache->getRoomType()->getId()][] = $roomCache;
                }


            }
        }
        if (!isset($groupedCaches['room'])) {
            return $results;
        }

        //delete short cache chains
        foreach ($groupedCaches['room'] as $hotelId => $hotelA) {
            foreach ($hotelA as $roomTypeId => $caches) {

                $quotes = false;
                if (isset($groupedCaches['tariff'][$hotelId][$roomTypeId])) {
                    foreach ($groupedCaches['tariff'][$hotelId][$roomTypeId] as $tariffCache) {

                        if (!isset($tariffMin[$hotelId][$roomTypeId]) || $tariffMin[$hotelId][$roomTypeId] > $tariffCache->getLeftRooms()) {
                            $tariffMin[$hotelId][$roomTypeId] = $tariffCache->getLeftRooms();
                        }

                        $skip = false;
                        if (in_array($tariffCache->getRoomType()->getId(), $query->excludeRoomTypes) && !empty($query->excludeBegin) && !empty($query->excludeEnd) && $tariffCache->getDate() >= $query->excludeBegin && $tariffCache->getDate() <= $query->excludeEnd) {
                            $skip = true;
                        }

                        if ($tariffCache->getLeftRooms() <= 0 && !$skip) {
                            $quotes = true;
                        }
                    }
                }
                if (count($caches) == $duration && !$quotes) {
                    $deletedCaches[$hotelId][$roomTypeId] = $caches;
                }
            }
        }

        //restrictions
        $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $query->begin, $query->end, null, $query->roomTypes
        );

        foreach ($restrictions as $restriction) {
            $delete = false;

            if ($query->tariff && $query->tariff->getId() != $restriction->getTariff()->getId()) {
                continue;
            }
            if (!$query->tariff && !$restriction->getTariff()->getIsDefault()) {
                continue;
            }

            //ClosedOnDeparture
            if ($restriction->getDate()->format('d.m.Y') == $query->end->format('d.m.Y')) {
                if ($restriction->getClosedOnDeparture() && isset($deletedCaches[$restriction->getHotel()->getId()][$restriction->getRoomType()->getId()])) {
                    unset($deletedCaches[$restriction->getHotel()->getId()][$restriction->getRoomType()->getId()]);
                }
                continue;
            }
            //MinBeforeArrival
            if ($restriction->getMinBeforeArrival() && $beforeArrival < $restriction->getMinBeforeArrival()) {
                $delete = true;
            }
            //MaxBeforeArrival
            if ($restriction->getMaxBeforeArrival() && $beforeArrival > $restriction->getMaxBeforeArrival()) {
                $delete = true;
            }
            //MinStay
            if ($restriction->getMinStay() && $duration < $restriction->getMinStay()) {
                $delete = true;
            }
            //MinStay
            if ($restriction->getMaxStay() && $duration > $restriction->getMaxStay()) {
                $delete = true;
            }
            //MinStayArrival
            if ($restriction->getMinStayArrival() && $restriction->getDate()->format('d.m.Y') == $query->begin->format('d.m.Y') && $duration < $restriction->getMinStayArrival()) {
                $delete = true;
            }
            //MaxStayArrival
            if ($restriction->getMaxStayArrival() && $restriction->getDate()->format('d.m.Y') == $query->begin->format('d.m.Y') && $duration > $restriction->getMaxStayArrival()) {
                $delete = true;
            }
            //ClosedOnArrival
            if ($restriction->getClosedOnArrival() && $restriction->getDate()->format('d.m.Y') == $query->begin->format('d.m.Y')) {
                $delete = true;
            }
            //closed
            if ($restriction->getClosed()) {
                $delete = true;
            }

            //delete chain
            if ($delete && isset($deletedCaches[$restriction->getHotel()->getId()][$restriction->getRoomType()->getId()])) {
                unset($deletedCaches[$restriction->getHotel()->getId()][$restriction->getRoomType()->getId()]);
            }
        }

        //cacheMin
        foreach ($deletedCaches as $hotelId => $hotelArray) {
            foreach ($hotelArray as $roomTypeId => $caches) {
                foreach ($caches as $cache) {
                    if (!isset($cachesMin[$hotelId][$roomTypeId]) || $cachesMin[$hotelId][$roomTypeId] > $cache->getLeftRooms()) {
                        $cachesMin[$hotelId][$roomTypeId] = $cache->getLeftRooms();
                    }
                }
            }
        }

        //generate result
        foreach ($deletedCaches as $hotelId => $hotelArray) {

            //skip disabled tariff & hotel
            $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->find($hotelId);
            if (!$hotel || !$hotel->getIsEnabled()) {
                continue;
            }
            if (!empty($query->tariff)) {
                $tariff = $query->tariff;
            } else {
                $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel);
            }
            if (!$tariff || !$tariff->getIsEnabled()) {
                continue;
            }
            // check hotel permission
            if (!$query->isOnline && !$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
                continue;
            }

            foreach ($hotelArray as $roomTypeId => $caches) {

                $min = $cachesMin[$hotelId][$roomTypeId];

                if (isset($tariffMin[$hotelId][$roomTypeId]) && $tariffMin[$hotelId][$roomTypeId] < $min) {
                    $min = $tariffMin[$hotelId][$roomTypeId];
                }

                $roomType = $caches[0]->getRoomType();
                $result = new SearchResult();
                $tourists = $roomType->getAdultsChildrenCombination($query->adults, $query->children);
                $result->setBegin($query->begin)
                    ->setEnd($query->end)
                    ->setRoomType($roomType)
                    ->setTariff($tariff)
                    ->setRoomsCount($min)
                    ->setAdults($tourists['adults'])
                    ->setChildren($tourists['children'])
                ;

                //prices
                $prices = $calc->calcPrices($roomType, $tariff, $query->begin, $end, $tourists['adults'], $tourists['children']);

                if (!$prices || (($query->adults + $query->children) != 0 && !isset($prices[$tourists['adults'] . '_' . $tourists['children']]))) {
                    continue;
                }
                foreach ($prices as $price) {
                    $result->addPrice($price['total'], $price['adults'], $price['children'])
                           ->setPricesByDate($price['prices'], $price['adults'], $price['children'])
                    ;
                }
                if(empty($result->getPrices())) {
                    continue;
                }

                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query)
    {
        $tariffs = [];
        if (!empty($query->roomTypes)) {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch(null, $query->roomTypes);
            foreach ($roomTypes as $roomType) {
                $tariffs = array_merge($tariffs, $this->dm->getRepository('MBHPriceBundle:Tariff')->fetch($roomType->getHotel())->toArray());
            }
        } else {
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetch(null, null, true);
        }

        foreach($tariffs as $tariff) {
            if (!$query->isOnline && !$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel())) {
                continue;
            }
            $results[] = $tariff;
        }

        return $results;
    }

}
