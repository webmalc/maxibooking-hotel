<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\ClientBundle\Document\ClientConfig;

/**
 *  Search service
 */
class Search implements SearchInterface
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
     * @var \DateTime
     */
    public $now;

    /**
     * @var RoomTypeManager
     */
    private $manager;

    /**
     * @var ClientConfig;
     */
    private $config;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Cache
     */
    private $memcached;

    /**
     * @var ArrayCollection
     */
    private $hotels;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->now = new \DateTime('midnight');
        $this->manager = $container->get('mbh.hotel.room_type_manager');
        $this->config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $this->hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        $this->memcached = $this->container->get('mbh.cache');
    }

    /**
     * @param \MBH\Bundle\PackageBundle\Lib\SearchQuery $query
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult[]
     */
    public function search(SearchQuery $query)
    {

        $results = $groupedCaches = $deletedCaches = $cachesMin = $tariffMin = [];

        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_FORCE_BOOKING')) {
            $query->forceBooking = false;
        }
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
        $helper = $this->container->get('mbh.helper');

        //roomTypes
        if (empty($query->roomTypes)) {
            $query->roomTypes = [];
            foreach ($this->hotels as $hotel) {
                $query->roomTypes = array_merge($helper->toIds($hotel->getRoomTypes()), $query->roomTypes);
            }
            if (!empty($query->availableRoomTypes)) {
                $query->roomTypes = array_intersect($query->roomTypes, $query->availableRoomTypes);
            };

        } elseif ($this->manager->useCategories && !$query->forceRoomTypes) {
            $roomTypes = [];
            foreach ($query->roomTypes as $catId) {
                $cat = $this->dm->getRepository('MBHHotelBundle:RoomTypeCategory')->find($catId);
                if ($cat) {
                    $roomTypes = array_merge($helper->toIds($cat->getTypes()), $roomTypes);
                }
            }
            $query->roomTypes = count($roomTypes) ? $roomTypes : [0];
        }
        //roomCache with tariffs
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $query->begin, $end, $query->tariff ? $query->tariff->getHotel() : null,
            $query->roomTypes, false, false/*, $this->memcached*/
        );

        //group caches
        foreach ($roomCaches as $roomCache) {

            if ($roomCache->getTariff()) {
                // collect quotes
                try {
                    $quotesTariff = $query->tariff;
                    if ($quotesTariff && $quotesTariff->getParent() && $quotesTariff->getChildOptions()->isInheritRooms()) {
                        $quotesTariff = $quotesTariff->getParent();
                    }
                    if ((!empty($quotesTariff) && $roomCache->getTariff()->getId() == $quotesTariff->getId()) || (empty($query->tariff) && $roomCache->getTariff()->getIsDefault())) {
                        $groupedCaches['tariff'][$roomCache->getHotel()->getId()][$roomCache->getRoomType()->getId()][] = $roomCache;
                    }
                } catch (DocumentNotFoundException $e) {

                }
            } else {
                $skip = false;
                if (in_array($roomCache->getRoomType()->getId(), $query->excludeRoomTypes) && !empty($query->excludeBegin) && !empty($query->excludeEnd) && $roomCache->getDate() >= $query->excludeBegin && $roomCache->getDate() <= $query->excludeEnd) {
                    $skip = true;
                }

                if ($skip || ($roomCache->getLeftRooms() > 0 /*&& $roomCache->getRoomType()->getTotalPlaces() >= $query->getTotalPlaces()*/ && !$roomCache->getIsClosed())) {
                    $groupedCaches['room'][$roomCache->getHotel()->getId()][$roomCache->getRoomType()->getId()][] = $roomCache;
                }
            }
        }

        if (!isset($groupedCaches['room'])) {
            return $results;
        }

        //tariff dates
        if (!empty($query->tariff)) {

            if ($query->tariff->getBegin() && $query->tariff->getBegin() > $this->now) {
                return $results;
            }
            if ($query->tariff->getEnd() && $query->tariff->getEnd() < $this->now) {
                return $results;
            }
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

        if (!$query->forceBooking) {
            //restrictions
            $restrictionsTariff = $query->tariff;
            if ($restrictionsTariff && $restrictionsTariff->getParent() && $restrictionsTariff->getChildOptions()->isInheritRestrictions()) {
                $restrictionsTariff = $restrictionsTariff->getParent();
            }

            $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $query->begin, $query->end, null, $query->roomTypes, $restrictionsTariff ? [$restrictionsTariff->getId()] : [], false, $this->memcached
            );

            foreach ($restrictions as $restriction) {
                $delete = false;

                if ($restrictionsTariff && $restrictionsTariff->getId() != $restriction->getTariff()->getId()) {
                    continue;
                }
                if (!$restrictionsTariff && !$restriction->getTariff()->getIsDefault()) {
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

                //MaxGuest
                if ($restriction->getMaxGuest() && $query->getTotalPlaces() > $restriction->getMaxGuest() ) {
                    $delete = true;
                }
                //MinGuest
                if ($restriction->getMinGuest() && $restriction->getMinGuest() > $query->getTotalPlaces()) {
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

            $check = PromotionConditionFactory::checkConditions(
                $tariff, $duration, $query->adults, $query->children
            );

            if (!$check) {
                continue;
            }


            $adults = $query->adults;
            $children = $query->children;
            $infants = $query->infants;

            //filter infants
            if (!empty($query->childrenAges)) {
                foreach ($query->childrenAges as $age) {
                    if ($age <= $tariff->getInfantAge()) {
                        $children -= 1;
                        $infants += 1;
                    }
                }
            }

            //filter children
            if (!empty($query->childrenAges)) {
                foreach ($query->childrenAges as $age) {
                    if ($age > $tariff->getChildAge()) {
                        $children -= 1;
                        $adults += 1;
                    }
                }
            }

            foreach ($hotelArray as $roomTypeId => $caches) {

                $min = $cachesMin[$hotelId][$roomTypeId];

                if (isset($tariffMin[$hotelId][$roomTypeId]) && $tariffMin[$hotelId][$roomTypeId] < $min) {
                    $min = $tariffMin[$hotelId][$roomTypeId];
                }

                if ($caches[0]->getRoomType()->getTotalPlaces() < $adults + $children) {
                    continue;
                }

                $roomType = $caches[0]->getRoomType();
                $useCategories = $query->isOnline && $this->config && $this->config->getUseRoomTypeCategory();
                $result = new SearchResult();
                $tourists = $roomType->getAdultsChildrenCombination($adults, $children, $this->manager->useCategories);

                if ($query->accommodations) {
                    $accommodationRooms = $this->dm->getRepository('MBHHotelBundle:Room')->fetchAccommodationRooms(
                        $query->begin,
                        $query->end,
                        $roomType->getHotel(),
                        $roomType->getId(), null, null, false, $this->memcached
                    );
                    $result->setRooms($accommodationRooms);
                }

                $result->setBegin($query->begin)
                    ->setEnd($query->end)
                    ->setRoomType($roomType)
                    ->setTariff($tariff)
                    ->setRoomsCount($min)
                    ->setPackagesCount($caches[0]->getPackagesCount())
                    ->setAdults($tourists['adults'])
                    ->setChildren($tourists['children'])
                    ->setUseCategories($useCategories)
                    ->setForceBooking($query->forceBooking)
                    ->setInfants($infants)
                ;

                //promotion
                $promotion = $query->getPromotion();
                if ($promotion === null && $tariff->getDefaultPromotion()) {
                    $promotion = $tariff->getDefaultPromotion();
                }
                if (!$promotion) {
                    $promotion = null;
                }
                
                //prices
                $prices = $calc->calcPrices(
                    $roomType, $tariff, $query->begin, $end,
                    $tourists['adults'], $tourists['children'], $promotion, $this->manager->useCategories
                );

                if (!$prices || (($query->adults + $query->children) != 0 && !isset($prices[$tourists['adults'] . '_' . $tourists['children']]))) {
                    continue;
                }
                foreach ($prices as $price) {
                    $result->addPrice($price['total'], $price['adults'], $price['children'])
                        ->setPricesByDate($price['prices'], $price['adults'], $price['children'])
                        ->setPackagePrices($price['packagePrices'], $price['adults'], $price['children'])
                    ;
                }
                if (empty($result->getPrices())) {
                    continue;
                }

                //check windows

                $virtualResult = $this->setVirtualRoom(
                    $result, $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($result->getRoomType()->getHotel())
                );

                if (!$virtualResult) {
                    continue;
                }

                $roomTypeObjId = $result->getRoomTypeInterfaceObject()->getId() . '_' . $result->getTariff()->getId();

                if (isset($results[$roomTypeObjId])) {
                    $totalRooms = $result->getRoomsCount() + $results[$roomTypeObjId]->getRoomsCount();
                    $result->setRoomsCount($totalRooms);
                    $results[$roomTypeObjId]->setRoomsCount($totalRooms);
                }

                if (
                    !$result->isUseCategories() ||
                    !isset($results[$roomTypeObjId]) ||
                    $results[$roomTypeObjId]->getRoomType()->getTotalPlaces() > $result->getRoomType()->getTotalPlaces()
                ) {
                    $results[$roomTypeObjId] = $result;
                }
            }
        }
        return array_values($results);
    }

    /**
     * Sets virtual room to search result
     * @param SearchResult $result
     * @param Tariff $tariff
     * @return bool|SearchResult
     */
    public function setVirtualRoom($result, Tariff $tariff)
    {

        if ($result->getBegin() <= new \DateTime('midnight')) {
            return true;
        }

        if (!$this->config || !$this->config->getSearchWindows() || $result->getForceBooking()) {
            return true;
        }
        $roomType = $result->getRoomType();
        $begin = clone $result->getBegin();
        $end = clone $result->getEnd();
        $preferredRoom = null;
        $emptyRoom = null;
        $restriction = $this->dm->getRepository('MBHPriceBundle:Restriction')
            ->findOneByDate($begin, $roomType, $tariff, $this->memcached);

        if ($restriction && $restriction->getMinStayArrival()) {
            $begin->modify('-' . $restriction->getMinStayArrival() . ' days');
            $end->modify('+' . ($restriction->getMinStayArrival() - 1) . ' days');
        }

        $packages = $this->dm->getRepository('MBHPackageBundle:Package')
            ->fetchWithVirtualRooms($begin, $end, $roomType)
        ;

        $minRoomCache = $this->dm->getRepository('MBHPriceBundle:RoomCache')->getMinTotal(
            $begin, $end, $roomType, $tariff
        );

        if (!$minRoomCache) {
            $minRoomCache = $this->dm->getRepository('MBHPriceBundle:RoomCache')->getMinTotal(
                $begin, $end, $roomType
            );
        }

        $groupedPackages = [];
        foreach ($packages as $package) {
            $groupedPackages[$package->getVirtualRoom()->getId()][] = $package;
        }

        $rooms = $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetchQuery(null, [$result->getRoomType()->getId()], null, null, null, $minRoomCache)
            ->sort(['id' => 'asc', 'fullTitle' => 'desc']);

        foreach ($rooms->getQuery()->execute() as $room) {
            if (isset($groupedPackages[$room->getId()])) {
                foreach ($groupedPackages[$room->getId()] as $package) {

                    if ($package->getBegin() == $result->getEnd() || $package->getEnd() == $result->getBegin()) {
                        $preferredRoom = $room;
                    } elseif ($package->getBegin() == $end || $package->getEnd() == $begin) {
                        $preferredRoom = $room;
                    } else {
                        $preferredRoom = null;
                        break;
                    }
                }

                if ($preferredRoom) {
                    $result->setVirtualRoom($preferredRoom);

                    return $result;
                }

            } else {
                $emptyRoom = $room;
            }

        }

        if ($emptyRoom) {
            $result->setVirtualRoom($emptyRoom);

            return $result;
        }

        return false;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query)
    {
        $tariffs = $results = [];
        if (!empty($query->roomTypes)) {
            $roomTypes = $this->manager->getRooms(null, $query->roomTypes);
            foreach ($roomTypes as $roomType) {
                $tariffs = array_merge($tariffs, $this->dm->getRepository('MBHPriceBundle:Tariff')->fetch($roomType->getHotel())->toArray());
            }
        } else {
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetch(null, null, true);
        }

        foreach ($tariffs as $tariff) {

            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }
            if ($tariff->getHotel()->getDeletedAt()) {
                continue;
            }
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }

            if (!$query->isOnline && !$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel())) {
                continue;
            }

            if ($tariff->getBegin() && $tariff->getBegin() > $this->now) {
                continue;
            }
            if ($tariff->getEnd() && $tariff->getEnd() < $this->now) {
                continue;
            }
            if ($query->isOnline && !$tariff->getIsOnline()) {
                continue;
            }
            if ($tariff->getDeletedAt()) {
                continue;
            }
            if ($query->grouped) {
                $results[$tariff->getHotel()->getId()][] = $tariff;
            } else {
                $results[] = $tariff;
            }
        }

        return $results;
    }

}
