<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentRepository;

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
     * @param \DateTime $deadlineDate
     * @return array
     */
    public function getUnpaidOrders(\DateTime $deadlineDate)
    {
        return $this->createQueryBuilder()->field('createdAt')->lte($deadlineDate)->getQuery()->execute()->toArray();
    }

    /**
     * @param $ordersIds
     * @return mixed
     */
    public function getByOrdersIds($ordersIds)
    {
        return $this
            ->createQueryBuilder()
            ->field('id')->in($ordersIds)
            ->getQuery()
            ->execute();
    }
    /**
     * @param Package[] $packages
     */
    public function loadRelatedOrders(array $packages)
    {
        $orderIds = array_map(function(Package $package) {
            return $package->getOrder()->getId();
        }, $packages);

        return $this->createQueryBuilder()
            ->field('id')->in($orderIds)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @param \DateTime $date
     * @param null $ordersIds
     * @return Cursor|Order[]
     */
    public function getUnpaidOrOverpaidOnDate(\DateTime $date, $ordersIds = null)
    {
        $qb = $this->createQueryBuilder();
        if (!is_null($ordersIds)) {
            $qb->field('id')->in($ordersIds);
        }
        $qb
            ->addOr($qb->expr()
                ->field('updatedAt')->lt($date)
                ->where('function() {
                return this.price != this.paid && this.price != this.paid;
            }'))
            ->addOr($qb->expr()
                ->field('updatedAt')->gte($date)
            );

        return $qb
            ->getQuery()
            ->execute();
    }
}
