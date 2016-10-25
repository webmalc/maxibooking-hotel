<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\ChannelManagerBundle\Lib\Response;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Request;

//TODO: Сменить название
class ExtendedAbstractChannelManager extends AbstractChannelManagerService
{

    /** @var AbstractRequestFormatter $requestFormatter */
    protected $requestFormatter;
    /** @var AbstractResponseHandler $responseHandler */
    protected $responseHandler;
    /** @var AbstractRequestDataFormatter $requestDataFormatter */
    protected $requestDataFormatter;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            $serviceTariffs = $this->pullTariffs($config);
            $pricesData = $this->requestDataFormatter->getPriceData($begin, $end, $roomType, $serviceTariffs, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdatePricesRequest($pricesData);

            foreach ($requestInfoArray as $requestInfo) {
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);

                $this->log($sendResult);
            }
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
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomsData = $this->requestDataFormatter->getRoomData($begin, $end, $roomType, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdateRoomsRequest($roomsData);

            foreach ($requestInfoArray as $requestInfo) {
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);

                $this->log($sendResult);
            }
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
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            $serviceTariffs = $this->pullTariffs($config);
            $restrictionsData = $this->requestDataFormatter->getRestrictionData($begin, $end, $roomType, $serviceTariffs, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdateRestrictionsRequest($restrictionsData);

            foreach ($requestInfoArray as $requestInfo) {
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);

                $this->log($sendResult);
            }
        }

        return $result;
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
        // TODO: Implement pullRooms() method.
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
        return $this->responseHandler->isResponseCorrect($response);
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

    private function sendRequestAndGetResponse(RequestInfo $requestInfo)
    {
        return $this->send(
            $requestInfo->getUrl(),
            $requestInfo->getRequestData(),
            $requestInfo->getHeadersList(),
            true,
            $requestInfo->getMethodName()
        );
    }
}