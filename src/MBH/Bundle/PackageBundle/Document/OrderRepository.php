<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class OrderRepository
 */
class OrderRepository extends DocumentRepository
{
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param bool $isGrouped
     * @return array
     */
    public function fetchWithPolls(\DateTime $begin = null, \DateTime $end = null, $isGrouped = false)
    {
        $qb = $this->createQueryBuilder('s')
            ->field('pollQuestions')->exists(true)
            ->field('pollQuestions')->notEqual(null)
        ;
        if ($begin) {
            $qb->field('createdAt')->gte($begin);
        }
        if ($end) {
            $qb->field('createdAt')->lte($end);
        }

        if($isGrouped) {
            $result = [
                'orders' => [], 'categories' => []
            ];
            foreach ($qb->getQuery()->execute() as $key => $order) {
                $result['orders'][$key] = [
                    'order' => $order,
                ];

                foreach ($order->getPollQuestions() as $pollQuestion) {
                    $question = $pollQuestion->getQuestion();
                    if (!$question || !$pollQuestion->getIsQuestion()) {
                        continue;
                    }

                    $cat = $question->getCategory();

                    isset($result['orders'][$key][$cat]) ?: $result['orders'][$key][$cat] = [];

                    $result['orders'][$key][$cat][] = $pollQuestion->getValue();
                    $result['categories'][] = $cat;
                }
            }
            $result['categories'] = array_unique($result['categories']);

            foreach ($result['orders'] as $key => $orderInfo) {
                foreach ($result['categories'] as $cat) {
                    if (!isset($orderInfo[$cat])) {
                        continue;
                    }
                    $result['orders'][$key][$cat] = number_format(round(array_sum($orderInfo[$cat])/count($orderInfo[$cat]), 2), 2);
                }
            }

            return $result;
        } else {
            return $qb->getQuery()->execute();
        }
    }

    /**
     * @param $data
     * @return \MBH\Bundle\PackageBundle\Document\Order[]
     * @throws \Exception
     */
    public function fetch($data)
    {
        $qb = $this->createQueryBuilder('s');

        //confirmed
        if (isset($data['confirmed'])) {

            if (!empty($data['confirmed'])) {
                $qb->field('confirmed')->equals(true);
            } else {
                $qb->addOr($qb->expr()->field('confirmed')->exists(false));
                $qb->addOr($qb->expr()->field('confirmed')->equals(false));
            }
        }

        //paid status
        if (isset($data['paid']) && in_array($data['paid'], ['paid', 'part', 'not_paid'])) {
            switch ($data['paid']) {
                case 'paid':
                    $qb->field('isPaid')->equals(true);
                    break;
                case 'part':
                    $qb->field('isPaid')->equals(false)
                        ->field('paid')->gt(0)
                    ;
                    break;
                case 'not_paid':
                    $qb->field('isPaid')->equals(false)
                        ->field('paid')->equals(0)
                    ;
                    break;
                default:
                    break;
            }
        }

        //status
        if(isset($data['status']) && !empty($data['status'])) {
            $qb->field('status')->equals($data['status']);
        }

        if (isset($data['count']) && $data['count']) {
            $docs = $qb->getQuery()->count();
        } else {
            $docs = $qb->getQuery()->execute();
            if(isset($data['asIdsArray']) && !empty($data['asIdsArray'])) {


                $ids = [];
                foreach ($docs as $doc) {
                    $ids[] = $doc->getId();
                }
                return $ids;
            }
        }



        return $docs;
    }


    /**
     * @param $orders
     * @return float|int
     */
    public function getRateByOrders($orders)
    {
        $rate = 0;
        $divider = 0;
        foreach($orders as $order) {
            foreach($order->getPollQuestions() as $question) {
                if ($question->getIsQuestion()) {
                    $rate += (int)$question->getValue();
                    ++$divider;
                }
            }
        }
        if($divider) {
            $rate = $rate / $divider;
        }

        return $rate;
    }

    /**
     * @param Hotel $hotel
     * @return array|Order[]
     */
    public function findByHotel(Hotel $hotel)
    {
        $roomTypes = $hotel->getRoomTypes();
        $roomTypeIDs = [];
        foreach($roomTypes as $roomType) {
            $roomTypeIDs[] = $roomType->getId();
        }

        if(!$roomTypeIDs) {
            return [];
        }

        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
        $packages = $packageRepository->createQueryBuilder()->hydrate(false)
            ->select('order')->field('roomType.id')->in($roomTypeIDs)->getQuery()->toArray();
        $ids = array_column(array_column($packages, 'order'), '$id');
        if(!$ids) {
            return [];
        }

        $orderRepository = $this->dm->getRepository('MBHPackageBundle:Order');
        /** @var Order[] $orders */
        $orders = $orderRepository->createQueryBuilder()->field('id')->in($ids)->getQuery()->execute();

        return $orders;
    }
}
