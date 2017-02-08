<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;

class HomeAwayOrderInfo extends AbstractOrderInfo
{
    /** @var  \SimpleXMLElement $bookingData */
    private $bookingData;

    public function setInitData(\SimpleXMLElement $bookingData)
    {
        $this->bookingData = $bookingData;
    }

    public function getPayer() : Tourist
    {
        // TODO: Implement getPayer() method.
    }

    public function getChannelManagerOrderId() : string
    {
        // TODO: Implement getChannelManagerOrderId() method.
    }

    public function getPrice()
    {
        // TODO: Implement getPrice() method.
    }

    public function getCashDocuments(Order $order)
    {
        // TODO: Implement getCashDocuments() method.
    }

    public function getSource() : ?PackageSource
    {
        // TODO: Implement getSource() method.
    }

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    public function getPackagesData()
    {
        // TODO: Implement getPackagesData() method.
    }

    /**
     * @return PackageService[]
     */
    public function getServices()
    {
        // TODO: Implement getServices() method.
    }

    /**
     * Возвращает данные о кредитной карте, если указаны.
     * @return CreditCard|null
     */
    public function getCreditCard()
    {
        // TODO: Implement getCreditCard() method.
    }

    public function getChannelManagerName() : string
    {
        // TODO: Implement getChannelManagerName() method.
    }

    public function getChannelManagerDisplayedName() : string
    {
        // TODO: Implement getChannelManagerDisplayedName() method.
    }

    public function isOrderModified() : bool
    {
        // TODO: Implement isOrderModified() method.
    }

    public function isOrderCreated() : bool
    {
        // TODO: Implement isOrderCreated() method.
    }

    public function isOrderCancelled() : bool
    {
        // TODO: Implement isOrderCancelled() method.
    }

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsNew(?Order $order) : bool
    {
        // TODO: Implement isHandleAsNew() method.
    }

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsModified(?Order $order) : bool
    {
        // TODO: Implement isHandleAsModified() method.
    }

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    public function isHandleAsCancelled(?Order $order) : bool
    {
        // TODO: Implement isHandleAsCancelled() method.
    }

    public function getNote() : string
    {
        // TODO: Implement getNote() method.
    }
}