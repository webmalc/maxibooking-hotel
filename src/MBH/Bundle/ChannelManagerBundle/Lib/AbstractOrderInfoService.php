<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\PackageBundle\Document\Tourist;

abstract class AbstractOrderInfoService
{
    abstract public function getPayer() : Tourist;
    abstract public function getChannelManagerOrderId();
    abstract public function getPrice();
    abstract public function getCashDocuments();
    abstract public function getPackagesData();
    abstract public function getServices();
    abstract public function getChannelManagerDisplayedName();
    abstract public function isOrderModified();
    abstract public function isOrderCreated();
    abstract public function isOrderCancelled();

    public function getOriginalPrice()
    {
        return $this->getPrice();
    }
}