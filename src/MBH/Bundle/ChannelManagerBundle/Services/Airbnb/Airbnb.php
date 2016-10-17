<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Response;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Request;

class Airbnb extends AbstractChannelManagerService
{
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        // TODO: Implement updatePrices() method.
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            /** @var AirbnbRequestFormatter $requestFormatter */
            $requestFormatter = $this->container->get('mbh.channelmanager.airbnb_request_formatter')->setInitData($config);

            /** @var AirbnbConfig $config */
            //$roomTypes array[roomTypeId => [roomId('syncId'), roomType('doc')]]
            $roomTypes = $this->getRoomTypes($config);
            //array[roomTypeId][tariffId][date('d.m.Y') => RoomCache]
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                null,
                true
            );

            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                    $roomQuotaForCurrentDate = 0;
                    /** @var \DateTime $day */
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        /** @var RoomCache $currentDateRoomCache */
                        $currentDateRoomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $roomQuotaForCurrentDate = $currentDateRoomCache->getLeftRooms() > 0 ? $currentDateRoomCache->getLeftRooms() : 0;
                    }
                    $requestFormatter->addSingleParamCondition($day, $requestFormatter::QUOTA, $roomTypeInfo['syncId'], $roomQuotaForCurrentDate);
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest();
            $sendResult = $this->send(static::BASE_URL, $request, null, true);

            $result = $this->checkResponse($sendResult);

            $this->log($sendResult);
        }
        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        // TODO: Implement updateRestrictions() method.
    }

    /**
     * Create packages from service request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throw \Exception
     */
    public function createPackages()
    {
        // TODO: Implement createPackages() method.
    }

    /**
     * Pull orders from service server
     * @return mixed
     */
    public function pullOrders()
    {
        // TODO: Implement pullOrders() method.
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $requestInfo = $this->getRequestFormatter($config)->formatGetListingsRequests();
        $response = $this->sendRequestAndGetJsonResponse($requestInfo);

        $rooms = [];
        if ($response['metadata']['listing_count'] > 0) {
            foreach ($response['listings'] as $roomType) {
                $rooms[$roomType['id']] = $roomType['listing_descriptions'][0]['name'];
            }
        }

        return $rooms;
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement pullTariffs() method.
    }

    /**
     * Check response from booking service
     * @param mixed $response
     * @param array $params
     * @return boolean
     */
    public function checkResponse($response, array $params = null)
    {
        if (!$response) {
            return false;
        }
        if (isset($response['error_code'])) {
            return false;
        }
        return true;
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement closeForConfig() method.
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request)
    {
        // TODO: Implement pushResponse() method.
    }


    public function safeConfigDataAndGetErrorMessage($email, $password, AirbnbConfig $config)
    {
        $errorMessage = $this->setAccessTokenAndGetErrorMessage($email, $password, $config);
        if ($errorMessage !== '') {
            return $errorMessage;
        }
        $errorMessage = $this->setUserIdAndGetErrorMessage($config);
        if ($errorMessage !== '') {
            return $errorMessage;
        }
        return '';
    }

    private function setAccessTokenAndGetErrorMessage($email, $password, AirbnbConfig $config)
    {
        $requestInfo = $this->getRequestFormatter($config)->formatAuthorizeRequest($email, $password);
        $response = $this->sendRequestAndGetJsonResponse($requestInfo);
        if (!$this->checkResponse($response)) {
            return $this->getResponseErrorMessage($response);
        }
        $config->setAccessToken($response['access_token']);
        return '';
    }

    public function testRequest()
    {
        $param = $this->send('https://api.airbnb.com/v1/authorize', ['password' => '44834631TRye2009', 'client_id' => '3092nxybyb0otqw18e8nh5nty', 'username'=> 'faainttt@gmail.com'], null, true, 'POST');
        $ar = 123;
        return $param;
    }

    private function setUserIdAndGetErrorMessage(AirbnbConfig $config) : string
    {
        $requestInfo = $this->getRequestFormatter($config)->formatGetUserInfoRequest();
        $response = $this->sendRequestAndGetJsonResponse($requestInfo);

        if (!$this->checkResponse($response)) {
            return $this->getResponseErrorMessage($response);
        }
        $config->setHotelId($response['user']['user']['id']);

        return '';
    }

    /**
     * @param $config
     * @return AirbnbRequestFormatter $this
     */
    private function getRequestFormatter($config)
    {
        return $this->container->get('mbh.channelmanager.airbnb_request_formatter')->setInitData($config);
    }

    private function sendRequestAndGetJsonResponse(RequestInfo $requestInfo)
    {
        $jsonResponse = $this->send(
            $requestInfo->getUrl(),
            $requestInfo->getRequestData(),
            $requestInfo->getHeadersList(),
            true,
            $requestInfo->getMethodName());

        return json_decode($jsonResponse, true);
    }

    private function getResponseErrorMessage($response)
    {
        return $response['error_message'];
    }
}