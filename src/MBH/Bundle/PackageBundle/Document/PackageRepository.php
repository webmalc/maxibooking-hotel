<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\MongoDB\CursorInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PriceBundle\Document\Special;

/**
 * Class PackageRepository
 */
class PackageRepository extends DocumentRepository
{
    /**
     * @param Special $special
     * @param Package $exclude
     * @return Builder
     */
    public function getBuilderBySpecial(Special $special, Package $exclude = null): Builder
    {
        $qb = $this->createQueryBuilder()
            ->field('special')->references($special)
            ->field('deletedAt')->equals(null);

        if ($exclude) {
            $qb->field('id')->notEqual($exclude->getId());
        }

        return $qb;
    }

    /**
     * @param Special $special
     * @param Package $exclude
     * @return int
     */
    public function countBySpecial(Special $special, Package $exclude = null)
    {
        return $this->getBuilderBySpecial($special, $exclude)->getQuery()->count();
    }


    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType|null $roomType
     * @param bool $group
     * @param Package|null $exclude
     * @param Cache|null $cache
     * @return array|mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchWithVirtualRooms(
        \DateTime $begin,
        \DateTime $end,
        RoomType $roomType = null,
        bool $group = false,
        Package $exclude = null,
        Cache $cache = null
    )
    {
        if ($cache) {
            $cacheEntry = $cache->get('packages_with_virtual_rooms', func_get_args());
            if ($cacheEntry !== false) {
                return $cacheEntry;
            }
        }

        $qb = $this->getFetchWithVirtualRoomQB($begin, $end, $roomType, $exclude);
        $packages = $qb->getQuery()->execute();

        if ($group) {
            $result = [];
            /** @var Package $package */
            foreach ($packages as $package) {
                $roomType = $package->getRoomType();
                $result[$roomType->getId()][$package->getVirtualRoom()->getId()][] = $package;

            }

            if ($cache) {
                $cache->set($result, 'packages_with_virtual_rooms', func_get_args());
            }

            return $result;
        }

        if ($cache) {
            $cache->set(iterator_to_array($packages), 'packages_with_virtual_rooms', func_get_args());
        }

        return $packages;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param bool $sortByBegin
     * @param RoomType|null $roomType
     * @param null $inRooms
     * @param Package|null $exclude
     * @param int $limit
     * @param int $skip
     * @return mixed
     */
    public function extendedFetchWithVirtualRooms(
        \DateTime $begin,
        \DateTime $end,
        $sortByBegin = false,
        RoomType $roomType = null,
        $inRooms = null,
        Package $exclude = null,
        $limit = 0,
        $skip = 0
    ) {
        $qb = $this->getFetchWithVirtualRoomQB($begin, $end, $roomType, $exclude);
        if (!is_null($inRooms)) {
            $qb->field('virtualRoom.id')->in($inRooms);
        }
        if ($sortByBegin) {
            $qb->sort('begin', 'asc');
        }

        $qb->limit($limit)->skip($skip);

        return $qb->getQuery()->execute();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType|null $roomType
     * @param Package|null $exclude
     * @return Builder
     */
    public function getFetchWithVirtualRoomQB(
        \DateTime $begin,
        \DateTime $end,
        RoomType $roomType = null,
        Package $exclude = null
    ) {
        $qb = $this->createQueryBuilder()
            ->field('begin')->lte($end)
            ->field('end')->gte($begin)
            ->field('virtualRoom')->notEqual(null)
            ->field('deletedAt')->equals(null);
        $qb->sort('begin', 'asc');
        if ($roomType) {
            $qb->field('roomType.id')->equals($roomType->getId());
        }

        if ($exclude) {
            $qb->field('id')->notEqual($exclude->getId());
        }

        return $qb;
    }

    /**
     * @param PackageQueryCriteria $criteria
     * @return Package[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findByQueryCriteria(PackageQueryCriteria $criteria)
    {
        return $this->queryCriteriaToBuilder($criteria)
            ->limit(50)//todo move to args
            ->getQuery()->execute();
    }

    /**
     * @param PackageQueryCriteria $criteria
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function queryCriteriaToBuilder(PackageQueryCriteria $criteria)
    {
        $queryBuilder = $this->createQueryBuilder();
        $now = new \DateTime('midnight');
        $orderData = [];

        //confirmed
        if (isset($criteria->confirmed)) {
            $orderData['asIdsArray'] = true;
            $orderData['confirmed'] = $criteria->confirmed;
        }
        //paid status
        if (isset($criteria->paid) && in_array($criteria->paid, ['paid', 'part', 'not_paid'])) {
            $orderData['asIdsArray'] = true;
            $orderData['paid'] = $criteria->paid;
        }
        //status
        if (isset($criteria->status)) {
            $orderData['asIdsArray'] = true;
            $orderData['status'] = $criteria->status;
        }
        if (!empty($orderData)) {
            $orders = $this->dm->getRepository('MBHPackageBundle:Order')->fetch($orderData);
            $queryBuilder->field('order.id')->in($orders);
        }

        //hotel
        if (isset($criteria->hotel)) {
            $roomTypesIds = [];
            foreach ($criteria->hotel->getRoomTypes() as $roomType) {
                $roomTypesIds[] = $roomType->getId();
            }
            if (count($roomTypesIds) > 0) {
                $queryBuilder->field('roomType.id')->in($roomTypesIds);
            }
        }
        //order
        if (isset($criteria->packageOrder)) {
            if ($criteria->order instanceof Order) {
                $criteria->order = $criteria->packageOrder->getId();
            }
            $queryBuilder->field('order.id')->equals($criteria->packageOrder);
        }
        //order ids
        if ($criteria->packageOrders) {
            $queryBuilder->field('order.id')->in($criteria->packageOrders);
        }

        //roomType
        if (isset($criteria->roomType)) {
            $queryBuilder->field('roomType.id')->equals($criteria->roomType->getId());
        }

        if (isset($criteria->virtualRoom)) {
            if ($criteria->virtualRoom instanceof Room) {
                $criteria->virtualRoom = $criteria->virtualRoom->getId();
            }
            $queryBuilder->field('virtualRoom.id')->equals($criteria->virtualRoom);
        }
        $dateFilterBy = $criteria->dateFilterBy ? $criteria->dateFilterBy : 'begin';
        //begin
        if (isset($criteria->begin)) {
            $queryBuilder->field($dateFilterBy)->gte($criteria->begin);
        }

        //end
        if (isset($criteria->end)) {
            $queryBuilder->field($dateFilterBy)->lte($criteria->end);
        }

        // filter
        if (isset($criteria->filter)) {
            //live now
            if ($criteria->filter == 'live_now') {
                $queryBuilder->field('begin')->lte($now);
                $queryBuilder->field('end')->gte($now);
            }
            // without accommodation
            if ($criteria->filter == 'without_accommodation') {
                $queryBuilder->addOr($queryBuilder->expr()->field('accommodation')->exists(false));
                $queryBuilder->addOr($queryBuilder->expr()->field('accommodation')->equals(null));
            }

            // live_between
            if ($criteria->filter == 'live_between' && isset($criteria->liveBegin) && isset($criteria->liveEnd)) {
                $queryBuilder->field('begin')->lte($criteria->liveEnd);
                $queryBuilder->field('end')->gte($criteria->liveBegin);
            }
        }

        if (isset($criteria->createdBy)) {
            $queryBuilder->field('createdBy')->equals($criteria->createdBy);
        }

        //query
        if (isset($criteria->query)) {
            $query = trim($criteria->query);
            $touristsIds = $this->dm->getRepository('MBHPackageBundle:Tourist')
                ->createQueryBuilder()
                ->field('fullName')->equals(new \MongoRegex('/^.*' . $query . '.*/ui'))
                ->distinct('id')
                ->getQuery()
                ->execute()
                ->toArray();

            if (count($touristsIds)) {
                $queryBuilder->addOr($queryBuilder->expr()->field('tourists.id')->in($touristsIds));
                $queryBuilder->addOr($queryBuilder->expr()->field('mainTourist.id')->in($touristsIds));
            }

            $queryBuilder->addOr($queryBuilder->expr()->field('numberWithPrefix')->equals(new \MongoRegex('/^.*' . $query . '.*/ui')));
        }

        //isCheckIn
        if (isset($criteria->checkIn)) {
            if ($criteria->checkIn) {
                $queryBuilder->field('isCheckIn')->equals(true);
            } else {
                $queryBuilder->field('isCheckIn')->notEqual(true);
            }
        }

        //isCheckOut
        if (isset($criteria->checkOut)) {
            if ($criteria->checkOut) {
                $queryBuilder->field('isCheckOut')->equals(true);
            } else {
                $queryBuilder->field('isCheckOut')->notEqual(true);
            }
        }

        if ($criteria->sort) {
            $queryBuilder->sort($criteria->sort);
        }

        // paging
        if (isset($criteria->skip)) {
            $queryBuilder->skip($criteria->skip);
        }
        if (isset($criteria->limit)) {
            $queryBuilder->limit($criteria->limit);
        }

        //deleted
        if ($criteria->deleted) {
            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }
        } else {
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }
        }

        return $queryBuilder;
    }

    /**
     * @param Tourist $tourist
     * @param PackageQueryCriteria|null $criteria
     * @return null|Package
     */
    public function findOneByTourist(Tourist $tourist, PackageQueryCriteria $criteria = null)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $criteria ? $this->queryCriteriaToBuilder($criteria) : $this->createQueryBuilder();
        $package = $queryBuilder
            ->field('tourists.id')->equals($tourist->getId())
            ->limit(1)
            ->getQuery()->getSingleResult();
        return $package;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param null $rooms
     * @param null $excludePackages
     * @param boolean $departure
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchWithAccommodation(
        \DateTime $begin = null,
        \DateTime $end = null,
        $rooms = null,
        $excludePackages = null,
        $departure = true
    )
    {
        $qb = $this->createQueryBuilder();
        $qb->field('accommodation')->exists(true)
            ->field('accommodation')->notEqual(null);

        if ($departure) {
            $qb->addOr($qb->expr()->field('departureTime')->exists(false))
                ->addOr($qb->expr()->field('departureTime')->equals(null));
        }

        if ($begin) {
            $qb->field('end')->gte($begin);
        }
        if ($end) {
            $qb->field('begin')->lte($end);
        }
        if ($rooms) {
            is_array($rooms) ? $rooms : $rooms = [$rooms];
            $qb->field('accommodation.id')->in($rooms);
        }
        if ($excludePackages) {
            is_array($excludePackages) ? $excludePackages : $excludePackages = [$excludePackages];
            $qb->field('id')->notIn($excludePackages);
        }
        $qb->sort('begin', 'asc');

        return $qb->getQuery()->execute();
    }

    /**
     * @param Room $room
     * @return Package|null
     */
    public function getPackageByAccommodation(Room $room, \DateTime $date)
    {
        $data = clone($date);
        $data->modify('+ 1 day');

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->field('accommodation.id')->equals($room->getId())
            //->field('arrivalTime')->lte($data)
            ->field('isCheckOut')->equals(false)
            ->sort('arrivalTime', -1)
            ->limit(1);

        return $queryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Exception
     */
    public function fetchSummary($data)
    {
        unset($data['skip']);
        unset($data['limit']);

        $qb = $this->fetchQuery($data);
        $orderData = [];
        $orderQb = clone $qb;
        $ordersIds = $orderQb->distinct('order.$id')->getQuery()->execute();

        if (!empty($ordersIds)) {
            $dm = $this->getDocumentManager();
            $orderQb = $dm->getRepository('MBHPackageBundle:Order')->createQueryBuilder('o');
            $orderQb
                ->field('id')
                ->in(iterator_to_array($ordersIds))
                ->group(
                    ['id' => 1],
                    [
                        'paid' => 0,
                        'debt' => 0,
                    ]
                )->reduce(
                    'function (obj, prev) {
                        var price = 0;

                        if(obj.totalOverwrite) {
                            price = obj.totalOverwrite;
                        } else {
                            price = obj.price;
                        }
                        prev.paid += obj.paid;
                        prev.debt += price - obj.paid;
                    }'
                );

            $orderData = iterator_to_array($orderQb->getQuery()->execute());
        }


        $qb = $this->fetchQuery($data);

        $qb->group(
            ['id' => 1],
            [
                'total' => 0,
                'paid' => 0,
                'debt' => 0,
                'nights' => 0,
                'guests' => 0,
            ]
        )->reduce(
            'function (obj, prev) {
                var oneDay = 24*60*60*1000;

                if(obj.totalOverwrite) {
                    prev.total += obj.totalOverwrite;
                } else {
                    var price = parseFloat(obj.price, 10);
                    if (price) {
                        prev.total += price;
                    }

                    if (obj.servicesPrice) {
                        prev.total += obj.servicesPrice
                    }
                    if (obj.discount) {
                        var discount = obj.isPercentDiscount ? obj.price * obj.discount/100 : obj.discount;
                        prev.total -= discount;
                    }
                }

                prev.guests += obj.adults + obj.children

                prev.nights += Math.round(Math.abs((obj.end.getTime() - obj.begin.getTime())/(oneDay)));
            }'
        );

        $packageResult = $qb->getQuery()->execute();

        if (!empty($packageResult[0])) {
            if (!empty($orderData[0])) {

                return array_merge($packageResult[0], $orderData[0]);
            }

            return $packageResult[0];
        }

        return null;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param string $groupType
     * @param string $type
     * @param array $roomTypesIds
     * @param \DateTime $creationBegin
     * @param \DateTime $creationEnd
     * @return array
     */
    public function getDistributionByDaysOfWeek(
        \DateTime $begin,
        \DateTime $end,
        string $groupType,
        string $type,
        array $roomTypesIds,
        ?\DateTime $creationBegin,
        ?\DateTime $creationEnd
    ) {
        $filterField = $groupType === 'arrival' ? 'begin' : 'end';
        $qb = $this
            ->createQueryBuilder()
            ->field($filterField)->gte($begin)
            ->field($filterField)->lte($end);

        if (count($roomTypesIds) > 0) {
            $qb->field('roomType.id')->in($roomTypesIds);
        }
        if ($type === 'actual') {
            $qb->addOr($qb->expr()->field('deletedAt')->exists(false));
            $qb->addOr($qb->expr()->field('deletedAt')->equals(null));
        }
        if ($type == 'deleted') {
            $qb->addAnd($qb->expr()->field('deletedAt')->exists(true));
            $qb->addAnd($qb->expr()->field('deletedAt')->notEqual(null));
        }
        if (!is_null($creationBegin)) {
            $qb->field('createdAt')->gte($creationBegin);
        }
        if (!is_null($creationEnd)) {
            $qb->field('createdAt')->lte($creationEnd);
        }

        $distributionData = $qb
            ->map(
                'function() {
                    var dayOfWeek = (this.' . $filterField . '.getDay() + 6) % 7;
                    emit(dayOfWeek, this)
                }'
            )
            ->reduce(
                'function(key, values) {
                    var byRoomTypes = {};
                    values.forEach(function(elem, index) {
                        if (elem._id) {
                            var packagePrice;
                            if(elem.totalOverwrite) {
                                packagePrice = elem.totalOverwrite;
                            } else {
                                packagePrice = parseFloat(elem.price, 10);
            
                                if (elem.servicesPrice) {
                                    packagePrice += elem.servicesPrice
                                }
                                if (elem.discount) {
                                    var discount = elem.isPercentDiscount ? elem.price * elem.discount/100 : elem.discount;
                                    packagePrice -= discount;
                                }
                            }
                            if (!elem.roomType) {
                                throw JSON.stringify(values);
                            }
                            
                            var roomTypeId = elem.roomType.$id.valueOf();
                            if (byRoomTypes[roomTypeId]) {
                                byRoomTypes[roomTypeId]["price"] += packagePrice;
                                byRoomTypes[roomTypeId]["count"]++;
                            } else {
                                byRoomTypes[roomTypeId] = {price: packagePrice, count: 1}
                            } 
                        } else {
                            for (var roomTypeId in elem) {
                                if (byRoomTypes[roomTypeId]) {
                                    byRoomTypes[roomTypeId]["price"] += elem[roomTypeId]["price"];
                                    byRoomTypes[roomTypeId]["count"] += elem[roomTypeId]["count"];
                                } else {
                                    byRoomTypes[roomTypeId] =
                                        {price: elem[roomTypeId]["price"], count: elem[roomTypeId]["count"]}; 
                                }
                            }
                        }
                    });
                    return byRoomTypes;
                }'
            )
            ->getQuery()
            ->execute()
            ->toArray();

        foreach ($distributionData as $dayOfWeekNumber => $dayOfWeekData) {
            if (isset($dayOfWeekData['value']['_id'])) {
                $packageData = $dayOfWeekData['value'];
                if (isset($packageData['totalOverwrite'])) {
                    $packagePrice = $packageData['totalOverwrite'];
                } else {
                    $packagePrice = floatval($packageData['price']);

                    if (isset($packageData['servicesPrice'])) {
                        $packagePrice += $packageData['servicesPrice'];
                    }
                    if (isset($packageData['discount'])) {
                        $discount = isset($packageData['isPercentDiscount']) && $packageData['isPercentDiscount'] === true
                            ? $packageData['price'] * $packageData['discount'] / 100 : $packageData['discount'];
                        $packagePrice -= $discount;
                    }
                }

                /** @var \MongoId $roomTypeMongoId */
                $roomTypeMongoId = $packageData['roomType']['$id'];
                $distributionData[$dayOfWeekNumber]['value'] = [$roomTypeMongoId->serialize() => [
                    'count' => 1,
                    'price' => $packagePrice
                ]];
            }
        }

        return $distributionData;
    }

    /**
     * @deprecated todo use queryCriteriaToBuilder
     * @see PackageRepository::queryCriteriaToBuilder
     *
     * @param $data
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     * @throws \Exception
     */
    public function fetchQuery($data)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getDocumentManager();
        $qb = $this->createQueryBuilder('s');
        $now = new \DateTime('midnight');
        $orderData = [];
        $isShowDeleted = isset($data['deleted']) && $data['deleted'];

        //confirmed
        if (isset($data['confirmed']) && $data['confirmed'] != null) {
            $orderData = array_merge(
                $orderData,
                ['asIdsArray' => true, 'confirmed' => !empty($data['confirmed']) ? true : false]
            );
        }
        //paid status
        if (isset($data['paid']) && in_array($data['paid'], ['paid', 'part', 'not_paid'])) {
            $orderData = array_merge($orderData, ['asIdsArray' => true, 'paid' => $data['paid']]);
        }
        //status
        if (isset($data['status']) && !empty($data['status'])) {
            $orderData = array_merge($orderData, ['asIdsArray' => true, 'status' => $data['status']]);
        }
        if (!empty($orderData)) {
            if ($isShowDeleted && $dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $dm->getFilterCollection()->disable('softdeleteable');
            }

            $orders = $dm->getRepository('MBHPackageBundle:Order')->fetch($orderData);

            if (!$dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $dm->getFilterCollection()->enable('softdeleteable');
            }
            $qb->field('order.id')->in($orders);
        }

        //hotel
        if (isset($data['hotel']) && !empty($data['hotel'])) {
            if (!$data['hotel'] instanceof Hotel) {
                $data['hotel'] = $dm->getRepository('MBHHotelBundle:Hotel')->find($data['hotel']);

                if (!$data['hotel']) {
                    throw new \Exception('Hotel not found.');
                }
            }
            $roomTypesIds = [];
            foreach ($data['hotel']->getRoomTypes() as $roomType) {
                $roomTypesIds[] = $roomType->getId();
            }

            $qb->field('roomType.id')->in($roomTypesIds);

        }
        //order
        if (isset($data['packageOrder']) && !empty($data['packageOrder'])) {
            if ($data['order'] instanceof Order) {
                $data['order'] = $data['packageOrder']->getId();
            }
            $qb->field('order.id')->equals($data['packageOrder']);
        }
        //order ids
        if (isset($data['packageOrders']) && !empty($data['packageOrders']) && is_array($data['packageOrders'])) {
            $qb->field('order.id')->in($data['packageOrders']);
        }

        //roomType
        if (isset($data['roomType']) && !empty($data['roomType'])) {
            if ($data['roomType'] instanceof RoomType) {
                $data['roomType'] = $data['roomType']->getId();
            }
            $qb->field('roomType.id')->equals($data['roomType']);
        }

        //get dates
        $dateType = 'begin';
        if (isset($data['dates']) && !empty($data['dates'])) {
            $dateType = $data['dates'];
        }

        if (isset($data['begin']) && !$data['begin'] instanceof \DateTime) {
            $data['begin'] = \DateTime::createFromFormat('d.m.Y H:i:s', $data['begin'] . ' 00:00:00');
        }

        if (isset($data['end']) && !$data['end'] instanceof \DateTime) {
            $data['end'] = \DateTime::createFromFormat('d.m.Y H:i:s', $data['end'] . ' 00:00:00');
        }

        if ($dateType == 'accommodation') {
            if ($data['begin'] && $data['end']) {
                $expr = $qb->expr();
                $expr->addOr($qb->expr()
                    ->field('begin')->gte($data['begin'])->lte($data['end'])
                );
                $expr->addOr($qb->expr()
                    ->field('end')->gte($data['begin'])->lte($data['end'])
                );
                $expr->addOr($qb->expr()
                    ->field('begin')->lte($data['begin'])
                    ->field('end')->gte($data['end'])
                );

                $qb->addAnd($expr);
            }
        } else {
            if (($dateType === 'createdAt' || $dateType === 'deletedAt') && isset($data['end']) && $data['end'] instanceof \DateTime) {
                $data['end']->modify('+1 day');
            }
            if (isset($data['begin']) && !empty($data['begin'])) {
                $qb->field($dateType)->gte($data['begin']);
            }
            if (isset($data['end']) && !empty($data['end'])) {
                $qb->field($dateType)->lte($data['end']);
            }
        }

        // filter
        if (isset($data['filter']) && $data['filter'] != null) {
            //live now
            if ($data['filter'] == 'live_now') {
                $qb->field('begin')->lte($now);
                $qb->field('end')->gte($now);
            }
            // without accommodation
            if ($data['filter'] == 'without_accommodation') {
                $qb->addOr($qb->expr()->field('accommodation')->exists(false));
                $qb->addOr($qb->expr()->field('accommodation')->equals(null));
            }

            // live_between
            if ($data['filter'] == 'live_between' && isset($data['live_begin']) && isset($data['live_end'])) {
                $qb->field('begin')->lte($data['live_end']);
                $qb->field('end')->gte($data['live_begin']);
            }
        }

        if (isset($data['createdBy']) && $data['createdBy'] != null) {
//            $qb->field('createdBy')->equals($data['createdBy']);
            $qb->addOr($qb->expr()->field('createdBy')->equals($data['createdBy']));
            $qb->addOr($qb->expr()->field('createdBy')->equals(null));
        }

        //query
        if (isset($data['query']) && !empty($data['query'])) {
            $query = trim($data['query']);
            $touristsIds = $dm
                ->getRepository('MBHPackageBundle:Tourist')
                ->getIdsWithNameByQueryString($query);

            if (count($touristsIds)) {
                $qb->addOr($qb->expr()->field('tourists.id')->in($touristsIds));
                $qb->addOr($qb->expr()->field('mainTourist.id')->in($touristsIds));
            }

            $qb->addOr($qb->expr()->field('numberWithPrefix')->equals(new \MongoRegex('/^.*' . $query . '.*/ui')));

            //Find by order
            /** @var  DocumentManager $dm */
            $order = $dm->getRepository('MBHOnlineBundle:Order')->find($query);
            if ($order) {
                $qb->addOr($qb->expr()->field('order.id')->equals($order->getId()));
            }

        }

        //isCheckIn
        if (isset($data['checkIn'])) {
            if (!empty($data['checkIn'])) {
                $qb->field('isCheckIn')->equals(true);
            } else {
                $qb->field('isCheckIn')->notEqual(true);
            }
        }

        //isCheckOut
        if (isset($data['checkOut'])) {
            if (!empty($data['checkOut'])) {
                $qb->field('isCheckOut')->equals(true);
            } else {
                $qb->field('isCheckOut')->notEqual(true);
            }
        }

        //order
        $order = 'createdAt';
        $dir = 'desc';
        $cols = [
            1 => ['order.id', 'number'],
            2 => 'begin',
            3 => 'roomType',
            4 => 'mainTourist',
            5 => 'price',
            6 => 'createdAt',
            7 => 'end'
        ];
        if (isset($data['order']) && isset($cols[$data['order']])) {
            $order = $cols[$data['order']];
        }
        if (isset($data['dir']) && in_array($data['dir'], ['asc', 'desc'])) {
            $dir = $data['dir'];
        }
        if (is_array($order)) {
            foreach ($order as $ord) {
                $qb->sort($ord, $dir);
            }
        } else {
            $qb->sort($order, $dir);
        }

        // paging
        if (isset($data['skip'])) {
            $qb->skip($data['skip']);
        }
        if (isset($data['limit'])) {
            $qb->limit($data['limit']);
        }

        //deleted if
        if (isset($data['deleted']) && $data['deleted'] || $dateType == 'deletedAt') {
            if ($dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $dm->getFilterCollection()->disable('softdeleteable');
            }
        } else {
            if (!$dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $dm->getFilterCollection()->enable('softdeleteable');
            }
        }

        return $qb;
    }

    /**
     * @deprecated todo use findByQueryCriteria
     * @see PackageRepository::findByQueryCriteria
     * @param $data
     * @return \MBH\Bundle\PackageBundle\Document\Package[]
     * @throws \Exception
     */
    public function fetch($data)
    {
        $qb = $this->fetchQuery($data);

        if (isset($data['count']) && $data['count']) {
            $docs = $qb->getQuery()->count();
        } else {
            $docs = $qb->getQuery()->execute();
        }

        return $docs;
    }


    /**
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function getArrivalsQueryBuilder($limit = 14)
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->addOr($queryBuilder->expr()
                ->field('begin')->gte(new \DateTime('midnight'))
                ->field('begin')->lte(new \DateTime('midnight + 1 day'))
            )
            ->addOr($queryBuilder->expr()
                ->field('begin')->lte(new \DateTime('midnight'))
                ->field('begin')->gte(new \DateTime('midnight - ' . (int)$limit . ' days'))
                ->field('isCheckIn')->equals(false)
            );
        return $queryBuilder;
    }

    protected function getLivesQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->field('begin')->lte(new \DateTime('midnight'))
            ->field('end')->gte(new \DateTime('midnight'))
            ->field('isCheckIn')->equals(true)
            ->field('isCheckOut')->equals(false);
        return $queryBuilder;
    }

    /**
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function getOutQueryBuilder($limit = 14)
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->field('isCheckIn')->equals(true)
            ->addOr($queryBuilder->expr()
                ->field('end')->gte(new \DateTime('midnight'))
                ->field('end')->lte(new \DateTime('midnight + 1 day'))
            )
            ->addOr($queryBuilder->expr()
                ->field('end')->lte(new \DateTime('midnight'))
                ->field('end')->gte(new \DateTime('midnight - ' . (int)$limit . ' days'))
                ->field('isCheckOut')->equals(false)
            );
        return $queryBuilder;
    }

    /**
     * @param $type
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function getQueryBuilderByType($type)
    {
        $method = 'get' . ucfirst($type) . 'QueryBuilder';
        return method_exists($this, $method) ? $this->$method() : null;
    }

    /**
     * @param $type
     * @raram Hotel $hotel
     * @return Package[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findByType($type, Hotel $hotel = null)
    {
        $queryBuilder = $this->getQueryBuilderByType($type);

        if (!$queryBuilder) {
            return [];
        }

        if ($hotel) {
            $roomTypes = [];
            foreach ($hotel->getRoomTypes() as $roomType) {
                $roomTypes[] = $roomType->getId();
            }
            $queryBuilder->field('roomType.id')->in($roomTypes);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param $type
     * @param bool $attention
     * @param Hotel $hotel
     * @param int $limit
     * @return int
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function countByType($type, $attention = false, Hotel $hotel = null, $limit = 14)
    {
        $queryBuilder = $this->getQueryBuilderByType($type);

        if ($hotel) {
            $roomTypes = [];
            foreach ($hotel->getRoomTypes() as $roomType) {
                $roomTypes[] = $roomType->getId();
            }
            $queryBuilder->field('roomType.id')->in($roomTypes);
        }

        if ($attention) {
            if ($type == 'arrivals') {
                $queryBuilder
                    ->field('isCheckIn')->equals(false)
                    ->field('begin')->gte(new \DateTime('midnight - ' . (int)$limit . ' days'))
                    ->field('begin')->lte(new \DateTime('midnight'));
            }
            if ($type == 'out') {
                $queryBuilder
                    ->field('isCheckOut')->equals(false)
                    ->field('end')->gte(new \DateTime('midnight - ' . (int)$limit . ' days'))
                    ->field('end')->lte(new \DateTime('midnight'));
            }
        }
        $queryBuilder->addAnd(
          $queryBuilder->expr()->field('deletedAt')->equals(null)
        );

        return $queryBuilder->getQuery()->count();
    }

    /**
     * @param PackageQueryCriteria $criteria
     * @return string[]
     */
    public function findTouristIDsByCriteria(PackageQueryCriteria $criteria)
    {
        $queryBuilder = $this->queryCriteriaToBuilder($criteria);
        $query = $queryBuilder->getQuery()->getQuery()['query'];

        $aggregate = [];
        if ($query) {
            $aggregate[] = ['$match' => $query];
        }
        $aggregate[] = ['$project' => ['tourists' => 1]];
        $aggregate[] = ['$unwind' => '$tourists'];
        $aggregate[] = ['$group' => ['_id' => '$tourists']];

        $result = $this->dm->getDocumentCollection(Package::class)->aggregate($aggregate);

        $ids = [];
        foreach ($result as $tourist) {
            $ids[] = strval($tourist['_id']['$id']);
        }

        return $ids;
    }

    public function findByOrderOrRoom(string $term, Helper $helper)
    {
        $queryRoom = $this->getDocumentManager()->getRepository('MBHHotelBundle:Room')->createQueryBuilder();
        $queryRoom
            ->addOr(
                $queryRoom->expr()->field('fullTitle')->equals(new \MongoRegex('/.*' . $term . '.*/i'))
            )
            ->addOr(
                $queryRoom->expr()->field('title')->equals(new \MongoRegex('/.*' . $term . '.*/i'))
            );

        $rooms = $queryRoom->getQuery()->execute();

        $roomIds = $helper->toIds($rooms);

        $queryPackage = $this->createQueryBuilder();
        $queryPackage
            ->addOr($queryPackage->expr()->field('accommodation.id')->in($roomIds))
            ->addOr($queryPackage->expr()->field('numberWithPrefix')->equals(new \MongoRegex('/.*' . $term . '.*/i')))
            ->field('departureTime')->exists(false)
            ->field('begin')->lte(new \DateTime('midnight'))
            ->field('end')->gte(new \DateTime('midnight'));

        return $queryPackage->getQuery()->execute();
    }

    public function getNumbers(\DateTime $day, RoomType $roomType):array
    {
        $packages = $this->createQueryBuilder()
            ->select('numberWithPrefix')
            ->field('begin')->lte($day)
            ->field('end')->gte($day)
            ->field('roomType')->references($roomType)
            ->field('deletedAt')->equals(null)
            ->hydrate(false)
            ->getQuery()
            ->execute();

        return array_map(function ($package) {
            return $package['numberWithPrefix'];
        }, iterator_to_array($packages));

    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return mixed
     */
    public function getNotVirtualRoom(\DateTime $begin, \DateTime $end)
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->addOr($queryBuilder->expr()->field('virtualRoom')->exists(false))
            ->addOr($queryBuilder->expr()->field('virtualRoom')->equals(null))
            ->field('begin')->lte($end)
            ->field('end')->gte($begin);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return mixed
     */
    public function getVirtualRoomNotRoom(\DateTime $begin, \DateTime $end, $rooms){
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->field('virtualRoom.id')->in($rooms)
            ->field('begin')->gte($begin)
            ->field('end')->lte($end);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return Package
     */
    public function getPackageCategory(\DateTime $begin, \DateTime $end, $categoryRoomType = null,$count = false,$limit = false,  $skip = 0)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->field('begin')->gte($begin)
            ->sort('createdAt', 'desc')
            ->skip($skip)
            ->field('end')->lte($end);

        if ($limit){
            $queryBuilder->limit(500);
        }
        if($count){
            $queryBuilder->count();
        }
        if ($categoryRoomType) {
            $queryBuilder->field('roomType.id')->in($categoryRoomType);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $roomTypes
     * @param bool $isSorted
     * @return mixed
     */
    public function getPackagesByCreationDatesAndRoomTypeIds(\DateTime $begin, \DateTime $end, $roomTypes = null, $isSorted = true){
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->field('createdAt')->gte($begin)
            ->field('createdAt')->lte($end)
            ->sort('createdAt', 'asc');

        if($roomTypes) {
            $queryBuilder->field('roomType.id')->in($roomTypes);
        }

        $packages = $queryBuilder->getQuery()->execute();
        if (!$isSorted) {
            return $packages;
        }

        $sortedPackages = [];
        /** @var Package $package */
        foreach ($packages as $package) {
            $sortedPackages[$package->getRoomType()->getId()][$package->getCreatedAt()->format('d.m.Y')][] = $package;
        }

        return $sortedPackages;
    }

    /**
     * @param $ordersIds
     * @return Cursor|Package[]
     */
    public function getByOrdersIds($ordersIds)
    {
        return $this
            ->createQueryBuilder()
            ->field('order.id')->in($ordersIds)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \DateTime $searchBegin
     * @param \DateTime $searchEnd
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getRawAccommodationByPeriod(\DateTime $searchBegin, \DateTime $searchEnd)
    {
        $result = $this->createQueryBuilder()
            ->field('begin')->lt($searchEnd)
            ->field('end')->gt($searchBegin)
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType|null $roomType
     * @param bool $group
     * @param Package|null $exclude
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchWithVirtualRoomsRaw(
        \DateTime $begin,
        \DateTime $end,
        RoomType $roomType = null,
        bool $group = false,
        Package $exclude = null
    )
    {
        $qb = $this->fetchWithVirtualRoomsQB($begin, $end, $roomType, $exclude);

        return $qb->select(['virtualRoom', 'begin', 'end'])->hydrate(false)->getQuery()->execute()->toArray();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType|null $roomType
     * @param Package|null $exclude
     * @return Builder
     */
    private function fetchWithVirtualRoomsQB(\DateTime $begin, \DateTime $end, RoomType $roomType = null, Package $exclude = null){
        $qb = $this->createQueryBuilder();
        if ($exclude) {
            $qb->field('id')->notEqual($exclude->getId());
        }
        if ($roomType) {
            $qb->field('roomType.id')->equals($roomType->getId());
        }
        $qb
            ->field('end')->gte($begin)
            ->field('begin')->lte($end)
            ->field('virtualRoom')->notEqual(null)
            ->field('deletedAt')->equals(null);

        return $qb;
    }
}