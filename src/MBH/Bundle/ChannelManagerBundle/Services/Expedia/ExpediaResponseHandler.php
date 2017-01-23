<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractResponseHandler;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class ExpediaResponseHandler extends AbstractResponseHandler
{
    const OCCUPANCY_BASED_PRICING = 'OccupancyBasedPricing';
    private $response;
    private $config;
    private $isOrderInfosInit = false;
    private $orderInfos = [];

    public function setInitData($response, ?ChannelManagerConfigInterface $config = null)
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
            $response = $this->removeXmlnsString($this->response);
            $responseXML = new \SimpleXMLElement($response);
            $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
            $tariffsSyncData = $channelManagerHelper->getTariffsSyncData($this->config, true);
            $roomTypesSyncData = $channelManagerHelper->getRoomTypesSyncData($this->config, true);
            
            foreach ($responseXML->Bookings->Booking as $bookingElement) {
                $this->orderInfos[] = $this->container->get('mbh.channelmanager.expedia_order_info')
                    ->setInitData($bookingElement, $this->config, $tariffsSyncData, $roomTypesSyncData);
            }

            $this->isOrderInfosInit = true;
        }

        return $this->orderInfos;
    }

    public function isResponseCorrect()
    {
        if ($this->isXMLResponse()) {
            $xmlResponse = new \SimpleXMLElement($this->removeXmlnsString($this->response));

            return $xmlResponse->xpath('//Error') ? false: true;
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
            $xmlResponse = new \SimpleXMLElement($this->removeXmlnsString($this->response));

            return (string)$xmlResponse->xpath('//Error');
        }
        $jsonResponse = json_decode($this->response, true);

        return $jsonResponse['errors']['message'];
    }

    public function getTariffsData(array $roomTypes)
    {
        $tariffs = [];
        $response = json_decode($this->response, true);
        foreach ($response['entity'] as $tariffInfo) {
            $requestedUrl = $tariffInfo['_links']['self']['href'];
            foreach ($tariffInfo['distributionRules'] as $data) {
                if ($data['manageable'] == true) {
                    $roomTypeId = $this->getRoomTypeIdFromUrlString($requestedUrl);
                    $roomTypeName = $roomTypes[$roomTypeId];
                    $tariffs[$data['expediaId']] = [
                        'title' => $tariffInfo['name'] . " ( $roomTypeName, {$data['distributionModel']} )" ,
                        'rooms' => [$roomTypeId],
                        'readonly' => $tariffInfo['pricingModel'] === self::OCCUPANCY_BASED_PRICING ? false : true,
                        'minLOSDefault' => $tariffInfo['minLOSDefault'],
                        'maxLOSDefault' => $tariffInfo['maxLOSDefault']
                    ];
                }
            }
        }

        return $tariffs;
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

        return $result->__toString() === '' ? false : true;
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

    private function removeXmlnsString($xmlString)
    {
        $xmlnsStringStartPosition = strpos($xmlString, 'xmlns');
        $firstQuotesPosition = $xmlnsStringStartPosition + 8;
        $xmlnsStringEndPosition = strpos($xmlString, '"', $firstQuotesPosition) + 1;
        $xmlnsString = substr($xmlString, $xmlnsStringStartPosition, $xmlnsStringEndPosition - $xmlnsStringStartPosition);

        return str_replace($xmlnsString, "", $xmlString);
    }

}