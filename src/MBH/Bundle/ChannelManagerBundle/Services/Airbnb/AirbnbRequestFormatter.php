<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\Airbnb\ClosedPeriod;
use MBH\Bundle\ChannelManagerBundle\Model\Airbnb\PricePeriod;
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

    public function setInitData(ChannelManagerConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    public function formatAuthorizeRequest($username, $password) : RequestInfo
    {
        $requestInfo = new RequestInfo();
        $requestInfo
            ->setUrl(self::BASE_URL . 'v1/authorize')
            ->addRequestParameter('username', $username)
            ->addRequestParameter('password', $password);
        $requestInfo = $this->addAPIKey($requestInfo);
        $requestInfo->setMethodName(RequestInfo::POST_METHOD_NAME);

        return $requestInfo;
    }

    public function formatGetUserInfoRequest() : RequestInfo
    {
        $requestInfo = new RequestInfo();
        $requestInfo->setUrl(self::BASE_URL . 'v1/account/active');
        $requestInfo = $this->addAccessToken($requestInfo);
        $requestInfo = $this->addAPIKey($requestInfo);

        return $requestInfo;
    }

    public function formatGetListingsRequests() : RequestInfo
    {
        $requestInfo = new RequestInfo();
        $requestInfo
            ->setUrl(self::BASE_URL . 'v2/listings')
            ->addRequestParameter(self::USER_ID_PARAMETER_NAME, $this->config->getHotelId())
            ->addRequestParameter(self::HAS_AVAILABILITY_PARAMETER_NAME, 'false');
        $requestInfo = $this->addAccessToken($requestInfo);

        return $requestInfo;
    }

    public function formatUpdatePricesRequest(PricePeriod $pricePeriod) : RequestInfo
    {
        $requestInfo = $this->getDailyInfoRequest($pricePeriod->getStartDate(), $pricePeriod->getEndDate(), $pricePeriod->getListingId());
        $requestInfo
            ->addRequestParameter('daily_price', $pricePeriod->getPrice())
            ->addRequestParameter('demand_based_pricing_overridden', 'true');

        return $requestInfo;
    }

    public function formatUpdateAvailabilityRequest(ClosedPeriod $closedPeriod) : RequestInfo
    {
        $requestInfo = $this->getDailyInfoRequest($closedPeriod->getStartDate(), $closedPeriod->getEndDate(), $closedPeriod->getListingId());
        $requestInfo->addRequestParameter('availability', $closedPeriod->getIsClosed() ? 'unavailable' : 'available');

        return $requestInfo;
    }

    public function formatDeactivateListingRequest($listingId) : RequestInfo
    {
        $requestInfo = new RequestInfo();
        $requestInfo
            ->setUrl(self::BASE_URL . 'v1/listings/' . $listingId . '/update')
            ->setMethodName(RequestInfo::POST_METHOD_NAME)
            ->addRequestParameter('listing', [self::HAS_AVAILABILITY_PARAMETER_NAME => 'true']);
        $requestInfo = $this->addAccessToken($requestInfo);
        $requestInfo = $this->addAPIKey($requestInfo);

        return $requestInfo;
    }

    private function getDailyInfoRequest(\DateTime $startDate, \DateTime $endDate, $listingId)
    {
        $requestInfo = new RequestInfo();
        $endDateString = $endDate->format('Y-m-d');
        $startDateString = $startDate->format('Y-m-d');
        $requestInfo
            ->setMethodName(RequestInfo::PUT_METHOD_NAME)
            ->addHeader('Content-Type', 'application/json; charset=UTF-8');
        $requestInfo = $this->addAccessToken($requestInfo);
        $requestInfo = $this->addAPIKey($requestInfo);

        $requestInfo->setUrl(self::BASE_URL . 'v2/calendars/' . $listingId . '/' . $startDateString . '/' . $endDateString);

        return $requestInfo;
    }

    private function addAccessToken(RequestInfo $requestInfo)
    {
        $requestInfo->addHeader(self::AUTH_TOKEN_HEADER_NAME, $this->config->getAccessToken());
        return $requestInfo;
    }

    private function addAPIKey(RequestInfo $requestInfo)
    {
        $requestInfo->addRequestParameter(self::API_KEY_PARAMETER_NAME, self::API_KEY);
        return $requestInfo;
    }
}