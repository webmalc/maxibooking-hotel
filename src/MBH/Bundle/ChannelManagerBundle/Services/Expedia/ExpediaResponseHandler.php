<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractResponseHandler;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class ExpediaResponseHandler extends AbstractResponseHandler
{
    private $response;
    private $config;
    private $isOrderInfosInit = false;
    private $orderInfos = [];

    public function setInitData($response, ChannelManagerConfigInterface $config = null)
    {
        $this->response = $response;
        $this->config = $config;

        return $this;
    }

    /**
     * Ленивая загрузка массива объектов, содержащих данные о заказах, полученных от сервиса
     *
     * @return ExpediaOrderInfo[]
     */
    public function getOrderInfos() {

        if (!$this->isOrderInfosInit) {

            $responseXML = new \SimpleXMLElement($this->response);
            $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
            $tariffsSyncData = $channelManagerHelper->getTariffsSyncData($this->config, true);
            $roomTypesSyncData = $channelManagerHelper->getRoomTypesSyncData($this->config, true);

            foreach ($responseXML->Bookings->booking as $orderInfoElement) {
                $orderInfos[] = $this->container->get('mbh.channelmanager.expedia_order_info')
                    ->setInitData($orderInfoElement, $this->config, $tariffsSyncData, $roomTypesSyncData);
            }

            $this->isOrderInfosInit = true;
        }

        return $this->orderInfos;
    }

    public function isResponseCorrect()
    {
        if ($this->isXMLResponse()) {
            $xmlResponse = new \SimpleXMLElement($this->response);

            return $xmlResponse->xpath('//Success') ? true : false;
        }
        $jsonResponse = json_decode($this->response, true);

        return !isset($jsonResponse['errors']);
    }

    public function getOrdersCount()
    {
        return count($this->getOrderInfos());
    }

    public function getErrorMessage()
    {
        if ($this->isXMLResponse()) {
            $xmlResponse = new \SimpleXMLElement($this->response);

            //TODO: Проверить в каком виде приходит сообщение
            return (string)$xmlResponse->xpath('//Error');
        }
        $jsonResponse = json_decode($this->response, true);

        return $jsonResponse['errors']['message'];
    }

    public function getTariffsData()
    {
        $tariffs = [];
        $response = json_decode($this->response, true);
        foreach ($response['entity'] as $tariffInfo) {
            $requestedUrl = $tariffInfo['_links']['self']['href'];
            $tariffs['resourceId'] = [
                'title' => $tariffInfo['name'],
                'rooms' => [$this->getRoomTypeIdFromUrlString($requestedUrl)]
            ];
        }
    }

    public function getRoomTypesData()
    {
        $roomTypesData = [];
        $response = json_decode($this->response, true);
        foreach ($response['entity'] as $roomTypeInfo) {
            $roomTypesData[$roomTypeInfo['resourceId']] = $roomTypeInfo['name']['value'];
        }

        return $roomTypesData;
    }

    private function isXMLResponse()
    {
        $result = simplexml_load_string($this->response, 'SimpleXmlElement', LIBXML_NOERROR+LIBXML_ERR_FATAL+LIBXML_ERR_NONE);

        return $result === false ? false : true;
    }

    /**
     * Получает id типа комнаты из url. Строка с id находится между строками roomTypes/ и /ratePlans
     * @param $url
     * @return string
     */
    private function getRoomTypeIdFromUrlString($url)
    {
        $roomTypeIdStartPosition = strpos($url, 'roomTypes/') + strlen('roomTypes/');
        $roomTypeIdEndPosition = strpos($url, '/ratePlans');
        $roomTypeIdStringLength = $roomTypeIdEndPosition - $roomTypeIdStartPosition;
        return substr($url, $roomTypeIdStartPosition, $roomTypeIdStringLength);
    }
}