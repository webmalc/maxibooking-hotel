<?php

namespace MBH\Bundle\CashBundle\Document;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\QueryBuilder;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;

/**
 * Class CashDocumentRepository
 * @package MBH\Bundle\CashBundle\Document
 */
class CashDocumentRepository extends DocumentRepository
{
    /**
     * @param Order $ordera
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
     * @param CashDocumentQueryCriteria $criteria
     * @return int
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Exception
     */
    public function total($type, CashDocumentQueryCriteria $criteria)
    {
        if (!in_array($type, ['in', 'out'])) {
            throw new \Exception('Invalid type');
        }

        $criteria->skip = null;
        $criteria->limit = null;
        $qb = $this->queryCriteriaToBuilder($criteria);

        if ($type == 'in') {
            $qb->field('operation')->notIn(['out', 'fee']);
        } else {
            $qb->field('operation')->in(['out', 'fee']);
        }

        $aggregate = [
            ['$match' => $qb->getQuery()->getQuery()['query']],
            ['$project' => ['total' => 1]],
            ['$group' => [
                '_id' => null, 'total' => ['$sum' => '$total']]
            ]
        ];
        $result = $this->dm->getDocumentCollection(CashDocument::class)->aggregate($aggregate);
        $total = $result[0]['total'];

        /*$qb
            ->map('function() { emit(1, this.total); }')
            ->reduce('function(k, vals) {
                    var sum = 0;
                    for (var i in vals) {
                        sum += vals[i];
                    }
                    return sum;
            }');
        $result = $qb->getQuery()->execute();
        $total = (isset($result[0]['value'])) ? $result[0]['value'] : 0;
        */

        return $total;
    }

    /**
     * @param CashDocumentQueryCriteria $criteria
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     *

     */
    private function queryCriteriaToBuilder(CashDocumentQueryCriteria $criteria)
    {
        $qb = $this->createQueryBuilder();
        $dm = $this->getDocumentManager();

        if ($criteria->skip) {
            $qb->skip($criteria->skip);
        }

        if ($criteria->limit) {
            $qb->limit($criteria->limit);
        }

        if (isset($criteria->sortBy) && isset($criteria->sortDirection) && is_array($criteria->sortBy) && is_array($criteria->sortDirection)) {
            foreach ($criteria->sortBy as $k => $v) {
                $qb->sort($v, isset($criteria->sortDirection[$k]) ? $criteria->sortDirection[$k] : 1);
            }
        } else {
            $qb->sort('createdAt', -1);
        }

        if ($criteria->methods) {
            $qb->field('method')->in($criteria->methods);
        }

        if ($criteria->article) {
            $ids = Helper::toIds($criteria->article->getChildren());
            $ids[] = $criteria->article->getId();
            foreach ($criteria->article->getChildren() as $child) {
                $ids = array_merge($ids, Helper::toIds($child->getChildren()));
            }

            $qb->field('article.id')->in($ids);
        }

        if ($criteria->createdBy) {
            $qb->field('createdBy')->equals($criteria->createdBy);
        }

        if ($criteria->isPaid) {
            $qb->field('isPaid')->equals(true);
        }

        if ($criteria->search) {
            $qb->addOr($qb->expr()->field('total')->equals((int)$criteria->search));
            $qb->addOr($qb->expr()->field('prefix')->equals(new \MongoRegex('/.*' . $criteria->search . '.*/ui')));
            $qb->addOr($qb->expr()->field('number')->equals(new \MongoRegex('/.*' . $criteria->search . '.*/ui')));

            if (!is_numeric($criteria->search)) {
                $tourists = $dm->getRepository('MBHPackageBundle:Tourist')
                    ->createQueryBuilder()
                    ->field('fullName')->equals(new \MongoRegex('/^.*' . $criteria->search . '.*/ui'))
                    ->getQuery()
                    ->execute();

                $touristsIds = array_map(function ($tourist) {
                    return $tourist->getId();
                }, $tourists->toArray());

                if (count($touristsIds)) {
                    $qb->addOr($qb->expr()->field('touristPayer.id')->in($touristsIds));
                }

                $organizations = $dm->getRepository('MBHPackageBundle:Organization')
                    ->createQueryBuilder()
                    ->field('name')->equals(new \MongoRegex('/^.*' . $criteria->search . '.*/ui'))
                    ->getQuery()
                    ->execute();

                $organizationsIds = array_map(function ($organization) {
                    return $organization->getId();
                }, $organizations->toArray());

                if (count($organizationsIds)) {
                    $qb->addOr($qb->expr()->field('organizationPayer.id')->in($organizationsIds));
                }
            }
        }

        if (isset($criteria->isConfirmed)) {
            $qb->field('isConfirmed')->equals($criteria->isConfirmed);
        }

        if ($criteria->begin) {
            $qb->field($criteria->filterByRange)->gte($criteria->begin);
        }

        if ($criteria->end) {
            $qb->field($criteria->filterByRange)->lte($criteria->end);
        }

        if ($criteria->type == CashDocumentQueryCriteria::TYPE_BY_ORDER) {
            if ($criteria->orderIds) {
                $qb->field('order.id')->in($criteria->orderIds);
            } else {
                $qb->field('order.id')->exists(true);
            }
        } elseif ($criteria->type == CashDocumentQueryCriteria::TYPE_BY_OTHER) {
            $qb->field('order')->exists(false);
        }

        if ($criteria->deleted && $this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }

        return $qb;
    }

    /**
     * @param CashDocument $document
     * @return string
     *

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
     * @param CashDocumentQueryCriteria $criteria
     * @param bool $byDays
     * @return CashDocument[]|array
     *

     */
    public function findByCriteria(CashDocumentQueryCriteria $criteria, $byDays = false)
    {
        $qb = $this->queryCriteriaToBuilder($criteria);

        if ($byDays) {
            return $this->getByDays($qb);
        }

        return $qb->getQuery()->execute();//->toArray();
    }

    /**
     * @param Builder $builder
     * @return array
     *

     */
    public function getByDays(Builder $builder)
    {
        return $builder
            ->field('paidDate')->type(9)
            ->group(['paidDate' => 1], [
                'totalIn' => 0,
                'totalOut' => 0,
                'confirmedTotalIn' => 0,
                'confirmedTotalOut' => 0,
                'noConfirmedTotalIn' => 0,
                'noConfirmedTotalOut' => 0,
                'countIn' => 0,
                'countOut' => 0
            ])
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
