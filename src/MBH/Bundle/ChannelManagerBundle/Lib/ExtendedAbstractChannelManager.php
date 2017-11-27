<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Для реализация данного абстрактного класса, необходимо также реализовать:
 * 1) Класс AbstractRequestFormatter, отвечающий за формирование данных о запросах(url, заголовки, передаваемые данные)
 * 2) Класс AbstractRequestDataFormatter, отвечающий за формирование данных, передаваемых в запросе
 * 3) Класс AbstractResponseHandler, отвечающий за основную обработку приходящих ответов
 * 4) Класс AbstractOrderInfo, отвечающий за обработку переданных данных о заказах
 * 5) Класс AbstractPackageInfo, отвечающий за обработку переданных данных о бронях
 *
 * Class ExtendedAbstractChannelManager
 * @package MBH\Bundle\ChannelManagerBundle\Lib
 */
abstract class ExtendedAbstractChannelManager extends AbstractChannelManagerService
{
    /** @var AbstractRequestFormatter $requestFormatter */
    protected $requestFormatter;
    /** @var AbstractRequestDataFormatter $requestDataFormatter */
    protected $requestDataFormatter;

    abstract protected function getResponseHandler($response, $config = null): AbstractResponseHandler;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            $serviceTariffs = $this->pullTariffs($config);
            $pricesData = $this->requestDataFormatter->formatPriceRequestData($begin, $end, $roomType, $serviceTariffs, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdatePricesRequest($pricesData);
            foreach ($requestInfoArray as $requestInfo) {
                $this->log('begin update prices');
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);
                $this->log('response for update prices request:');
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
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomsData = $this->requestDataFormatter->formatRoomRequestData($begin, $end, $roomType, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdateRoomsRequest($roomsData);
            foreach ($requestInfoArray as $requestInfo) {
                $this->log('begin update rooms');
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);
                $this->log('response for update rooms request:');
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
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $serviceTariffs = $this->pullTariffs($config);
            $restrictionsData = $this->requestDataFormatter->formatRestrictionRequestData($begin, $end, $roomType, $serviceTariffs, $config);
            $requestInfoArray = $this->requestFormatter->formatUpdateRestrictionsRequest($restrictionsData);
            foreach ($requestInfoArray as $requestInfo) {
                $this->log('begin update restrictions');
                $sendResult = $this->sendRequestAndGetResponse($requestInfo);
                $result = $this->checkResponse($sendResult);
                $this->log('response for update restrictions request:');
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
        return $this->pullOrders();
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $roomTypes = [];

        //Получаем список объектов RequestInfo, содержащих данные о запросах.
        $requestInfoList = $this->requestFormatter->formatPullRoomsRequest($config);

        foreach ($requestInfoList as $requestInfo) {
            $response = $this->sendRequestAndGetResponse($requestInfo);
            $responseHandler = $this->getResponseHandler($response, $config);
            if ($responseHandler->isResponseCorrect()) {
                $roomTypesData = $responseHandler->getRoomTypesData();
                $roomTypes += $roomTypesData;
            }
        }

        return $roomTypes;
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $tariffs = [];
        $roomTypes = $this->pullRooms($config);

//        Получаем список объектов RequestInfo, содержащих данные о запросах.
        $requestInfoList = $this->requestFormatter->formatPullTariffsRequest($config, $roomTypes);

        foreach ($requestInfoList as $requestInfo) {
            $response = $this->sendRequestAndGetResponse($requestInfo);
            $responseHandler = $this->getResponseHandler($response, $config);
            $tariffsData = $responseHandler->getTariffsData($roomTypes);
            $tariffs += $tariffsData;
        }

        return $tariffs;
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
        $responseHandler = $this->getResponseHandler($response);
        $isSuccess = $responseHandler->isResponseCorrect();
        if (!$isSuccess) {
            $this->addError($responseHandler->getErrorMessage());
        }

        return $isSuccess;
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return bool
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $requestData = $this->requestDataFormatter->formatCloseForConfigData($config);
        $requestInfo = $this->requestFormatter->formatCloseForConfigRequest($requestData);
        $response = $this->sendRequestAndGetResponse($requestInfo);

        return $this->checkResponse($response);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request)
    {
        $this->log($request->getContent());

        return new Response('OK');
    }

    protected function sendRequestAndGetResponse(RequestInfo $requestInfo)
    {
        return $this->send(
            $requestInfo->getUrl(),
            $requestInfo->getRequestData(),
            $requestInfo->getHeadersList(),
            true,
            $requestInfo->getMethodName()
        );
    }

    public function pullOrders()
    {
        $result = true;

        /** @var ChannelManagerConfigInterface $config */
        foreach ($this->getConfig() as $config) {
            $this->log('begin pulling orders for hotel "' . $config->getHotel()->getName() . '" with id "' . $config->getHotel()->getId() . '"');

            $requestData = $this->requestDataFormatter->formatGetBookingsData($config);
            $request = $this->requestFormatter->formatGetOrdersRequest($requestData);

            $response = $this->sendRequestAndGetResponse($request);
            $this->log($response);
            $this->handlePullOrdersResponse($response, $config, $result);
        }

        return $result;
    }

    public function handlePullOrdersResponse($response, $config, &$result, $isFirstPulling = false)
    {
        $responseHandler = $this->getResponseHandler($response, $config);
        $orderHandler = $this->container->get('mbh.channelmanager.order_handler');
        if (!$this->checkResponse($response)) {
            $result = false;
        } else {
            foreach ($responseHandler->getOrderInfos() as $orderInfo) {
                /** @var AbstractOrderInfo $orderInfo */
                if ($orderInfo->isOrderModified()) {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }

                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => $orderInfo->getChannelManagerOrderId(),
                        'channelManagerType' => $orderInfo->getChannelManagerName(),
                    ]
                );

                if ($orderInfo->isOrderModified()) {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }

                //new
                if ($orderInfo->isHandledAsNew($order) || $isFirstPulling) {
                    $result = $orderHandler->createOrder($orderInfo, $order);
                    $this->notify($result, $orderInfo->getChannelManagerName(), 'new');
                }

                //edited
                if ($orderInfo->isHandledAsModified($order)) {
                    $result = $orderHandler->createOrder($orderInfo, $order);
                    if ($orderInfo->getModifiedDate()) {
                        $order->setChannelManagerEditDateTime($orderInfo->getModifiedDate());
                    }
                    $this->notify($result, $orderInfo->getChannelManagerName(), 'edit');
                }

                //delete
                if ($orderInfo->isHandledAsCancelled($order)) {
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, $orderInfo->getChannelManagerName(), 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;
                };

                if (($orderInfo->isOrderModified() || $orderInfo->isOrderCancelled()) && !$order && !$isFirstPulling) {
                    if ($orderInfo->isOrderModified()) {
                        $result = $orderHandler->createOrder($orderInfo, $order);
                        $this->notifyError($orderInfo->getChannelManagerName(), $this->getUnexpectedOrderError($result, true));
                    }

                    if ($orderInfo->isOrderCancelled()) {
                        $this->notifyError($orderInfo->getChannelManagerName(), $this->getUnexpectedOrderError($result, false));
                    }
                }
                $this->notifyServiceAboutReservation($orderInfo, $config);
            };
        }
    }

    /**
     * Метод, добавляющий функциональность после обработки полученной брони
     * По дефолту не используется, потому пуст
     *
     * @param AbstractOrderInfo $orderInfo
     * @param $config
     * @return null
     * @internal param $responseHandler
     */
    protected function notifyServiceAboutReservation(AbstractOrderInfo $orderInfo, $config)
    {
        return null;
    }

}