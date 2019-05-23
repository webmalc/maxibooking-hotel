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

    /**@var ContainerInterface */
    private $container;

    /**
     * TemplatePricesGenerator constructor.
     * @param DocumentManager $dm
     * @param ContainerInterface $container
     */
    public function __construct(DocumentManager $dm, ContainerInterface $container)
    {
        $this->dm = $dm;
        $this->container = $container;
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
            if (in_array($cacheDocument->getMethod(), $methods)) {
                $price += $cacheDocument->getTotal();
            }
        }

        return $price === 0 ? null : $price;
    }
}