<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;


abstract class AbstractRequestFormatter
{
    abstract public function formatUpdatePricesRequest($pricePeriods);
    abstract public function formatUpdateRestrictionsRequest($restrictionPeriods);
    abstract public function formatUpdateRoomsRequest($roomPeriods);
    abstract public function formatPullRoomsRequest(ChannelManagerConfigInterface $config);
    abstract public function formatPullTariffsRequest(ChannelManagerConfigInterface $config, $roomTypesData = []);
    abstract public function formatCloseForConfigRequest($requestData);
    abstract public function formatGetOrdersRequest($getOrdersData);
}