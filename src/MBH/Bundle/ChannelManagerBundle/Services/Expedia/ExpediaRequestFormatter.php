<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;

class ExpediaRequestFormatter extends AbstractRequestFormatter
{
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

    public function formatGetOrdersRequest($getOrdersData)
    {
        return $this->getXMLRequestInfo($getOrdersData);
    }

    public function formatGetHotelInfoRequest(ChannelManagerConfigInterface $config)
    {
        $url = self::PRODUCT_API_BASE_URL;

        return $this->getJsonRequestInfo($config, $url);
    }

    public function formatPullRoomsRequest(ChannelManagerConfigInterface $config)
    {
        $url = self::PRODUCT_API_BASE_URL . "/{$config->getHotelId()}/roomTypes/";

        return $this->getJsonRequestInfo($config, $url);
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param array $roomTypesData
     * @return \MBH\Bundle\ChannelManagerBundle\Model\RequestInfo[]
     */
    public function formatPullTariffsRequest(ChannelManagerConfigInterface $config, $roomTypesData = [])
    {
        $requestInfos = [];
        foreach ($roomTypesData as $roomTypeId => $roomTypeData) {
            $url = self::PRODUCT_API_BASE_URL . "/{$config->getHotelId()}/roomTypes/{$roomTypeId}/ratePlans/";
            $requestInfos[] = $this->getJsonRequestInfo($config, $url);
        }

        return $requestInfos;
    }

    public function formatCloseForConfigRequest($requestData)
    {
        return $this->getXMLRequestInfo($requestData);
    }

    public function formatNotifyServiceRequest($requestData)
    {
        return $this->getXMLRequestInfo($requestData);
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

    private function getJsonRequestInfo(ChannelManagerConfigInterface $config, $url) : RequestInfo
    {
        return (new RequestInfo())
            ->addHeader('Authorization', 'Basic ' . base64_encode("{$config->getUsername()}:{$config->getPassword()}"))
            ->setUrl($url);
    }
}