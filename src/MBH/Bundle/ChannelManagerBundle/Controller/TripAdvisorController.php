<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorRoomType;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorTariff;
use MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor\TripAdvisorType;
use MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor\TripAdvisorTariffsType;
use MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor\TripAdvisorRoomTypesForm;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorOrderInfo;
use MBH\Bundle\PackageBundle\Document\Order;
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
            $config->setIsEnabled(false);
        }

        $languages = $this->getParameter('full_locales');
        $paymentTypes = $this->getParameter('mbh.online.form')['payment_types'];
        array_splice($paymentTypes, 2, 1);
        $form = $this->createForm(TripAdvisorType::class, $config, [
            'hotel' => $this->hotel,
            'languages' => $languages,
            'payment_types' => $paymentTypes
        ]);

        $form->handleRequest($request);
        $unfilledData = $this->get('mbh.channelmanager.helper')->getHotelUnfilledRequiredFields($this->hotel);
        $unfilledStringData = '';
        if (count($unfilledData) > 0) {
            $translator = $this->get('translator');
            $unfilledFieldsString = '';
            foreach ($unfilledData as $unfilledDatum) {
                $unfilledFieldsString .= '<br>"' . $translator->trans($unfilledDatum) . '", ';
            }
            $unfilledFieldsString = rtrim($unfilledFieldsString, ', ');
            $unfilledStringData = $translator->trans('controller.trip_advisor_controller.unfilled_hotel_data.error',
                ['%fields%' => $unfilledFieldsString]);
        } elseif ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($config);
            $this->dm->flush();
            $this->addFlash('success', 'controller.tripadvisor_controller.settings_saved_success');
        }

        return [
            'unfilledFieldsString' => $unfilledStringData,
            'doc' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Route("/tariffs", name="tripadvisor_tariff")
     * @Security("is_granted('ROLE_TRIPADVISOR')")
     * @Template()
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffsAction(Request $request)
    {
        $config = $this->hotel->getTripAdvisorConfig();
        if (is_null($config)) {
            $this->addFlash('error', 'controller.tripadvisor_controller.config_not_found');

            return $this->redirectToRoute('tripadvisor');
        }

        if (count($config->getTariffs()) == 0) {
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->findBy(['hotel.id' => $this->hotel->getId()]);
            $mainTariffId = $config->getMainTariff()->getId();
            foreach ($tariffs as $tariff) {
                $tripAdvisorTariff = (new TripAdvisorTariff())->setTariff($tariff);
                if ($tariff->getId() == $mainTariffId) {
                    $tripAdvisorTariff->setIsEnabled(true);
                }
                $config->addTariff($tripAdvisorTariff);
            }
        }

        $form = $this->createForm(TripAdvisorTariffsType::class, $config, [
            'hotel' => $this->hotel,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
     * @Route("/rooms", name="tripadvisor_room")
     * @Security("is_granted('ROLE_TRIPADVISOR')")
     * @Template()
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roomsAction(Request $request)
    {
        $config = $this->hotel->getTripAdvisorConfig();
        if (is_null($config)) {
            $this->addFlash('error', 'controller.tripadvisor_controller.config_not_found');

            return $this->redirectToRoute('tripadvisor');
        }

        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->findBy(['hotel.id' => $this->hotel->getId()]);

        if (count($config->getRooms()) == 0) {
            foreach ($roomTypes as $roomType) {
                $config->addRoom((new TripAdvisorRoomType())->setRoomType($roomType));
            }
        }

        $requiredFieldsErrors = [];
        foreach ($roomTypes as $roomType) {
            $translator = $this->get('translator');
            $unfilledFieldsString = '';
            $requiredUnfilledFields = $this->get('mbh.channelmanager.helper')->getRoomTypeRequiredUnfilledFields($roomType);
            if (count($requiredUnfilledFields) > 0) {
                foreach ($requiredUnfilledFields as $unfilledDatum) {
                    $unfilledFieldsString .= '<br>"' . $translator->trans($unfilledDatum) . '", ';
                }
                $unfilledFieldsString = $translator->trans('controller.trip_advisor_controller.unfilled_room_type_data.error',
                    ['%fields%' => rtrim($unfilledFieldsString, ', '), '%roomTypeName%' => $roomType->getName()]);

            }
            $requiredFieldsErrors[] = $unfilledFieldsString;
        }

        $form = $this->createForm(TripAdvisorRoomTypesForm::class, $config, [
            'hotel' => $this->hotel,
            'requiredFieldsErrors' => $requiredFieldsErrors
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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

        $responseDataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $configuredHotels = $responseDataFormatter->getTripAdvisorConfigs();

        //TODO: Уточнить нужно ли реализовывать
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelInventoryData($apiVersion, $language, $configuredHotels);

        return new JsonResponse($response);
    }

    //TODO: Поставить POST после тестов
    /**
     * @Method("GET")
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
        $requestedHotels = [["ta_id" => 123, "partner_id" => "58b937c5a84718004a438a52"]];
        $startDate = '2017-03-05';
        $endDate = '2017-03-06';
        $requestedAdultsChildrenCombination = [["adults" => 2, 'children' => [7]], ["adults" => 2]];
        $language = 'en_US';
        $queryKey = 'sadfafasdf';
        $currency = 'USD';
        $userCountry = 'US';
        $deviceType = 'd';
        //TODO: Учесть возможность того, что не будет актуален отель
        //TODO: Разобраться, если несколько комнат
        $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $availabilityData = $dataFormatter->getAvailabilityData($startDate, $endDate, $requestedHotels);
        $taHotelIds = [];
        foreach ($requestedHotels as $requestedHotelData) {
            $taHotelIds[] = $requestedHotelData['ta_id'];
        }
        $requestedHotelConfigs = $dataFormatter->getTripAdvisorConfigs($taHotelIds);
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelAvailability($availabilityData, $apiVersion, $requestedHotels, $startDate, $endDate,
                $requestedAdultsChildrenCombination, $language, $queryKey, $currency, $userCountry, $deviceType);

        return new JsonResponse($response);
    }

    //TODO: Вернуть POST
    /**
     * @Method("GET")
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
        $requestedHotel = ["ta_id" => 123, "partner_hotel_code" => "58b937c5a84718004a438a52"];
        $startDate = '2017-03-12';
        $endDate = '2017-03-18';
        $requestedAdultsChildrenCombination = [["adults" => 1], ["adults" => 1]];
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

        $checkInDate = '2017-03-12';
        $checkOutDate = '2017-03-18';
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
            "amount" => 255.14,
            "currency" => "USD"
        ];
        $finalPriceAtCheckout = [
            "amount" => 0,
            "currency" => "USD"
        ];
        $bookingMainData = [
            "pricesByDate" => [
                '1_0' => [
                    "12_01_2017" => 1234,
                    "13_01_2017" => 1234,
                    "14_01_2017" => 1234,
                    "15_01_2017" => 1234,
                    "16_01_2017" => 1234,
                    "17_01_2017" => 1234
                ]
            ],
            "roomTypeId" => "58b93c03a8471801ee458562",
            "tariffId" => "58b93bd4a8471801dc3eb862",
            "hotelId" => "58b937c5a84718004a438a52",
            'language' => 'en_US'
        ];

        $language = $bookingMainData['language'];
        $currency = $finalPriceAtCheckout['currency'];
        $countryCode = $customerData['country'];

        /** @var TripAdvisorOrderInfo $orderInfo */
        $orderInfo = $this->get('mbh.channel_manager.trip_advisor_order_info')
            ->setInitData($checkInDate, $checkOutDate, $hotelId, $customerData, $roomsData, $specialRequests,
                $paymentData, $finalPriceAtBooking, $finalPriceAtCheckout, $bookingMainData, $bookingSession,
                $currency);

        $orderHandler = $this->get('mbh.channel_manager.order_handler');
        $orderCreationErrorsData = $orderHandler->getOrderAvailability($orderInfo, substr($language, 0, 2));

        $responseFormatter = $this->get('mbh.channel_manager.trip_advisor_response_formatter');
        $hotel = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter')
            ->getHotelById($bookingMainData['hotelId']);

        if ($orderCreationErrorsData['isCorrupted']) {
            $response = $responseFormatter->formatSubmitBookingResponse($bookingSession, null,
                $orderCreationErrorsData['errors'], $hotel);
        } else {
            $bookingCreationResult = $orderHandler->createOrder($orderInfo);
            if ($bookingCreationResult instanceof Order) {
                $bookingCreationResult->addAdditionalData('language', $language);
                $bookingCreationResult->addAdditionalData('currency', $currency);
                $bookingCreationResult->addAdditionalData('countryCode', $countryCode);
                $this->dm->flush();
            }

            $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
                ->formatSubmitBookingResponse($bookingSession, $bookingCreationResult,
                    $orderInfo->getPackageAndOrderMessages(), $hotel);
        }

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

        $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $order = $dataFormatter->getOrderById($orderId);
        //TODO: Проверку на наличие брони
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingVerificationResponse($order, $channelManagerOrderId);

        return new JsonResponse($response);
    }

    //TODO: Вернуть POST
    /**
     * @Method("GET")
     * @Route("/booking_cancel")
     * @param Request $request
     * @return string
     */
    public function bookingCancelAction(Request $request)
    {
        $hotelId = $request->get('partner_hotel_code');
        $orderId = $request->get('reservation_id');

        $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $order = $dataFormatter->getOrderById($orderId, true);
        $hotel = $dataFormatter->getHotelById($hotelId);
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
            ->formatBookingCancelResponse($removalStatus, $hotel, $orderId);

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

    /**
     * @Route("/test")
     * @return Response
     */
    public function testAction()
    {
        return new Response();
    }
}
