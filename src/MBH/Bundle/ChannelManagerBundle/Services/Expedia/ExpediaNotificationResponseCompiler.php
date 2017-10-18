<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\PackageBundle\Document\Order;

class ExpediaNotificationResponseCompiler
{
    public function formatSuccessCreationResponse(Order $order)
    {
        $responseNode = new \SimpleXMLElement('<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/"></soap-env:Envelope>');
//        $responseNode->addAttribute('xmlns:soap-env', "http://schemas.xmlsoap.org/soap/envelope/");
        $headerNode = $responseNode->addChild('soap-env:Header');
        $headerNode->addChild('Interface');

        return $responseNode->asXML();
    }
}