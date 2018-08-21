<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ExtendedAbstractChannelManager;
use MBH\Bundle\ChannelManagerBundle\Services\ChannelManager;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractResponseHandler;

class Expedia extends ExtendedAbstractChannelManager
{
    const CONFIG = 'ExpediaConfig';
    const UNAVAIBLE_PRICES = [
    ];

    const UNAVAIBLE_RESTRICTIONS = [
        'minStayArrival' => null,
        'maxStayArrival' => null,
        'minBeforeArrival' => null,
        'maxBeforeArrival' => null,
        'maxGuest' => null,
        'minGuest' => null,
    ];

    const BOOKING_SOURCES = [
        'Expedia',
        'Hotels.com',
        'Expedia Affiliate Network',
        'Egencia',
        'Travelocity',
        'Orbitz',
        'Wotif',
        'Hotwire',
        'CheapTickets',
        'ebookers',
        'MrJet',
        'Lastminute.au',
        'American Express Travel',
        'Amex The Hotel Collection',
        'Amex FINE HOTELS AND RESORTS'
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->requestFormatter = $container->get('mbh.channelmanager.expedia_request_formatter');
        $this->requestDataFormatter = $container->get('mbh.channelmanager.expedia_request_data_formatter');
    }

    public function safeConfigDataAndGetErrorMessage()
    {
        $requestInfo = $this->requestFormatter->formatGetHotelInfoRequest();
        $jsonResponse = $this->sendRequestAndGetResponse($requestInfo);
        $responseHandler = $this->getResponseHandler($jsonResponse);
        if ($responseHandler->isResponseCorrect()) {
            return '';
        }

        return $responseHandler->getErrorMessage();
    }

    protected function getResponseHandler($response, $config = null): AbstractResponseHandler
    {
        return $this->container->get('mbh.channelmanager.expedia_response_handler')->setInitData($response, $config);
    }

    protected function notifyServiceAboutReservation(AbstractOrderInfo $orderInfo, $config)
    {
        /** @var ExpediaOrderInfo $orderInfo */
        $requestData = $this->requestDataFormatter->formatNotifyServiceData($orderInfo, $config);
        $requestInfo = $this->requestFormatter->formatBookingConfirmationRequest($requestData);

        $this->logger->info('Send confirmation request to expedia for order #' . $orderInfo->getChannelManagerOrderId());
        $response = $this->sendRequestAndGetResponse($requestInfo);
        $responseHandler = $this->getResponseHandler($response);

        if (!$responseHandler->isResponseCorrect()) {
            $this->notifyError($orderInfo->getChannelManagerName(),
                $this->container->get('translator')->trans('services.expedia.booking_notification.error') . ' #'
                . $orderInfo->getChannelManagerOrderId() . ' ' . $orderInfo->getPayer()->getName());
        }

        $this->logger->info('Confirmation response for order #'
        . $orderInfo->getChannelManagerOrderId()
        . 'is ' . $responseHandler->isResponseCorrect() ? 'correct' : 'incorrect');

    }

    public function pullOrders($pullOldStatus = ChannelManager::OLD_PACKAGES_PULLING_NOT_STATUS)
    {
        if ($pullOldStatus === ChannelManager::OLD_PACKAGES_PULLING_NOT_STATUS) {
            return parent::pullOrders();
        }

        return $this->pullAllOrders();
    }

    /**
     * pull all orders during client connection
     */
    public function pullAllOrders()
    {
        $result = true;
        $availableStatuses = ['confirmed', 'retrieved', 'pending'];
        foreach ($availableStatuses as $status) {
            /** @var ExpediaConfig $config */
            foreach ($this->getConfig(true) as $config) {

                $requestData = $this->requestDataFormatter->formatGetAllBookingsData($config, $status);
                $request = $this->requestFormatter->formatGetOrdersRequest($requestData);

                $response = $this->sendRequestAndGetResponse($request);
                $this->handlePullOrdersResponse($response, $config, $result, true);
            }

            $config->setIsAllPackagesPulled(true);
            $this->dm->flush();
        }

        $cm = $this->container->get('mbh.channelmanager');
        $cm->clearAllConfigsInBackground();
        $cm->updateInBackground();

        return $result;
    }

