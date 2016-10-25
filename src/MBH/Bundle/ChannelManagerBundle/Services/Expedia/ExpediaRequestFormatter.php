<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestFormatter;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;

class ExpediaRequestFormatter extends AbstractRequestFormatter
{
    /** @var  RequestInfo $requestInfo */
    private $requestInfo;
    const BASE_URL = 'https://services.expediapartnercentral.com/eqc/ar';

    public function formatUpdatePricesRequest($pricePeriods)
    {
        $this->requestInfo
            ->setMethodName(RequestInfo::POST_METHOD_NAME)
            ->setUrl(self::BASE_URL);
        //etc

    }

    public function formatUpdateRestrictionsRequest($restrictionPeriods)
    {
        // TODO: Implement formatUpdateRestrictionsRequest() method.
    }

    public function formatUpdateRoomsRequest($roomPeriods)
    {
        // TODO: Implement formatUpdateRoomsRequest() method.
    }
}