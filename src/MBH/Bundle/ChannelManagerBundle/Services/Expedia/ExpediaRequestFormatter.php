<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;

class ExpediaRequestFormatter extends AbstractRequestFormatter
{
    //TODO: Уточнить URL
    const BASE_URL = 'https://services.expediapartnercentral.com/eqc/ar';
    const PRODUCT_API_BASE_URL = 'https://services.expediapartnercentral.com/products/properties/';

    public function formatUpdatePricesRequest($priceData)
    {
        return $this->getXMLRequestInfo($priceData);
    }

    public function formatUpdateRestrictionsRequest($restrictionData)
    {
        return $this->getXMLRequestInfo($restrictionData);
    }

    public function formatUpdateRoomsRequest($roomData)
    {
        return $this->getXMLRequestInfo($roomData);
    }

    public function formatGetOrdersRequest()
    {
        
    }

    public function formatGetHotelInfoRequest($username, $password)
    {
        $requestInfo = new RequestInfo();
        $requestInfo
            ->setUrl(self::PRODUCT_API_BASE_URL)
            ->addHeader('Authorization', 'Basic ' . base64_encode("$username: $password"))
        ;
        return $requestInfo;
    }

    /**
     * @param $requestData
     * @return RequestInfo
     */
    private function getXMLRequestInfo($requestData)
    {
        return (new RequestInfo())
            ->setUrl(self::BASE_URL)
            ->setMethodName(RequestInfo::POST_METHOD_NAME)
            ->setRequestData($requestData)
            ->addHeader("Content-Type", 'text/xml');
    }

    public function formatPullRoomsRequest(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatPullRoomsRequest() method.
    }

    public function formatPullTariffsRequest(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatPullTariffsRequest() method.
    }

    public function formatCloseForConfigRequest(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatCloseForConfigRequest() method.
    }
}