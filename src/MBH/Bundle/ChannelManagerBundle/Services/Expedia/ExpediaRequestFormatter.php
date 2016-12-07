<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;

class ExpediaRequestFormatter extends AbstractRequestFormatter
{
    const AVAILABILITY_AND_RATES_API_URL = 'https://services.expediapartnercentral.com/eqc/ar';
    const BOOKING_RETRIEVAL_API_URL = 'https://services.expediapartnercentral.com/eqc/br';
    const BOOKING_CONFIRMATION_API_URL = 'https://services.expediapartnercentral.com/eqc/bc';
    const PRODUCT_API_URL = 'https://services.expediapartnercentral.com/products/properties/';

    public function formatUpdatePricesRequest($priceData)
    {
        return [$this->getXMLRequestInfo($priceData)
            ->setUrl(self::AVAILABILITY_AND_RATES_API_URL)];
    }

    public function formatUpdateRestrictionsRequest($restrictionData)
    {
        return $this->getXMLRequestInfo($restrictionData)
            ->setUrl(self::AVAILABILITY_AND_RATES_API_URL);
    }

    public function formatUpdateRoomsRequest($roomData)
    {
        return $this->getXMLRequestInfo($roomData)
            ->setUrl(self::AVAILABILITY_AND_RATES_API_URL);
    }

    public function formatGetOrdersRequest($getOrdersData)
    {
        return $this->getXMLRequestInfo($getOrdersData)
            ->setUrl(self::BOOKING_RETRIEVAL_API_URL);
    }

    public function formatGetHotelInfoRequest(ChannelManagerConfigInterface $config)
    {
        $url = self::PRODUCT_API_URL;

        return $this->getJsonRequestInfo($config, $url);
    }

    public function formatPullRoomsRequest(ChannelManagerConfigInterface $config)
    {
        $url = self::PRODUCT_API_URL . "/{$config->getHotelId()}/roomTypes/";

        return [$this->getJsonRequestInfo($config, $url)
            ->addHeader('limit', 200)];
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
            $url = self::PRODUCT_API_URL . "/{$config->getHotelId()}/roomTypes/{$roomTypeId}/ratePlans/";
            $requestInfos[] = $this->getJsonRequestInfo($config, $url)
                ->addHeader('limit', 200);
        }

        return $requestInfos;
    }

    public function formatCloseForConfigRequest($requestData)
    {
        return $this->getXMLRequestInfo($requestData)
            ->setUrl(self::AVAILABILITY_AND_RATES_API_URL);
    }

    public function formatBookingConfirmationRequest($requestData)
    {
        return $this->getXMLRequestInfo($requestData)
            ->setUrl(self::BOOKING_CONFIRMATION_API_URL);
    }

    /**
     * @param $requestData
     * @return RequestInfo
     */
    private function getXMLRequestInfo($requestData)
    {
        return (new RequestInfo())
            ->setMethodName(RequestInfo::POST_METHOD_NAME)
            ->setRequestData($requestData)
            ->addHeader("Content-Type", 'text/xml');
    }

    private function getJsonRequestInfo(ChannelManagerConfigInterface $config, $url) : RequestInfo
    {
        /** @var ExpediaConfig $config */
        return (new RequestInfo())
            ->addHeader('Authorization', 'Basic ' . base64_encode("{$config->getUsername()}:{$config->getPassword()}"))
            ->setUrl($url);
    }
}