    /**
     * @param $xmlString
     * @return string
     */
    public function handleNotificationOrder($xmlString)
    {
        $xmlString = '<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface xmlns="http://www.newtrade.com/expedia/R14/header" Name="ExpediaDirectConnect" Version="4.0">
            <PayloadInfo ExpirationDateTime="2017-12-14T17:06:22+00:00" Location="Body" RequestId="39C070CF4E66E18252EA"
                         RequestorId="Expedia.com" ResponderId="EQCMaxibooking">
                <CommDescriptor DestinationId="EQCMaxibooking" RetryIndicator="false" SourceId="ExpediaDC"/>
                <PayloadDescriptor Name="OTA_HotelResNotifRQ" Version="2003A">
                    <PayloadReference DistributorHotelId="17093320" SupplierChainCode="2131"
                                      SupplierHotelCode="sdfasdfasdf"/>
                </PayloadDescriptor>
            </PayloadInfo>
        </Interface>
    </soap-env:Header>
    <soap-env:Body>
        <OTA_HotelResNotifRQ xmlns="http://www.opentravel.org/OTA/2003/05" EchoToken="39C070CF4E66E18252EA"
                             PrimaryLangID="en-us" ResStatus="Commit" Target="Production"
                             TimeStamp="2017-12-14T14:36:22+00:00" Version="1.000">
            <POS>
                <Source>
                    <RequestorID ID="A-Hotwire" Type="18"/>
                    <BookingChannel Primary="true" Type="2">
                        <CompanyName>Expedia</CompanyName>
                    </BookingChannel>
                </Source>
            </POS>
            <HotelReservations>
                <HotelReservation CreateDateTime="2017-12-14T14:36:22+00:00" CreatorID="Expedia"
                                  RoomStayReservation="true">
                    <UniqueID ID="123752645" Type="14"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType IsRoom="true" RoomTypeCode="202202547"/>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan EffectiveDate="2017-12-17" ExpireDate="2017-12-18" RatePlanCode="209753354A"/>
                                <RatePlan EffectiveDate="2017-12-18" ExpireDate="2017-12-19" RatePlanCode="209753354A"/>
                                <RatePlan EffectiveDate="2017-12-19" ExpireDate="2017-12-20" RatePlanCode="209753354A"/>
                                <RatePlan EffectiveDate="2017-12-20" ExpireDate="2017-12-21" RatePlanCode="209753354A"/>
                                <RatePlan EffectiveDate="2017-12-21" ExpireDate="2017-12-22" RatePlanCode="209753354A"/>
                            </RatePlans>
                            <RoomRates>
                                <RoomRate EffectiveDate="2017-12-17" ExpireDate="2017-12-18" NumberOfUnits="1"
                                          RatePlanCode="209753354A" RoomTypeCode="202202547">
                                    <Rates>
                                        <Rate EffectiveDate="2017-12-17" ExpireDate="2017-12-18" RateTimeUnit="Day"
                                              UnitMultiplier="1">
                                            <Base AmountBeforeTax="123" CurrencyCode="USD"/>
                                            <Fees>
                                                <Fee Amount="0.00" Code="1" CurrencyCode="USD" TaxInclusive="false"
                                                     Type="Exclusive"/>
                                            </Fees>
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                                <RoomRate EffectiveDate="2017-12-18" ExpireDate="2017-12-19" NumberOfUnits="1"
                                          RatePlanCode="209753354A" RoomTypeCode="202202547">
                                    <Rates>
                                        <Rate EffectiveDate="2017-12-18" ExpireDate="2017-12-19" RateTimeUnit="Day"
                                              UnitMultiplier="1">
                                            <Base AmountBeforeTax="123" CurrencyCode="USD"/>
                                            <Fees>
                                                <Fee Amount="0.00" Code="1" CurrencyCode="USD" TaxInclusive="false"
                                                     Type="Exclusive"/>
                                            </Fees>
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                                <RoomRate EffectiveDate="2017-12-19" ExpireDate="2017-12-20" NumberOfUnits="1"
                                          RatePlanCode="209753354A" RoomTypeCode="202202547">
                                    <Rates>
                                        <Rate EffectiveDate="2017-12-19" ExpireDate="2017-12-20" RateTimeUnit="Day"
                                              UnitMultiplier="1">
                                            <Base AmountBeforeTax="123" CurrencyCode="USD"/>
                                            <Fees>
                                                <Fee Amount="0.00" Code="1" CurrencyCode="USD" TaxInclusive="false"
                                                     Type="Exclusive"/>
                                            </Fees>
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                                <RoomRate EffectiveDate="2017-12-20" ExpireDate="2017-12-21" NumberOfUnits="1"
                                          RatePlanCode="209753354A" RoomTypeCode="202202547">
                                    <Rates>
                                        <Rate EffectiveDate="2017-12-20" ExpireDate="2017-12-21" RateTimeUnit="Day"
                                              UnitMultiplier="1">
                                            <Base AmountBeforeTax="123" CurrencyCode="USD"/>
                                            <Fees>
                                                <Fee Amount="0.00" Code="1" CurrencyCode="USD" TaxInclusive="false"
                                                     Type="Exclusive"/>
                                            </Fees>
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                                <RoomRate EffectiveDate="2017-12-21" ExpireDate="2017-12-22" NumberOfUnits="1"
                                          RatePlanCode="209753354A" RoomTypeCode="202202547">
                                    <Rates>
                                        <Rate EffectiveDate="2017-12-21" ExpireDate="2017-12-22" RateTimeUnit="Day"
                                              UnitMultiplier="1">
                                            <Base AmountBeforeTax="123" CurrencyCode="USD"/>
                                            <Fees>
                                                <Fee Amount="0.00" Code="1" CurrencyCode="USD" TaxInclusive="false"
                                                     Type="Exclusive"/>
                                            </Fees>
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts IsPerRoom="true">
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                            <TimeSpan End="2017-12-22" Start="2017-12-17"/>
                            <Guarantee>
                                <GuaranteesAccepted>
                                    <GuaranteeAccepted>
                                        <PaymentCard CardCode="MC" CardNumber="5555555555554444" CardType="1"
                                                     ExpireDate="0418">
                                            <CardHolderName>Naveen Vorugantii</CardHolderName>
                                        </PaymentCard>
                                    </GuaranteeAccepted>
                                </GuaranteesAccepted>
                            </Guarantee>
                            <Total AmountAfterTax="626" CurrencyCode="USD">
                                <Taxes Amount="11" CurrencyCode="USD">
                                    <Tax Amount="11" Code="27" CurrencyCode="USD" Type="Exclusive"/>
                                </Taxes>
                            </Total>
                            <BasicPropertyInfo ChainCode="2131" HotelCode="sdfasdfasdf"/>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                            </ResGuestRPHs>
                            <SpecialRequests>
                                <SpecialRequest Language="en-us" RequestCode="1.15">
                                    <Text Formatted="false" Language="en-us">1 queen</Text>
                                </SpecialRequest>
                                <SpecialRequest Language="en-us" RequestCode="2.2">
                                    <Text Formatted="false" Language="en-us">Smoking</Text>
                                </SpecialRequest>
                                <SpecialRequest Language="en-us" RequestCode="4">
                                    <Text Formatted="false" Language="en-us">sdfsadfasdfasd</Text>
                                </SpecialRequest>
                            </SpecialRequests>
                        </RoomStay>
                    </RoomStays>
                    <ResGuests>
                        <ResGuest AgeQualifyingCode="10" ResGuestRPH="1">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile ProfileType="1">
                                        <Customer>
                                            <PersonName>
                                                <GivenName>sadfasdf</GivenName>
                                                <Surname>asdfasdf</Surname>
                                            </PersonName>
                                            <Telephone AreaCityCode="555" CountryAccessCode="1" PhoneNumber="555-5555"/>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <HotelReservationIDs>
                            <HotelReservationID ResID_Date="2017-12-14T14:36:22+00:00" ResID_Source="Expedia"
                                                ResID_Type="8" ResID_Value="123752645"/>
                        </HotelReservationIDs>
                    </ResGlobalInfo>
                </HotelReservation>
            </HotelReservations>
        </OTA_HotelResNotifRQ>
    </soap-env:Body>
</soap-env:Envelope>';
        //TODO: Добавить обработку ошибок
        /** @var ExpediaResponseHandler $responseHandler */
        $responseHandler = $this->getResponseHandler($xmlString);

        $result = null;
        $orderInfo = $responseHandler->getNotificationOrderInfo();
        $this->handleOrderInfo($orderInfo, $result);

        if ($result instanceof Order) {
            $requestData = $responseHandler->getNotificationRequestData();
            $notificationResponseCompiler = $this->container
                ->get('mbh.channel_manager.expedia_notification_response_compiler');
            if ($orderInfo->isOrderCreated()) {
                $response = $notificationResponseCompiler->formatSuccessCreationResponse($result, $requestData);
            } elseif ($orderInfo->isOrderModified()) {
                $response = $notificationResponseCompiler->formatSuccessModificationResponse($result, $requestData);
            } elseif ($orderInfo->isOrderCancelled()) {
                $response = $notificationResponseCompiler->formatSuccessCancellationResponse($result, $requestData);
            } else {
                //TODO: Добавить обработку ошибки
                $response = 'Все пропало';
            }
        } else {
            //TODO: Добавить обработку ошибки
            $response = 'Все пропало';
        }

        return $response;
    }
}