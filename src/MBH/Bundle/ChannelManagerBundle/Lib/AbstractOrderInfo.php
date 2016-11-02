<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Tourist;

abstract class AbstractOrderInfo
{
    abstract public function getPayer() : Tourist;
    abstract public function getChannelManagerOrderId();
    abstract public function getPrice();
    abstract public function getCashDocuments(Order $order);

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    abstract public function getPackagesData();
    abstract public function getServices();

    /**
     * Возвращает данные о кредитной карте, если указаны.
     * @return CreditCard|null
     */
    abstract public function getCreditCard();
    abstract public function getChannelManagerDisplayedName();
    abstract public function isOrderModified();
    abstract public function isOrderCreated();
    abstract public function isOrderCancelled();

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    abstract public function isHandleAsNew(Order $order);

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    abstract public function isHandleAsModified(Order $order);

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    abstract public function isHandleAsCancelled(Order $order);

    abstract public function getNote();

    /**
     * Возвращает время изменения заказа.
     * @return \DateTime|null
     */
    public function getModifiedDate()
    {
        return null;
    }

    public function getOriginalPrice()
    {
        return $this->getPrice();
    }
}