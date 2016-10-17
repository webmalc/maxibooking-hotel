<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;

class AirbnbRequestFormatter
{
    //TODO: Откуда берется client_id?
    const API_KEY_PARAMETER_NAME = 'client_id';
    const API_KEY = '3092nxybyb0otqw18e8nh5nty';
    const BASE_URL = 'https://api.airbnb.com/';
    const AUTH_TOKEN_HEADER_NAME = 'X-Airbnb-OAuth-Token';
    const USER_ID_PARAMETER_NAME = 'user_id';
    const HAS_AVAILABILITY_PARAMETER_NAME = 'has_availability';


    /** @var  AirbnbConfig */
    private $config;

    public function __construct()
    {

    }

    public function setInitData(ChannelManagerConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    public function formatAuthorizeRequest($username, $password)
    {
        $requestInfo = new RequestInfo();
        $requestInfo->setUrl(self::BASE_URL . 'v1/authorize');
        $requestInfo->addRequestParameter('username', $username);
        $requestInfo->addRequestParameter('password', $password);
        $requestInfo->addRequestParameter(self::API_KEY_PARAMETER_NAME, self::API_KEY);
        $requestInfo->setMethodName(RequestInfo::POST_METHOD_NAME);

        return $requestInfo;
    }

    public function formatGetUserInfoRequest()
    {
        $requestInfo = new RequestInfo();
        $requestInfo->setUrl(self::BASE_URL . 'v1/account/active');
        $requestInfo->addHeader(self::AUTH_TOKEN_HEADER_NAME, $this->config->getAccessToken());
        $requestInfo->addRequestParameter(self::API_KEY_PARAMETER_NAME, self::API_KEY);
        return $requestInfo;
    }

    public function formatGetListingsRequests()
    {
        $requestInfo = new RequestInfo();
        $requestInfo->setUrl(self::BASE_URL . 'v2/listings');
        $requestInfo->addHeader(self::AUTH_TOKEN_HEADER_NAME, $this->config->getAccessToken());
        $requestInfo->addRequestParameter(self::USER_ID_PARAMETER_NAME, $this->config->getHotelId());
        $requestInfo->addRequestParameter(self::HAS_AVAILABILITY_PARAMETER_NAME, 'false');

        return $requestInfo;
    }

}