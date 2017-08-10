<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ExtendedAbstractChannelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractResponseHandler;

class Expedia extends ExtendedAbstractChannelManager
{
    const CONFIG = 'ExpediaConfig';
    const UNAVAIBLE_PRICES = [
    ];

    const UNAVAIBLE_RESTRICTIONS = [
        'minStayArrival' => null,
        'maxStayArrival' => null,
        'minBeforeArrival' => null,
        'maxBeforeArrival' => null,
        'maxGuest' => null,
        'minGuest' => null
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->requestFormatter = $container->get('mbh.channelmanager.expedia_request_formatter');
        $this->requestDataFormatter = $container->get('mbh.channelmanager.expedia_request_data_formatter');
    }

    public function safeConfigDataAndGetErrorMessage(ExpediaConfig $config)
    {
        $requestInfo = $this->requestFormatter->formatGetHotelInfoRequest($config);
        $jsonResponse = $this->sendRequestAndGetResponse($requestInfo);
        $responseHandler = $this->getResponseHandler($jsonResponse);
        if ($responseHandler->isResponseCorrect()) {
            return '';
        }

        return $responseHandler->getErrorMessage();
    }

    protected function getResponseHandler($response, $config = null): AbstractResponseHandler
    {
        return $this->container->get('mbh.channelmanager.expedia_response_handler')->setInitData($response, $config);
    }

    protected function notifyServiceAboutReservation(AbstractOrderInfo $orderInfo, $config)
    {
        /** @var ExpediaOrderInfo $orderInfo */
        $requestData = $this->requestDataFormatter->formatNotifyServiceData($orderInfo, $config);
        $requestInfo = $this->requestFormatter->formatBookingConfirmationRequest($requestData);

        $response = $this->sendRequestAndGetResponse($requestInfo);
        $responseHandler = $this->getResponseHandler($response);

        if (!$responseHandler->isResponseCorrect()) {
            $this->notifyError($orderInfo->getChannelManagerName(),
                $this->container->get('translator')->trans('services.expedia.booking_notification.error') . ' #'
                . $orderInfo->getChannelManagerOrderId() . ' ' . $orderInfo->getPayer()->getName());
        }
    }

}