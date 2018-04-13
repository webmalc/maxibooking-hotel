<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;

abstract class AbstractRequestFormatter
{
    /**
     * @param $pricesPeriods
     * @return array|RequestInfo[]
     */
    abstract public function formatUpdatePricesRequest($pricesPeriods) : array;

    /**
     * @param $restrictionPeriods
     * @return array|RequestInfo[]
     */
    abstract public function formatUpdateRestrictionsRequest($restrictionPeriods) : array;

    /**
     * @param $roomPeriods
     * @return array|RequestInfo[]
     */
    abstract public function formatUpdateRoomsRequest($roomPeriods) : array;
    abstract public function formatPullRoomsRequest(ChannelManagerConfigInterface $config);
    abstract public function formatPullTariffsRequest(ChannelManagerConfigInterface $config, $roomTypesData = []);
    abstract public function formatCloseForConfigRequest($requestData);
    abstract public function formatGetOrdersRequest($getOrdersData);
}