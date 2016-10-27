<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfoService;
use MBH\Bundle\ChannelManagerBundle\Lib\ExtendedAbstractChannelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractResponseHandler;

class Expedia extends ExtendedAbstractChannelManager
{
    const CONFIG = 'ExpediaConfig';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->requestFormatter = $container->get('mbh.channelmanager.expedia_request_formatter');
        $this->requestDataFormatter = $container->get('mbh.channelmanager.expedia_request_data_formatter');
    }

    public function pullOrders()
    {
        $result = true;

        foreach ($this->getConfig() as $config) {

            //TODO: Сформировать и отправить запрос
            $request = $this->requestFormatter->formatGetOrdersRequest();

            $response = $this->sendRequestAndGetResponse($request);
            $responseHandler = $this->getResponseHandler($response);

            if (!$this->checkResponse($response)) {
                $this->log($responseHandler->getErrorMessage());
                return false;
            }

            $this->log('Reservations count: ' . $responseHandler->getOrdersCount());

            //TODO: Получение данных о тарифах и типах комнат вообще желательно вынести в отдельный сервис, конечно.
            $tariffs = $this->getTariffs($config, true);
            $roomTypes = $this->getRoomTypes($config, true);

            foreach ($responseHandler->getOrderInfoArray() as $serviceOrder) {

                /** @var AbstractOrderInfoService $orderInfo */
                $orderInfo = $this->getOrderInfo($serviceOrder, $config, $tariffs, $roomTypes);

                if ($orderInfo->isOrderModified()) {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }
                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => $orderInfo->getChannelManagerOrderId(),
                        'channelManagerType' => $orderInfo->getChannelManagerDisplayedName()
                    ]
                );
                if ($orderInfo->isOrderModified()) {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }
                //new
                if ($orderInfo->isOrderCreated() && !$order) {
                    $result = $this->createOrder($orderInfo, $order);
                    $this->notify($result, $orderInfo->getChannelManagerDisplayedName(), 'new');
                }

                //edited
                //TODO: Придумать общие статусы
                if ($orderInfo->isOrderModified() && $order
                    && $order->getChannelManagerEditDateTime() != $orderInfo->getModifiedDate()) {
                    $result = $this->createOrder($orderInfo, $order);
                    $order->setChannelManagerEditDateTime($orderInfo->getModifiedDate());
                    $this->notify($result, $orderInfo->getChannelManagerDisplayedName(), 'edit');
                }

                //delete
                if ($orderInfo->isOrderCancelled() && $order) {
                    $order->setChannelManagerStatus('cancelled');
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, $orderInfo->getChannelManagerDisplayedName(), 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;
                };

                if (($orderInfo->isOrderModified() || $orderInfo->isOrderCancelled()) && !$order) {
                    $this->notifyError(
                        $orderInfo->getChannelManagerDisplayedName(),
                        '#' . $orderInfo->getChannelManagerOrderId() . ' ' . $orderInfo->getPayer()->getName()
                    );
                }
            };
        }

        return $result;
    }

    public function safeConfigDataAndGetErrorMessage($username, $password, ExpediaConfig $config)
    {
        $requestInfo = $this->requestFormatter->formatGetHotelInfoRequest($username, $password);
        $jsonResponse = $this->sendRequestAndGetResponse($requestInfo);
        $response = json_decode($jsonResponse, true);
        if (isset($response['errors'])) {
            if ($response['errors']['code'] == 1001) {
                return 'services.expedia.invalid_authorization_data';
            } else {
                return $response['errors']['message'];
            }
        }
        $hotelId = $response['entity']['resourceId'];
        $config->setHotelId($hotelId);
        return '';
    }
    
    protected function getResponseHandler($response) : AbstractResponseHandler
    {
        return $this->container->get('mbh.channelmanager.expedia_response_handler')->setInitData($response);
    }

    protected function getOrderInfo($serviceOrder, $config, $tariffs, $roomTypes) : AbstractOrderInfoService
    {
        $this->container->get('mbh.channelmanager.expedia_order_info')
            ->setInitData($serviceOrder, $config, $tariffs, $roomTypes);
    }


}