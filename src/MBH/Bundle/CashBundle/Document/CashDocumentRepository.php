<?php

namespace MBH\Bundle\CashBundle\Document;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;

class CashDocumentRepository extends DocumentRepository
{
    /**
     * @param Order $order
     * @return PayerInterface[]
     */
    public function getAvailablePayersByOrder(Order $order)
    {
        $payers = [];
        $mainTourist = $order->getMainTourist();
        /** @var Organization $organization */
        $organization = $order->getOrganization();
        /** @var Tourist[] $allTourists */
        $allTourists = $this->getDocumentManager()->getRepository('MBHPackageBundle:Tourist')->getAllTouristsByOrder($order);
        if ($organization) {
            $payers[] = $organization;
        }
        if ($mainTourist) {
            $payers[$mainTourist->getId()] = $mainTourist;
        }
        if ($allTourists) {
            $payers += $allTourists;
        }

        return $payers;
    }

    /**
     * @param $type
     * @param $search
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param boolean $isPaid Only paid
     * @param boolean $isConfirmed
     * @param string $filterByRange
     * @param string[] $methods
     * @return int
     * @throws \Exception
     */
    public function total($type, $search, \DateTime $begin = null, \DateTime $end = null, $filterByRange = 'documentDate', $isPaid = false, $isConfirmed = null, $methods = [], $orderIds = [])
    {
        if (!in_array($type, ['in', 'out'])) {
            throw new \Exception('Invalid type');
        }

        $qb = $this->createQueryBuilder('CashDocument');

        if (!empty($search)) {
            $qb->addOr($qb->expr()->field('total')->equals((int)$search));
            $qb->addOr($qb->expr()->field('prefix')->equals(new \MongoRegex('/.*' . $search . '.*/ui')));
        }
        if ($type == 'in') {
            $qb->field('operation')->notIn(['out', 'fee']);
        } else {
            $qb->field('operation')->in(['out', 'fee']);
        }

        if ($begin) {
            $qb->field($filterByRange)->gte($begin);
        }

        if ($end) {
            $qb->field($filterByRange)->lte($end);
        }

        if ($isPaid) {
            $qb->field('isPaid')->equals(true);
        }

        if ($methods) {
            $qb->field('method')->in($methods);
        }

        if ($orderIds) {
            $qb->field('order.id')->in($orderIds);
        }

        if(isset($isConfirmed)){
            $qb->field('isConfirmed')->equals($isConfirmed);
        }

        $qb->map('function() { emit(1, this.total); }')
            ->reduce('function(k, vals) {
                    var sum = 0;
                    for (var i in vals) {
                        sum += vals[i];
                    }
                    return sum;
            }');

        $result = $qb->getQuery()->execute();

        return (isset($result[0]['value'])) ? $result[0]['value'] : 0;
    }


    /**
     * @param CashDocument $document
     * @return string
     */
    public function generateNewNumber(CashDocument $document)
    {
        $number = $this->createQueryBuilder()
            ->field('order.id')
            ->equals($document->getOrder()->getId())
            ->getQuery()
            ->count();

        return $document->getOrder()->getId() . '-' . (++$number);
    }


    /**
     * @param $start
     * @param $limit
     * @param $sort
     * @param $dir
     * @param $methods
     * @param $search
     * @param $isPaid
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param string $filterByRange
     * @param string[] $orderIds
     * @param boolean $byDays
     * @return CashDocument[]|array
     *
     * @todo less arguments, Criteria Filter ...
     */
    public function getListForCash(
        $start,
        $limit,
        $sort,
        $dir,
        $methods,
        $search,
        $isPaid,
        \DateTime $begin = null,
        \DateTime $end = null,
        $filterByRange = 'documentDate',
        $orderIds = [],
        $byDays = false
    ) {
        $qb = $this->createQueryBuilder('CashDocument')
            ->skip($start)
            ->limit($limit);

        $qb->sort($sort, $dir);

        if ($methods) {
            $qb->field('method')->in($methods);
        }

        if ($isPaid) {
            $qb->field('isPaid')->equals(true);
        }

        //Search
        if (!empty($search)) {
            $qb->addOr($qb->expr()->field('total')->equals((int)$search));
            $qb->addOr($qb->expr()->field('prefix')->equals(new \MongoRegex('/.*' . $search . '.*/ui')));
        }


        if (!$begin) {
            $begin = new \DateTime('midnight -7 days');
        }
        if (!$end) {
            $end = new \DateTime('midnight +1 day');
        }

        $qb->field($filterByRange)->gte($begin);
        $qb->field($filterByRange)->lte($end);

        if ($orderIds) {
            $qb->field('order.id')->in($orderIds);
        }

        if ($byDays) {
            return $this->getByDays($qb);
        }

        return $qb->getQuery()->execute();//->toArray();
    }

    /**
     * @param Builder $builder
     * @return array
     */
    public function getByDays(Builder $builder)
    {
        return $builder
            ->field('paidDate')->type(9)
            ->group(['paidDate' => 1], ['totalIn' => 0, 'totalOut' => 0, 'confirmedTotalIn' => 0, 'confirmedTotalOut' => 0,'noConfirmedTotalIn' => 0, 'noConfirmedTotalOut' => 0, 'countIn' => 0, 'countOut' => 0])
            ->reduce('function (obj, prev) {
                    if (obj.operation == "in") {
                        if(obj.isConfirmed) {
                            prev.confirmedTotalIn += obj.total;
                        } else {
                            prev.noConfirmedTotalIn += obj.total;
                        }
                        prev.totalIn += obj.total;
                        prev.countIn++;
                    } else {
                        if(obj.isConfirmed) {
                            prev.confirmedTotalOut += obj.total;
                        } else {
                            prev.noConfirmedTotalOut += obj.total;
                        }
                        prev.totalOut += obj.total;
                        prev.countOut++;
                    }
                }')->getQuery()->execute()->toArray();
    }
}
