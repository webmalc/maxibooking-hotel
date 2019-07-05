<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $cacheDocuments = $this->dm->getRepository(CashDocument::class)->findBy(
            ['order.$id' => $order->getId(), 'isPaid' => true]
        );

        $price = 0;
        /** @var CashDocument $cacheDocument */
        foreach ($cacheDocuments as $cacheDocument) {
            if (in_array($cacheDocument->getMethod(), $methods, true)) {
                $price += $cacheDocument->getTotal();
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
        $cacheDocuments = $this->dm->getRepository(CashDocument::class)
            ->createQueryBuilder()
            ->field('order.id')->equals($order->getId())
            ->field('isPaid')->equals(true)
            ->field('paidDate')->exists(true)
            ->sort('paidDate', 'asc')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        if (\count($cacheDocuments) > 1) {
            return array_shift($cacheDocuments)['total'];
        }

        return null;
    }
}
