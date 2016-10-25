<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\ExtendedAbstractChannelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Expedia extends ExtendedAbstractChannelManager
{
    const CONFIG = 'ExpediaConfig';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->requestFormatter = $container->get('mbh.channelmanager.expedia_request_formatter');
        $this->responseHandler = $container->get('mbh.channelmanager.expedia_response_handler');
        $this->requestDataFormatter = $container->get('mbh.channelmanager.expedia_request_data_formatter');
    }
}