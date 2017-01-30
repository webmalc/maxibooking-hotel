<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorPackageInfo;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TripAdvisorController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 * @Route("/tripadvisor")
 */
class TripAdvisorController extends BaseController
{
    /**
     * @Route("/config")
     */
    public function getConfigDataAction()
    {
        return $this->get('mbh.channel_manager.trip_advisor_response_formatter')->formatConfigResponse();
    }

    /**
     * @Route("/hotel_inventory")
     * @param Request $request
     * @return array
     */
    public function getHotelInventoryDataAction(Request $request)
    {
        $apiVersion = $request->get('api_version');
        $language = $request->get('lang');
        $inventoryType = $request->get('inventory_type');

        $responseDataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $configuredHotels = $responseDataFormatter->getTripAdvisorConfigs();

        //TODO: Уточнить нужно ли реализовывать
        return $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelInventoryData($apiVersion, $language, $inventoryType, $configuredHotels);
    }

    /**
     * @Route("/hotel_availability")
     * @param Request $request
     * @return Response
     */
    public function getHotelAvailabilityAction(Request $request)
    {
        $apiVersion = $request->get('api_version');
        $requestedHotels = $request->get('hotels');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $requestedAdultsChildrenCombination = $request->get('party');
        $language = $request->get('lang');
        $queryKey = $request->get('query_key');
        $currency = $request->get('currency');
        $userCountry = $request->get('user_country');
        $deviceType = $request->get('device_type');

        $availabilityData = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter')
            ->getAvailabilityData($startDate, $endDate, $requestedHotels);

//        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
//            ->formatHotelAvailability($apiVersion, $requestedHotels, $startDate, $endDate,
//                $requestedAdultsChildrenCombination, $language, $queryKey, $currency, $userCountry, $deviceType);
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelAvailability(
                $availabilityData,
                7,
                [["ta_id" => 97497, "partner_id" => "5864e3da2f77d9004b580232"]],
                '2017-01-12',
                '2017-01-18',
                [["adults" => 1]],
                'en_US',
                'sadfafasdf',
                'USD',
                'US',
                'd');

        return new Response();
    }

    /**
     * @Route("/booking_availability")
     * @param Request $request
     * @return string
     */
    public function getBookingAvailabilityAction(Request $request)
    {
        $apiVersion = $request->get('api_version');
        $requestedHotels = $request->get('hotels');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $requestedAdultsChildrenCombination = $request->get('party');
        $language = $request->get('lang');
        $queryKey = $request->get('query_key');
        $currency = $request->get('currency');
        $userCountry = $request->get('user_country');
        $deviceType = $request->get('device_type');
        $bookingSessionId = $request->get('booking_session_id');
        $bookingRequestId = $request->get('booking_request_id');

        $responseDataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
//        $hotel = $responseDataFormatter->getHotelById([$hotelData['partner_hotel_code']]);
//        $availabilityDataArray = $responseDataFormatter->getSearchResults($startDate, $endDate, $hotel);

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingAvailability($apiVersion, $requestedHotels, $startDate, $endDate,
                $requestedAdultsChildrenCombination, $language, $queryKey, $userCountry, $deviceType, $currency);

        return json_encode($response);
    }

    public function bookingSubmitAction(Request $request)
    {
        $checkInDate = $request->get('checkin_date');
        $checkOutDate = $request->get('checkout_date');
        $hotelId = $request->get('partner_hotel_code');
        $bookingSession = $request->get('reference_id');
        $ipAddress = $request->get('ip_address');
        $customerData = $request->get('customer');
        $roomsData = $request->get('rooms');
        $specialRequests = $request->get('special_requests');
        $paymentData = $request->get('payment_method');
        $finalPriceAtBooking = $request->get('final_price_at_booking');
        $finalPriceAtCheckout = $request->get('final_price_at_checkout');
        $bookingMainData = $request->get('partner_data');


        /** @var TripAdvisorOrderInfo $orderInfo */
        $orderInfo = $this->get('mbh.channel_manager.trip_advisor_order_info')
            ->setInitData($checkInDate, $checkOutDate, $hotelId, $customerData, $roomsData, $specialRequests,
                $paymentData, $finalPriceAtBooking, $finalPriceAtCheckout, $bookingMainData, $bookingSession);

        $bookingCreationResult = $this->get('mbh.channel_manager.order_creator')->createOrder($orderInfo);

        $currency = $finalPriceAtCheckout['currency'];
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatSubmitBookingResponse($bookingSession, $bookingCreationResult,
                $orderInfo->getPackageAndOrderMessages(), $customerData['country'], $roomsData, $currency);

        return json_encode($response);
    }

    /**
     * @Route("/booking_verify")
     * @param Request $request
     * @return string
     */
    public function bookingVerifyAction(Request $request)
    {
        $hotelId = $request->get('partner_hotel_code');
        $orderId = $request->get('reservation_id');
        $channelManagerOrderId = $request->get('reference_id');

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingVerificationResponse($orderId, $channelManagerOrderId);

        return json_encode($response);
    }

    /**
     * @Route("/booking_cancel")
     * @param Request $request
     * @return string
     */
    public function bookingCancelAction(Request $request)
    {
        $hotelId = $request->get('partner_hotel_code');
        $orderId = $request->get('reservation_id');

        $order = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter')
            ->getOrderById($orderId, true);
        if (is_null($order)) {
            $removalStatus = 'UnknownReference';
        } elseif ($order->isDeleted()) {
            $removalStatus = 'AlreadyCancelled';
        } else {
            try {
                $this->get('mbh.channel_manager.order_handler')->deleteOrder($order);
                $removalStatus = 'Success';
            } catch (DeleteException $e) {
                $removalStatus = 'CannotBeCancelled';
            } catch (\Exception $e) {
                $removalStatus = 'Error';
            }
        }

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingCancelResponse($removalStatus, $hotelId, $orderId);

        return json_encode($response);
    }

    /**
     * @Route("/booking_sync")
     * @param Request $request
     * @return string
     */
    public function bookingSyncAction(Request $request)
    {
        //TODO: Сменить имя, неизвестно какое
        $syncOrderData = $request->get('array');

        $syncOrders = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter')
            ->getBookingSyncData($syncOrderData);

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingSyncResponse($syncOrders);

        return json_encode($response);
    }

    /**
     * @Route("/room_information")
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function roomInformationAction(Request $request)
    {
        $apiVersion = $request->get('api_version');
        $hotelData = $request->get('hotel');
        $language = $request->get('language_request');
        $queryKey = $request->get('unique_query_key');

        $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $hotel = $dataFormatter->getHotelById($hotelData['partner_hotel_code']);
        //TODO: Что с этой ситуацией?
        if (is_null($hotel)) {
            throw new \Exception();
        }

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatRoomInformationResponse($apiVersion, $hotelData, $language, $queryKey);

        return json_encode($response);
    }
}
