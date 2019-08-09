<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order;

class TemplatePricesGenerator
{
    /**@var DocumentManager */
    private $dm;

    /**
     * TemplatePricesGenerator constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param Order $order
     * @param array<string> $methods
     * @return float|null
     */
    public function getPriceByMethod(Order $order, array $methods): ?float
    {
        $cashDocuments = $this->dm->getRepository(CashDocument::class)->findBy(
            ['order.$id' => $order->getId(), 'isPaid' => true]
        );

        $price = 0;
        /** @var CashDocument $cacheDocument */
        foreach ($cashDocuments as $cashDocument) {
            if (in_array($cashDocument->getMethod(), $methods, true)) {
                $price += $cashDocument->getTotal();
            }
        }

        return $price === 0 ? null : $price;
    }

    /**
     * @param Order $order
     * @return float|null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getPrepayment(Order $order): ?float
    {
        $cashDocuments = $this->getPaidCashDocumentsByOrder($order);

        if (\count($cashDocuments) > 1) {
            return array_shift($cashDocuments)['total'];
        }

        return null;
    }

    /**
     * @param Order $order
     * @return float|null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getSurcharge(Order $order): ?float
    {
        $cashDocuments = $this->getPaidCashDocumentsByOrder($order);

        $price = 0;
        if (\count($cashDocuments) > 1) {
            array_shift($cashDocuments);
            /** @var CashDocument $cacheDocument */
            foreach ($cashDocuments as $cashDocument) {
                $price += $cashDocument['total'];
            }

            return $price === 0 ? null : $price;
        }

        return null;
    }

    /**
     * @param Order $order
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function getPaidCashDocumentsByOrder(Order $order): array
    {
        return $this->dm->getRepository(CashDocument::class)
            ->createQueryBuilder()
            ->field('order.id')->equals($order->getId())
            ->field('isPaid')->equals(true)
            ->field('paidDate')->exists(true)
            ->sort('paidDate', 'asc')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();
    }
}
