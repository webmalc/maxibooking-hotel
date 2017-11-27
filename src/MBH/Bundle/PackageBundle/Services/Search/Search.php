<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param SearchQuery $query
     * @return SearchResult[]
     */
    public function search(SearchQuery $query)
    {
        $results = $groupedCaches = $deletedCaches = $cachesMin = $tariffMin = [];
        $token = $this->container->get('security.token_storage')->getToken();
        $session = $this->container->get('session');
        $trans = $this->container->get('translator');

        if (!$query->memcached) {
            $this->memcached = null;
        }

        if ($token && !$this->container->get('security.authorization_checker')->isGranted('ROLE_FORCE_BOOKING')) {
            $query->forceBooking = false;
        }
        if (empty($query->end) || empty($query->begin) || $query->end <= $query->begin) {
            return $results;
        }

        $calc = $this->container->get('mbh.calculation');
        if (!empty($query->tariff) && !$query->tariff instanceof Tariff) {
             $query->tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')
                 ->fetchById($query->tariff, $this->memcached);
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
            $query->begin,
            $end,
            $query->tariff ? $query->tariff->getHotel() : null,
            $query->roomTypes,
            false,
            false,
            $this->memcached
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
                $query->begin,
                $query->end,
                null,
                $query->roomTypes,
                $restrictionsTariff ? [$restrictionsTariff->getId()] : [],
                false,
                $this->memcached
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
                if ($restriction->getMaxGuest() && $query->getTotalPlaces() > $restriction->getMaxGuest()) {
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
                $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')
                    ->fetchBaseTariff($hotel, null, $this->memcached);
            }
            if (!$tariff || !$tariff->getIsEnabled()) {
                continue;
            }
            // check hotel permission
            if ($token && !$query->isOnline && !$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
                continue;
            }

            if (!$query->forceBooking) {
                $check = PromotionConditionFactory::checkConditions(
                    $tariff,
                    $duration,
                    $query->adults,
                    $query->children
                );

                if (!$check) {
                    continue;
                }
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

                $roomType = $caches[0]->getRoomType();
                if (method_exists($roomType, '__isInitialized') && !$roomType->__isInitialized()) {
                    $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($roomType->getId());
                }

                if ($caches[0]->getRoomType()->getTotalPlaces() < $adults + $children) {
                    continue;
                }
                $useCategories = $query->isOnline && $this->config && $this->config->getUseRoomTypeCategory();
                $result = new SearchResult();
                $tourists = $roomType->getAdultsChildrenCombination($adults, $children, $this->manager->useCategories);

                if ($query->accommodations) {
                    $accommodationRooms = $this->dm->getRepository('MBHHotelBundle:Room')->fetchAccommodationRooms(
                        $query->begin,
                        $query->end,
                        $roomType->getHotel(),
                        $roomType->getId(),
                        null,
                        null,
                        false,
                        $this->memcached
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

                $baseTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')
                    ->fetchBaseTariff($result->getRoomType()->getHotel(), null, $this->memcached);

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
                    $roomType,
                    $tariff,
                    $query->begin,
                    $end,
                    $tourists['adults'],
                    $tourists['children'],
                    $promotion,
                    $this->manager->useCategories,
                    $query->getSpecial()
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
                    $result,
                    $baseTariff,
                    $query->getExcludePackage()
                );

                if (!$virtualResult) {
                    $session->getFlashBag()->add(
                        'search',
                        $trans->trans(
                            'windows.search.error',
                            ['%room_type%' => $result->getRoomType()]
                        )
                    );
                    continue;
                }

                $roomTypeObjId = $result->getRoomTypeInterfaceObject()->getId() . '_' . $result->getTariff()->getId();

                if (isset($results[$roomTypeObjId])) {
                    $totalRooms = $result->getRoomsCount() + $results[$roomTypeObjId]->getRoomsCount();
                    $result->setRoomsCount($totalRooms);
                    $results[$roomTypeObjId]->setRoomsCount($totalRooms);
                }

                if (!$result->isUseCategories() ||
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
     * @param Package $package
     * @return bool|SearchResult
     */
    public function setVirtualRoom($result, Tariff $tariff, Package $package = null)
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
        $restriction = $this->dm->getRepository('MBHPriceBundle:Restriction')
            ->findOneByDate($begin, $roomType, $tariff, $this->memcached);

        if ($restriction && $restriction->getMinStayArrival()) {
            $begin->modify('-' . $restriction->getMinStayArrival() . ' days');
            $end->modify('+' . ($restriction->getMinStayArrival() - 1) . ' days');
        }

        $packages = $this->dm->getRepository('MBHPackageBundle:Package')
            ->fetchWithVirtualRooms($begin, $end, $roomType, false, $package, $this->memcached)
        ;

        $minRoomCache = $this->dm->getRepository('MBHPriceBundle:RoomCache')->getMinTotal(
            $begin,
            $end,
            $roomType,
            null,
            $this->memcached
        );

        $groupedPackages = [];
        foreach ($packages as $package) {
            $groupedPackages[$package->getVirtualRoom()->getId()][] = $package;
        }

        $rooms = $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetch(
                null,
                [$result->getRoomType()->getId()],
                null,
                null,
                null,
                $minRoomCache,
                false,
                true,
                ['roomType.id' => 'asc', 'fullTitle' => 'desc'],
                $this->memcached
            );

        $min = 0;
        $preferredRooms = new \SplObjectStorage();
        $emptyRooms =  new \SplObjectStorage();

        foreach ($rooms as $room) {
            if (isset($groupedPackages[$room->getId()])) {
                foreach ($groupedPackages[$room->getId()] as $package) {
                    if ($package->getBegin() == $result->getEnd() || $package->getEnd() == $result->getBegin()) {
                        $min += 1;
                        $preferredRooms->attach($room);
                    } elseif ($package->getBegin() == $end || $package->getEnd() == $begin) {
                        $min += 1;
                        $preferredRooms->attach($room);
                    } else {
                        $preferredRooms->detach($room);
                        break;
                    }
                }
            } else {
                $emptyRooms->attach($room);
                $min += 1;
            }
        }
        $result->setRoomsCount($emptyRooms->count() + $preferredRooms->count());

        $collection = $preferredRooms->count() ? $preferredRooms :  $emptyRooms;

        if ($collection->count()) {
            $collection->rewind();
            $room = $collection->current();

            $room = $this->dm->getRepository('MBHHotelBundle:Room')->find($room->getId());
            $result->setVirtualRoom($room);

            return $result;
        }
        return false;
    }

    public function searchSpecials(SearchQuery $query)
    {
        $filter = new SpecialFilter();
        $filter->setRemain(1)
            ->setDisplayFrom($query->begin)
            ->setDisplayTo($query->end);

        $specials = $this->dm->getRepository('MBHPriceBundle:Special')->getFiltered($filter);

        if (!$specials->count()) {
            $query->setSpecial(null);
        }

        return $specials;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query)
    {
        if ($query->limit === null && !count($query->roomTypes)) {
            $query->limit = $this->config->getSearchTariffs();
        }

        $results = $tariffs = [];
        if (!empty($query->roomTypes)) {
            $hotels = [];
            $roomTypes = $this->manager->getRooms(null, $query->roomTypes);
            foreach ($roomTypes as $roomType) {
                $hotels[$roomType->getHotel()->getId()] = $roomType->getHotel();
            }
        } else {
            $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        }

        foreach ($hotels as $hotel) {
            $hotelTariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->fetch($hotel, null, null, false);

            foreach ($hotelTariffs as $tariff) {
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
                if (!$tariff->getIsEnabled()) {
                    continue;
                }
                if ($tariff->getDeletedAt()) {
                    continue;
                }
                $tariffs[$tariff->getHotel()->getId()][] = $tariff;
            }
        }

        foreach ($tariffs as $key => $tariffsCollection) {
            usort($tariffsCollection, function ($a, $b) {
                if ($a->getPosition() == $b->getPosition()) {
                    if ($a->getIsDefault() && $b->getIsDefault()) {
                        return 0;
                    }
                    if (!$a->getIsDefault() && !$b->getIsDefault()) {
                        return 0;
                    }
                    if ($a->getIsDefault() && !$b->getIsDefault()) {
                        return -1;
                    }
                    if (!$a->getIsDefault() && $b->getIsDefault()) {
                        return 1;
                    }
                }
                return ($a->getPosition() > $b->getPosition()) ? -1 : 1;
            });
            if ($query->limit) {
                $tariffsCollection = array_slice($tariffsCollection, 0, $query->limit);
            }
            if ($query->grouped) {
                $results[$key] = $tariffsCollection;
            } else {
                $results = array_merge($results, $tariffsCollection);
            }
        }

        return $results;
    }
}
