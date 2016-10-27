<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractResponseHandler;

class ExpediaResponseHandler extends AbstractResponseHandler
{
    private $response;

    public function setInitData($response)
    {
        $this->response = $response;

        return $this;
    }

    public function getOrderInfoArray()
    {
        $responseXML = new \SimpleXMLElement($this->response);

        return $responseXML->Bookings;
    }

    public function isResponseCorrect()
    {
        // TODO: Implement isResponseCorrect() method.
    }

    public function getOrdersCount()
    {
        return count($this->getOrderInfoArray());
    }

    public function getErrorMessage()
    {
        //TODO: Implement method
    }

    public function getTariffsData()
    {
        // TODO: Implement getTariffsData() method.
    }

    public function getRoomTypesData()
    {
        // TODO: Implement getRoomTypesData() method.
    }
}