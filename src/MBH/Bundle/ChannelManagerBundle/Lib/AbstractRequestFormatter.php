<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;


abstract class AbstractRequestFormatter
{
    abstract public function formatUpdatePricesRequest($pricePeriods);
    abstract public function formatUpdateRestrictionsRequest($restrictionPeriods);
    abstract public function formatUpdateRoomsRequest($roomPeriods);
}