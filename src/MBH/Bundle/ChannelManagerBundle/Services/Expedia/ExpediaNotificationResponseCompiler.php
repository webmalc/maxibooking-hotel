<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Model\Expedia\NotificationRequestData;
use MBH\Bundle\PackageBundle\Document\Order;

class ExpediaNotificationResponseCompiler
{
    public function formatSuccessCreationResponse(Order $order, NotificationRequestData $requestData)
    {
        $resultNode = new \SimpleXMLElement('<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/"></soap-env:Envelope>');
        $this->addHeaderNode($resultNode, $requestData);



        return $resultNode->asXML();
    }

    /**
     * @param \SimpleXMLElement $resultNode
     * @param NotificationRequestData $requestData
     */
    private function addHeaderNode(\SimpleXMLElement $resultNode, NotificationRequestData $requestData)
    {
        $headerNode = $resultNode->addChild('soap-env:Header');
        $interfaceNode = $headerNode->addChild('<Interface xmlns="http://www.newtrade.com/expedia/R14/header" Name="ExpediaDirectConnect" Version="4.0">');

        $payloadInfoNode = $interfaceNode->addChild('PayloadInfo');
        $payloadInfoNode->addAttribute('RequestId', $requestData->getRequestId());
        $payloadInfoNode->addAttribute('RequestorId', $requestData->getRequestorId());
        $payloadInfoNode->addAttribute('ResponderId', $requestData->getResponderId());
        $payloadInfoNode->addAttribute('Location', "Body");

        $commdescriptorNode = $payloadInfoNode->addChild('CommDescriptor');
        $commdescriptorNode->addAttribute('SourceId', $requestData->getDestinationId());
        $commdescriptorNode->addAttribute('DestinationId', $requestData->getSourceId());
        $commdescriptorNode->addAttribute('RetryIndicator', "false");

        $payloadDescriptorNode = $payloadInfoNode->addChild('PayloadDescriptor');
        $payloadDescriptorNode->addAttribute('Name', "OTA_HotelResNotifRS");
        $payloadDescriptorNode->addAttribute('Version', $requestData->getVersion());
        $payloadReferenceNode = $payloadDescriptorNode->addChild('PayloadReference');
        $payloadReferenceNode->addAttribute('SupplierHotelCode', $requestData->getMbhHotelId());
    }

    public function addBodyNode(\SimpleXMLElement $resultNode)
    {
        $bodyNode = $resultNode->addChild('<soap-env:Body></soap-env:Body>');
        $responseNode = $bodyNode->addChild('OTA_HotelResNotifRS');
        $responseNode->addAttribute('xmlns', "http://www.opentravel.org/OTA/2003/05");
        $responseNode->addAttribute('Version', 1);
        //TODO: Для модифицированных - другая
        $responseNode->addAttribute('ResResponseType', 'Committed');
        $responseNode->addAttribute('TimeStamp', (new \DateTime())->format('Y-m-d\TH:i:s.vP'));
        //TODO: Для теста другая
        $responseNode->addAttribute('Target', "Production");
        //TODO: Мб язык другой
        $responseNode->addAttribute('PrimaryLangID', "en-us");
    }

    /**
    <Success/>
    <HotelReservations>
    <HotelReservation>
    <ResGlobalInfo>
    <HotelReservationIDs>
    <HotelReservationID ResID_Type="3" ResID_Value="ConfNumber123" ResID_Date="2016-05-17T12:56:52.597-07:00" ResID_Source="EQCSpecTest"/>
    <HotelReservationID ResID_Type="8" ResID_Value="13357395" ResID_Source="Expedia" ResID_Date="2016-05-17T12:57:00-07:00"/>
    </HotelReservationIDs>
    </ResGlobalInfo>
    </HotelReservation>
    </HotelReservations>
     */
}