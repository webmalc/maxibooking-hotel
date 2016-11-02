<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;


use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractResponseHandler
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    abstract public function getOrderInfos();
    abstract public function isResponseCorrect();
    abstract public function getOrdersCount();
    abstract public function getErrorMessage();
    abstract public function getTariffsData();
    abstract public function getRoomTypesData();
}