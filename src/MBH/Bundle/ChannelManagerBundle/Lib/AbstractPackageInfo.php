<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;


abstract class AbstractPackageInfo
{
    abstract public function getBeginDate();
    abstract public function getEndDate();
    abstract public function getRoomType();
    abstract public function getTariff();
    abstract public function getAdultsCount();
    abstract public function getChildrenCount();
    abstract public function getPrices();
    abstract public function getPrice();
    abstract public function getNote();
    abstract public function getIsCorrupted();
    abstract public function getTourists();
    abstract public function getIsSmoking();

    public function getOriginalPrice()
    {
        return $this->getPrice();
    }
}