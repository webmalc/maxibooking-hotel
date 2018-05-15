<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order as OrderBase;

/**
 * Class Order
 *
 * @property OrderBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class Order extends Common
{
    public function getPrice(): string
    {
        return $this->entity->getPrice() !== null ? Helper::numFormat($this->entity->getPrice()) : '';
    }

    public function allCashDocuments(): array
    {
        $return = [];
        $cashDocumentSerialize = $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\CashDocument');
        foreach ($this->entity->getCashDocuments() as $cashDocument) {
            $return[] = (clone $cashDocumentSerialize)->newInstance($cashDocument);
        }

        return $return;
    }

    public function getPaidFor(): string
    {
        $amount = 0;
        /** @var CashDocument $cashDocument */
        foreach ($this->entity->getCashDocuments() as $cashDocument) {
            if (in_array($cashDocument->getOperation(), ['fine', 'in'])) {
                $amount += $cashDocument->getTotal();
            }
        }

        return Helper::numFormat($amount);
    }
}