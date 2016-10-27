<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;


abstract class AbstractResponseHandler
{
    abstract public function getOrderInfoArray();
    abstract public function isResponseCorrect();
    abstract public function getOrdersCount();
    abstract public function getErrorMessage();
    abstract public function getTariffsData();
    abstract public function getRoomTypesData();
}