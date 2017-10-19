<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\PackageBundle\Document\Order;

class ExpediaNotificationResponseCompiler
{
    public function formatSuccessCreationResponse(Order $order)
    {
        $responseNode = new \SimpleXMLElement('<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/"></soap-env:Envelope>');
        $headerNode = $responseNode->addChild('soap-env:Header');
        $interfaceNode = $headerNode->addChild('<Interface xmlns="http://www.newtrade.com/expedia/R14/header" Name="ExpediaDirectConnect" Version="4.0">');
        $payloadInfoNode = $interfaceNode->addChild('PayloadInfo');


        return $responseNode->asXML();
    }
}