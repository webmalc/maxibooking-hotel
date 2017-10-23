<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Model\Expedia\NotificationRequestData;
use MBH\Bundle\PackageBundle\Document\Order;

class ExpediaNotificationResponseCompiler
{
    CONST DATE_FORMAT = 'Y-m-d\TH:i:s.vP';

    /**
     * @param Order $order
     * @param NotificationRequestData $requestData
     * @return string
     */
    public function formatSuccessCreationResponse(Order $order, NotificationRequestData $requestData)
    {
        $resultNode = $this->getResultNodeEnvelopeNode();
        $this->addHeaderNode($resultNode, $requestData);
        $this->addBodyNode($resultNode, $order, $requestData);

        return $resultNode->asXML();
    }

    /**
     * @param Order $order
     * @param NotificationRequestData $requestData
     * @return string
     */
    public function formatSuccessModificationResponse(Order $order, NotificationRequestData $requestData)
    {
        $resultNode = $this->getResultNodeEnvelopeNode();
        $this->addHeaderNode($resultNode, $requestData);
        $this->addBodyNode($resultNode, $order, $requestData, true);

        return $resultNode->asXML();
    }

    public function formatSuccessCancellationResponse(Order $order, NotificationRequestData $requestData)
    {
        $resultNode = $this->getResultNodeEnvelopeNode();
        $this->addHeaderNode($resultNode, $requestData);
        $this->addCancellationResponseBody($resultNode, $order, $requestData);

        return $resultNode->asXML();
    }

    public function formErrorResponse($errorCode, $errorMessage)
    {
        $resultNode = $this->getResultNodeEnvelopeNode();
        $resultNode->addChild('<soap-env:Header');
        $bodyNode = $resultNode->addChild('Body');
        $faultNode = $bodyNode->addChild('Fault');
        $faultNode->addChild('faultcode', 'soap-env:Client.' . $errorCode, '');
        $faultNode->addChild('faultstring', $errorMessage, '');

        return $resultNode->asXML();
    }

    private function addCancellationResponseBody(\SimpleXMLElement $resultNode, Order $order, NotificationRequestData $requestData)
    {
        $bodyNode = $resultNode->addChild('Body');
        $responseNode = $bodyNode->addChild(
            'OTA_CancelRS',
            null,
            'http://www.opentravel.org/OTA/2003/05'
        );

        $responseNode->addAttribute('Version', 1);
        $responseNode->addAttribute('Status', "Cancelled");
        $responseNode->addAttribute('TimeStamp', (new \DateTime())->format(self::DATE_FORMAT));
        //TODO: Для теста другая
        $responseNode->addAttribute('Target', "Production");
        //TODO: Мб язык другой
        $responseNode->addAttribute('PrimaryLangID', "en-us");

        $responseNode->addChild('Success');
        $expediaUniqueIdNode = $responseNode->addChild('UniqueID');
        $expediaUniqueIdNode->addAttribute('ID', $order->getChannelManagerId());
        $expediaUniqueIdNode->addAttribute('Type', 14);
        $expediaUniqueIdNode->addChild('CompanyName', 'Expedia');

        $mbhUniqueIdNode = $responseNode->addChild('UniqueID');
        $mbhUniqueIdNode->addAttribute('ID', ExpediaOrderInfo::DEFAULT_CONFIRM_NUMBER);
        $mbhUniqueIdNode->addAttribute('Type', 10);
        $mbhUniqueIdNode->addChild('CompanyName', $requestData->getSourceId());

        $cancelInfoNode = $responseNode->addChild('CancelInfoRS');
        $cancelUniqueIDNode = $cancelInfoNode->addChild('UniqueID');
        $cancelUniqueIDNode->addAttribute('Type', 10);
        $cancelUniqueIDNode->addAttribute('ID', $order->getId());
        $cancelUniqueIDNode->addChild('CompanyName', $requestData->getSourceId());
    }
    
    /**
     * @param \SimpleXMLElement $resultNode
     * @param NotificationRequestData $requestData
     */
    private function addHeaderNode(\SimpleXMLElement $resultNode, NotificationRequestData $requestData)
    {
        $headerNode = $resultNode->addChild('soap-env:Header');
        $interfaceNode = $headerNode->addChild('Interface', null, 'http://www.newtrade.com/expedia/R14/header');
        $interfaceNode->addAttribute('Name', 'ExpediaDirectConnect');
        $interfaceNode->addAttribute('Version', '4.0');

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

    /**
     * @param \SimpleXMLElement $resultNode
     * @param Order $order
     * @param NotificationRequestData $requestData
     * @param bool $isModified
     */
    private function addBodyNode(\SimpleXMLElement $resultNode, Order $order, NotificationRequestData $requestData, $isModified = false)
    {
        $bodyNode = $resultNode->addChild('Body');
        $responseNode = $bodyNode->addChild(
            $isModified ? 'OTA_HotelResModifyNotifRS' : 'OTA_HotelResNotifRS',
            null,
            'http://www.opentravel.org/OTA/2003/05'
        );

        $responseNode->addAttribute('Version', 1);
        $responseNode->addAttribute('ResResponseType', $isModified ? 'Modified' : 'Committed');
        $responseNode->addAttribute('TimeStamp', (new \DateTime())->format(self::DATE_FORMAT));
        //TODO: Для теста другая
        $responseNode->addAttribute('Target', "Production");
        //TODO: Мб язык другой
        $responseNode->addAttribute('PrimaryLangID', "en-us");

        $responseNode->addChild('Success');
        $hotelReservationsNode = $responseNode->addChild($isModified ? 'HotelResModifies' :'HotelReservations');
        $hotelReservationNode = $hotelReservationsNode->addChild($isModified ? 'HotelResModify' :'HotelReservation');
        $resGlobalInfoNode = $hotelReservationNode->addChild('ResGlobalInfo');
        $reservationIdsNode = $resGlobalInfoNode->addChild('HotelReservationIDs');

        $mbhReservationIdNode = $reservationIdsNode->addChild('HotelReservationID');
        $mbhReservationIdNode->addAttribute('ResID_Type', 3);
        $mbhReservationIdNode->addAttribute('ResID_Value', ExpediaOrderInfo::DEFAULT_CONFIRM_NUMBER);
        $mbhReservationIdNode->addAttribute('ResID_Date', $order->getCreatedAt()->format(self::DATE_FORMAT));
        $mbhReservationIdNode->addAttribute('ResID_Source', $requestData->getResponderId());

        $expediaReservationIdNode = $reservationIdsNode->addChild('HotelReservationID');
        $expediaReservationIdNode->addAttribute('ResID_Type', 8);
        $expediaReservationIdNode->addAttribute('ResID_Value', $order->getChannelManagerId());
        $expediaReservationIdNode->addAttribute('ResID_Date', (new \DateTime())->format(self::DATE_FORMAT));
        $expediaReservationIdNode->addAttribute('ResID_Source', 'Expedia');
    }

    /**
     * @return \SimpleXMLElement
     */
    private function getResultNodeEnvelopeNode()
    {
        return new \SimpleXMLElement('<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/"></soap-env:Envelope>');
    }
}