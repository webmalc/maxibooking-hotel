<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
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

        //TODO: Уточнить нужно ли реализовывать
        return $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelInventoryData($apiVersion, $language, $inventoryType);
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

//        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
//            ->formatHotelAvailability($apiVersion, $requestedHotels, $startDate, $endDate,
//                $requestedAdultsChildrenCombination, $language, $queryKey, $currency, $userCountry, $deviceType);
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelAvailability(7,
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


        $orderInfo = $this->get('mbh.channel_manager.trip_advisor_order_info')
            ->setInitData($checkInDate, $checkOutDate, $hotelId, $customerData, $roomsData, $specialRequests,
                $paymentData, $finalPriceAtBooking, $finalPriceAtCheckout, $bookingMainData, $bookingSession);

        $bookingCreationResult = $this->get('mbh.channel_manager.trip_advisor_booking_handler')
            ->createBooking($checkInDate, $checkOutDate, $hotelId, $bookingSession, $ipAddress, $customerData,
                $roomsData, $specialRequests, $paymentData, $finalPriceAtBooking, $finalPriceAtCheckout,
                $bookingMainData);

//        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
    }

    public function testAction()
    {
//        $this->get('mbh.channel_manager.trip_advisor_response_formatter')->formatHotelAvailability(7, [])
    }
}
