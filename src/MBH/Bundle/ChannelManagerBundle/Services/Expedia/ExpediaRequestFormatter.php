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

    private $expediaUsername;
    private $expediaPassword;

    public function __construct(array $channelManagersData) {
        $expediaAuthData = $channelManagersData['expedia'];
        $this->expediaPassword = $expediaAuthData['password'];
        $this->expediaUsername = $expediaAuthData['username'];
    }

    public function formatUpdatePricesRequest($pricesData) : array
    {
        $pricesRequestsInfo = [];
        foreach ($pricesData as $priceData) {
            $pricesRequestsInfo[] = $this->getXMLRequestInfo($priceData)->setUrl(self::AVAILABILITY_AND_RATES_API_URL);
        }

        return $pricesRequestsInfo;
    }

    public function formatUpdateRestrictionsRequest($restrictionsData) : array
    {
        $restrictionsRequestInfo = [];
        foreach ($restrictionsData as $restrictionData) {
            $restrictionsRequestInfo[] = $this->getXMLRequestInfo($restrictionData)
                ->setUrl(self::AVAILABILITY_AND_RATES_API_URL);
        }

        return $restrictionsRequestInfo;
    }

    public function formatUpdateRoomsRequest($roomsData) : array
    {
        $roomRequestInfo = [];
        foreach ($roomsData as $roomData) {
            $roomRequestInfo[] = $this->getXMLRequestInfo($roomData)->setUrl(self::AVAILABILITY_AND_RATES_API_URL);
        }

        return $roomRequestInfo;
    }

    public function formatGetOrdersRequest($getOrdersData)
    {
        return $this->getXMLRequestInfo($getOrdersData)
            ->setUrl(self::BOOKING_RETRIEVAL_API_URL);
    }

    public function formatGetHotelInfoRequest()
    {
        return $this->getJsonRequestInfo(self::PRODUCT_API_URL);
    }

    public function formatPullRoomsRequest(ChannelManagerConfigInterface $config)
    {
        $url = self::PRODUCT_API_URL . "{$config->getHotelId()}/roomTypes/";

        return [$this->getJsonRequestInfo($url)
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
            $url = self::PRODUCT_API_URL . "{$config->getHotelId()}/roomTypes/{$roomTypeId}/ratePlans/";
            $requestInfos[] = $this->getJsonRequestInfo($url)
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

    private function getJsonRequestInfo($url) : RequestInfo
    {
        /** @var ExpediaConfig $config */
        return (new RequestInfo())
            ->addHeader('Authorization', 'Basic ' . base64_encode("{$this->expediaUsername}:{$this->expediaPassword}"))
            ->setUrl($url);
    }
}