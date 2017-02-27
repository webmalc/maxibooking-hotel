<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\ChannelManagerBundle\Form\TripAdvisorType;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorOrderInfo;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/", name="tripadvisor")
     * @Security("is_granted('ROLE_TRIPADVISOR')")
     * @Template()
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $config = $this->hotel->getTripAdvisorConfig();

        if (!$config) {
            $config = new TripAdvisorConfig();
            $config->setHotel($this->hotel);
        }

        $languages = $this->getParameter('full_locales');
        $form = $this->createForm(TripAdvisorType::class, $config, [
            'hotel' => $this->hotel,
            'languages' => $languages
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->dm->persist($config);
            $this->dm->flush();
            $this->addFlash('success', 'controller.tripadvisor_controller.settings_saved_success');
        }

        return [
            'doc' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Route("/config")
     * @Method("GET")
     * @return JsonResponse
     */
    public function getConfigDataAction()
    {
        $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->getHotelWithFilledContacts();
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')->formatConfigResponse($hotel);

        return new JsonResponse($response);
    }

    /**
     * @Method("GET")
     * @Route("/hotel_inventory")
     * @param Request $request
     * @return JsonResponse
     */
    public function getHotelInventoryDataAction(Request $request)
    {
        $apiVersion = $request->get('api_version');
        $language = $request->get('lang');
        $inventoryType = $request->get('inventory_type');

        $responseDataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $configuredHotels = $responseDataFormatter->getTripAdvisorConfigs();

        //TODO: Уточнить нужно ли реализовывать
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelInventoryData($apiVersion, $language, $inventoryType, $configuredHotels);

        return new JsonResponse($response);
    }

    /**
     * @Method("POST")
     * @Route("/hotel_availability")
     * @param Request $request
     * @return Response
     */
    public function getHotelAvailabilityAction(Request $request)
    {
//        $apiVersion = $request->get('api_version');
//        $requestedHotels = $request->get('hotels');
//        $startDate = $request->get('start_date');
//        $endDate = $request->get('end_date');
//        $requestedAdultsChildrenCombination = $request->get('party');
//        $language = $request->get('lang');
//        $queryKey = $request->get('query_key');
//        $currency = $request->get('currency');
//        $userCountry = $request->get('user_country');
//        $deviceType = $request->get('device_type');

        $apiVersion = 7;
        $requestedHotels = [["ta_id" => 123, "partner_id" => "5864e3da2f77d9004b580232"]];
        $startDate = '2017-01-12';
        $endDate = '2017-01-18';
        $requestedAdultsChildrenCombination = [["adults" => 2]];
        $language = 'en_US';
        $queryKey = 'sadfafasdf';
        $currency = 'USD';
        $userCountry = 'US';
        $deviceType = 'd';

        $availabilityData = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter')
            ->getAvailabilityData($startDate, $endDate, $requestedHotels);
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelAvailability($availabilityData, $apiVersion, $requestedHotels, $startDate, $endDate,
                $requestedAdultsChildrenCombination, $language, $queryKey, $currency, $userCountry, $deviceType);

        return new JsonResponse($response);
    }

    /**
     * @Method("POST")
     * @Route("/booking_availability")
     * @param Request $request
     * @return string
     */
    public function getBookingAvailabilityAction(Request $request)
    {
//        $apiVersion = $request->get('api_version');
//        $requestedHotel = $request->get('hotel');
//        $startDate = $request->get('start_date');
//        $endDate = $request->get('end_date');
//        $requestedAdultsChildrenCombination = $request->get('party');
//        $language = $request->get('lang');
//        $queryKey = $request->get('query_key');
//        $currency = $request->get('currency');
//        $userCountry = $request->get('user_country');
//        $deviceType = $request->get('device_type');
//        $bookingSessionId = $request->get('booking_session_id');
//        $bookingRequestId = $request->get('booking_request_id');

        $apiVersion = 7;
        $requestedHotel = ["ta_id" => 123, "partner_hotel_code" => "5864e3da2f77d9004b580232"];
        $startDate = '2017-01-12';
        $endDate = '2017-01-18';
        $requestedAdultsChildrenCombination = [["adults" => 2], ["adults" => 1]];
        $language = 'en_US';
        $queryKey = 'sadfafasdf';
        $currency = 'USD';
        $userCountry = 'US';
        $deviceType = 'd';

        $responseDataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $hotel = $responseDataFormatter->getHotelById($requestedHotel['partner_hotel_code']);
        $availabilityData = $responseDataFormatter->getBookingOptionsByHotel($startDate, $endDate, $hotel);

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingAvailability($availabilityData, $hotel, $apiVersion, $requestedHotel, $startDate, $endDate,
                $requestedAdultsChildrenCombination, $language, $queryKey, $userCountry, $deviceType, $currency);

        return new JsonResponse($response);
    }

    /**
     * //TODO: Вернуть POST
     * @Method("GET")
     * @Route("/booking_submit")
     * @param Request $request
     * @return JsonResponse
     */
    public function bookingSubmitAction(Request $request)
    {
//        $checkInDate = $request->get('checkin_date');
//        $checkOutDate = $request->get('checkout_date');
//        $hotelId = $request->get('partner_hotel_code');
//        $bookingSession = $request->get('reference_id');
//        $ipAddress = $request->get('ip_address');
//        $customerData = $request->get('customer');
//        $roomsData = $request->get('rooms');
//        $specialRequests = $request->get('special_requests');
//        $paymentData = $request->get('payment_method');
//        $finalPriceAtBooking = $request->get('final_price_at_booking');
//        $finalPriceAtCheckout = $request->get('final_price_at_checkout');
//        $bookingMainData = $request->get('partner_data');

        $checkInDate = '2017-01-12';
        $checkOutDate = '2017-01-18';
        $hotelId = '5864e3da2f77d9004b580232';
        $bookingSession = '12345';
        $customerData = [
            "first_name" => "Paul",
            "last_name" => "Revere",
            "phone_number" => "5555555555",
            "email" => "paul.revere@tripadvisor.com",
            "country" => "US"
        ];
        $roomsData = [
            [
                "party" => ["adults" => 1, "children" => []],
                "traveler_first_name" => "Paul",
                "traveler_last_name" => "Revere"
            ],
            [
                "party" => ["adults" => 1, "children" => []],
                "traveler_first_name" => "Valera",
                "traveler_last_name" => "Dualist"
            ]
        ];
        $specialRequests = 'A pre-made pillow fort and Vanilla coke on arrival please.';
        $paymentData = [
            "card_type" => "Visa",
            "card_number" => "5454545454545454",
            "cardholder_name" => "Paul Revere",
            "expiration_month" => "01",
            "expiration_year" => "2015",
            "cvv" => "999",
            "billing_address" => [
                "address1" => "141 Needham Street",
                "city" => "newton",
                "state" => "MA",
                "postal_code" => "77777",
                "country" => "US"
            ]
        ];
        $finalPriceAtBooking = [
            "amount" => 100,
            "currency" => "USD"
        ];
        $finalPriceAtCheckout = [
            "amount" => 100,
            "currency" => "USD"
        ];
        $bookingMainData = [
            "pricesByDate" => [
                '1_0' => [
                    "12_01_2017" => 555,
                    "13_01_2017" => 555,
                    "14_01_2017" => 555,
                    "15_01_2017" => 555,
                    "16_01_2017" => 555,
                    "17_01_2017" => 555
                ]
            ],
            "roomTypeId" => "5864fc922f77d901104b57ac",
            "tariffId" => "5864fc912f77d901104b5794",
            "hotelId" => "5864e3da2f77d9004b580232"
        ];

        /** @var TripAdvisorOrderInfo $orderInfo */
        $orderInfo = $this->get('mbh.channel_manager.trip_advisor_order_info')
            ->setInitData($checkInDate, $checkOutDate, $hotelId, $customerData, $roomsData, $specialRequests,
                $paymentData, $finalPriceAtBooking, $finalPriceAtCheckout, $bookingMainData, $bookingSession);

        $bookingCreationResult = $this->get('mbh.channel_manager.order_handler')->createOrder($orderInfo);

        $currency = $finalPriceAtCheckout['currency'];
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatSubmitBookingResponse($bookingSession, $bookingCreationResult,
                $orderInfo->getPackageAndOrderMessages(), $customerData['country'], $roomsData, $currency);

        return new JsonResponse($response);
    }

    /**
     * @Method("GET")
     * @Route("/booking_verify")
     * @param Request $request
     * @return string
     */
    public function bookingVerifyAction(Request $request)
    {
//        $hotelId = $request->get('partner_hotel_code');
        $orderId = $request->get('reservation_id');
        $channelManagerOrderId = $request->get('reference_id');

        $order = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter')
            ->getOrderById($orderId);

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingVerificationResponse($order, $channelManagerOrderId);

        return new JsonResponse($response);
    }

    /**
     * @Method("POST")
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

        return new JsonResponse($response);
    }

    /**
     * @Method("POST")
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

        return new JsonResponse($response);
    }

    /**
     * @Method("POST")
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
            ->formatRoomInformationResponse($apiVersion, $hotelData, $language, $queryKey, $hotel);

        return new JsonResponse($response);
    }
}
