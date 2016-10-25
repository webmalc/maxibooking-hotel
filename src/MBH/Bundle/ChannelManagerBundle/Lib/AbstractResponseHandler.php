<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;


abstract class AbstractResponseHandler
{
    abstract public function getOrderInfoArray($response);
    abstract public function isResponseCorrect($response);
}