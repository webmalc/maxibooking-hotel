<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;

class OrderSerialize extends CommonSerialize
{
    public function __construct(Order $order)
    {
        $this->entity = $order;
    }

    public function getPrice(): string
    {
        return $this->entity->getPrice() !== null ? Helper::numFormat($this->entity->getPrice()) : '';
    }

    public function allCashDocuments():array
    {
        $return = [];
        foreach ($this->entity->getCashDocuments() as $cashDocument){
            $return[] = new CashDocumentSerialize($cashDocument);
        }
        return $return;
    }

    public function getPaidFor(): string
    {
        $amount = 0;
        /** @var CashDocument $cashDocument */
        foreach ($this->entity->getCashDocuments() as $cashDocument){
            if (in_array($cashDocument->getOperation(),['fine', 'in'])){
                $amount += $cashDocument->getTotal();
            }
        }
        return Helper::numFormat($amount);
    }
}