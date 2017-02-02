<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractResponseHandler;
use MBH\Bundle\ChannelManagerBundle\Lib\ExtendedAbstractChannelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HomeAway extends ExtendedAbstractChannelManager
{
    const CONFIG = 'HomeAwayConfig';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->requestFormatter = $container->get('mbh.channelmanager.homeaway_request_formatter');
        $this->requestDataFormatter = $container->get('mbh.channelmanager.homeaway_data_formatter');
    }

    protected function getResponseHandler($response, $config = null) : AbstractResponseHandler
    {
        // TODO: Implement getResponseHandler() method.
    }
}