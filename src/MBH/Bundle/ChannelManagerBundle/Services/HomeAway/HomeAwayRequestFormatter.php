<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class HomeAwayRequestFormatter extends AbstractRequestFormatter
{

    public function formatUpdatePricesRequest($pricePeriods) : array
    {
        // TODO: Implement formatUpdatePricesRequest() method.
    }

    public function formatUpdateRestrictionsRequest($restrictionPeriods) : array
    {
        // TODO: Implement formatUpdateRestrictionsRequest() method.
    }

    public function formatUpdateRoomsRequest($roomPeriods) : array
    {
        // TODO: Implement formatUpdateRoomsRequest() method.
    }

    public function formatPullRoomsRequest(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatPullRoomsRequest() method.
    }

    public function formatPullTariffsRequest(ChannelManagerConfigInterface $config, $roomTypesData = [])
    {
        // TODO: Implement formatPullTariffsRequest() method.
    }

    public function formatCloseForConfigRequest($requestData)
    {
        // TODO: Implement formatCloseForConfigRequest() method.
    }

    public function formatGetOrdersRequest($getOrdersData)
    {
        // TODO: Implement formatGetOrdersRequest() method.
    }
}