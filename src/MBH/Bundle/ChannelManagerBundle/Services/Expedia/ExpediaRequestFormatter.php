<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;

class ExpediaRequestFormatter
{
    /** @var  RequestInfo $requestInfo */
    private $requestInfo;

    public function init()
    {
        $this->requestInfo = new RequestInfo();
    }


}