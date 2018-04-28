<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Order;

class OrderSerialize extends CommonSerialize
{
    /**
     * @var Order
     */
    protected $entity;

    public function __construct(Order $order)
    {
        $this->entity = $order;
    }

    public function getPrice(): string
    {
        return $this->entity->getPrice() !== null ? number_format($this->entity->getPrice(), 2) : '';
    }
}