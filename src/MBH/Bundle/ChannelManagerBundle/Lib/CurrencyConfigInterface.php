<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;


interface CurrencyConfigInterface
{
    public function getCurrency();

    public function getCurrencyDefaultRatio();

}