<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractResponseHandler;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\Expedia\NotificationRequestData;

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

            return (string)$xmlResponse->xpath('//Error')[0];
        }
        $jsonResponse = json_decode($this->response, true);

        return $jsonResponse['errors'][0]['message'];
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
                        'title' => $tariffInfo['name'] . " ($roomTypeName, {$data['distributionModel']})" ,
                        'rooms' => [$roomTypeId],
                        'readonly' => $tariffInfo['pricingModel'] === self::OCCUPANCY_BASED_PRICING ? false : true,
                        'minLOSDefault' => $tariffInfo['minLOSDefault'],
                        'maxLOSDefault' => $tariffInfo['maxLOSDefault'],
                    ];

                    if (isset($tariffInfo['ratePlanLinkage'])) {
                        $tariffs[$data['expediaId']]['derivationRules'] = $tariffInfo['ratePlanLinkage'];
                    }
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

    /**
     * @return bool
     */
    private function isXMLResponse()
    {
        return $this->container->get('mbh.helper')->isXMLValid($this->response);
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

    public function removeXmlnsString($xmlString)
    {
        $xmlnsStringStartPosition = strpos($xmlString, 'xmlns');
        $firstQuotesPosition = $xmlnsStringStartPosition + 10;
        $xmlnsStringEndPosition = strpos($xmlString, '"', $firstQuotesPosition) + 1;
        $xmlnsString = substr($xmlString, $xmlnsStringStartPosition, $xmlnsStringEndPosition - $xmlnsStringStartPosition);

        return str_replace($xmlnsString, "", $xmlString);
    }

    /**
     * @param NotificationRequestData $requestData
     * @return ExpediaNotificationOrderInfo
     */
    public function getNotificationOrderInfo()
    {
        $simpleXml = $this->getSimpleXmlByRequestXml();

        $expediaConfig = $this->getExpediaConfigByNotificationRequest();
        $channelManagerHelper = $this->container->get('mbh.channelmanager.helper');
        $tariffsSyncData = $channelManagerHelper->getTariffsSyncData($expediaConfig, true);
        $roomTypesSyncData = $channelManagerHelper->getRoomTypesSyncData($expediaConfig, true);

        return $this->container
            ->get('mbh.channel_manager.expedia_notification_order_info')
            ->setInitData($simpleXml, $expediaConfig, $tariffsSyncData, $roomTypesSyncData);
    }

    /**
     * @return ExpediaConfig
     */
    public function getExpediaConfigByNotificationRequest()
    {
        $simpleXml = $this->getSimpleXmlByRequestXml();
        $hotelId = (string)$simpleXml->Header->Interface->PayloadInfo->PayloadDescriptor->PayloadReference->attributes()['DistributorHotelId'];

        return $this->container
            ->get('doctrine_mongodb.odm.default_document_manager')
            ->getRepository('MBHChannelManagerBundle:ExpediaConfig')
            ->findOneBy(['hotelId' => $hotelId]);
    }

    /**
     * @return NotificationRequestData
     */
    public function getNotificationRequestData(): NotificationRequestData
    {
        $simpleXml = $this->getSimpleXmlByRequestXml();
        /** @var \SimpleXMLElement $payloadInfoNode */
        $payloadInfoNode = $simpleXml->Header->Interface->PayloadInfo;
        $payloadInfoAttributes = $payloadInfoNode->attributes();

        $commdescriptorAttributes = $payloadInfoNode->CommDescriptor->attributes();

        return (new NotificationRequestData())
            ->setRequestId($payloadInfoAttributes['RequestId'])
            ->setRequestorId($payloadInfoAttributes['RequestorId'])
            ->setResponderId($payloadInfoAttributes['ResponderId'])
            ->setDestinationId($commdescriptorAttributes['DestinationId'])
            ->setSourceId($commdescriptorAttributes['SourceId'])
            ->setVersion($payloadInfoNode->PayloadDescriptor->attributes()['Version'])
            ->setMbhHotelId($payloadInfoNode->PayloadDescriptor->PayloadReference->attributes()['SupplierHotelCode']);
    }

    private function getSimpleXmlByRequestXml()
    {
        $requestXml = str_replace("soap-env:", '', $this->response);

        return new \SimpleXMLElement($requestXml);
    }
}