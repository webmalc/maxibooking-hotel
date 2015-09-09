<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\HotelBundle\Document\Hotel;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Form\RoomType;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class PackageRepository
 */
class PackageRepository extends DocumentRepository
{
    /**
     * @param Tourist $tourist
     * @return null|Package
     */
    public function getPackageByTourist(Tourist $tourist)
    {
        $queryBuilder = $this->createQueryBuilder();
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
    ) {
        $qb = $this->createQueryBuilder('s');
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
            ->field('arrivalTime')->lte($data)
            ->field('isCheckOut')->equals(false)
            ->sort('arrivalTime', -1)
            ->limit(1)
        ;

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
                    prev.total += obj.price;

                    if (obj.servicesPrice) {
                        prev.total += obj.servicesPrice
                    }
                    if (obj.discount) {
                        prev.total -= obj.price * obj.discount/100
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
            $orders = $dm->getRepository('MBHPackageBundle:Order')->fetch($orderData);
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
            if (count($roomTypesIds)) {
                $qb->field('roomType.id')->in($roomTypesIds);
            }
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

        //begin
        if (isset($data['begin']) && !empty($data['begin'])) {
            if (!$data['begin'] instanceof \DateTime) {
                $data['begin'] = \DateTime::createFromFormat('d.m.Y H:i:s', $data['begin'].' 00:00:00');
            }

            $qb->field($dateType)->gte($data['begin']);
        }

        //end
        if (isset($data['end']) && !empty($data['end'])) {
            if (!$data['end'] instanceof \DateTime) {
                $data['end'] = \DateTime::createFromFormat('d.m.Y H:i:s', $data['end'].' 00:00:00');
            }

            $qb->field($dateType)->lte($data['end']);
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
            $qb->field('createdBy')->equals($data['createdBy']);
        }

        //query
        if (isset($data['query']) && !empty($data['query'])) {
            $query = trim($data['query']);
            $tourists = $dm->getRepository('MBHPackageBundle:Tourist')
                ->createQueryBuilder('t')
                ->field('fullName')->equals(new \MongoRegex('/.*'.$query.'.*/ui'))
                ->getQuery()
                ->execute();

            $touristsIds = [];
            foreach ($tourists as $tourist) {
                $touristsIds[] = $tourist->getId();
            }

            if (count($touristsIds)) {
                $qb->addOr($qb->expr()->field('tourists.id')->in($touristsIds));
                $qb->addOr($qb->expr()->field('mainTourist.id')->in($touristsIds));
            }

            $qb->addOr($qb->expr()->field('numberWithPrefix')->equals(new \MongoRegex('/.*'.$query.'.*/ui')));
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
        if(is_array($order)) {
            foreach($order as $ord) {
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

        //deleted
        if (isset($data['deleted']) && $data['deleted']) {
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

}
