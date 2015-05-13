<?php

namespace MBH\Bundle\CashBundle\Document;

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
     * @return int
     * @throws \Exception
     */
    public function total($type, $search, \DateTime $begin = null, \DateTime $end = null)
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

        if (!empty($begin)) {
            $qb->field('createdAt')->gte($begin);
        }

        if (!empty($end)) {
            $qb->field('createdAt')->lte($end);
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

        return $document->getOrder()->getId().'-'. (++$number);
    }
}
