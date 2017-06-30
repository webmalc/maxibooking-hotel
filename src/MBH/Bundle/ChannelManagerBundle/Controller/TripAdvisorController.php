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
        $confirmationUrl = $this->getParameter('trip_advisor_confirmation_url');
        $unfilledData = $this->get('mbh.channel_manager.tripadvisor')
            ->getHotelUnfilledRequiredFields($this->hotel, $confirmationUrl);

        $unfilledStringData = '';
        if (count($unfilledData) > 0) {
            $unfilledStringData = $this->getUnfilledString($unfilledData,
                'controller.trip_advisor_controller.unfilled_hotel_data.error');
        }

        $mainTariffUnfilledFields = [];
        if (!is_null($config->getMainTariff())) {
            $mainTariffUnfilledFields = $this->get('mbh.channel_manager.tripadvisor')
                ->getTariffRequiredUnfilledFields($config->getMainTariff());
        }
        if (count($mainTariffUnfilledFields) > 0) {
            empty($unfilledStringData) ?: $unfilledStringData .= '<br>';
            $unfilledStringData .= $this->getUnfilledString($mainTariffUnfilledFields,
                'controller.trip_advisor_controller.unfilled_hotel_data.main_tariff.error');
        }

        if (empty($unfilledStringData) && $form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($config);
            $this->addFlash('success', 'controller.tripadvisor_controller.settings_saved_success');
            $this->dm->flush();
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

        $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')->findBy(['hotel.id' => $this->hotel->getId()]);
        if (count($config->getTariffs()) == 0) {
            $mainTariffId = $config->getMainTariff()->getId();
            foreach ($tariffs as $tariff) {
                $tripAdvisorTariff = (new TripAdvisorTariff())->setTariff($tariff);
                if ($tariff->getId() == $mainTariffId) {
                    $tripAdvisorTariff->setIsEnabled(true);
                }
                $config->addTariff($tripAdvisorTariff);
            }
        }

        $requiredFieldsErrors = [];
        $translator = $this->get('translator');
        foreach ($tariffs as $tariff) {
            $unfilledFieldsString = '';
            $requiredUnfilledFields = $this->get('mbh.channel_manager.tripadvisor')->getTariffRequiredUnfilledFields($tariff);
            if (count($requiredUnfilledFields) > 0) {
                foreach ($requiredUnfilledFields as $unfilledDatum) {
                    $unfilledFieldsString .= '<br>"' . $translator->trans($unfilledDatum) . '", ';
                }
                $unfilledFieldsString = $translator->trans('controller.trip_advisor_controller.unfilled_tariff_data.error',
                    ['%fields%' => rtrim($unfilledFieldsString, ', '), '%tariffName%' => $tariff->getName()]);

            }
            $requiredFieldsErrors[] = $unfilledFieldsString;
        }

        $form = $this->createForm(TripAdvisorTariffsType::class, $config, [
            'hotel' => $this->hotel,
            'unfilledFieldErrors' => $requiredFieldsErrors
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
            ->findBy(['hotel.id' => $this->hotel->getId()], ['fullTitle' => 'ASC']);

        foreach ($roomTypes as $roomType) {
            if (is_null($config->getTARoomTypeByMBHRoomTypeId($roomType->getId()))) {
                $config->addRoom((new TripAdvisorRoomType())->setRoomType($roomType));
            }
        }

        $requiredFieldsErrors = [];
        foreach ($roomTypes as $roomType) {
            $translator = $this->get('translator');
            $unfilledFieldsString = '';
            $requiredUnfilledFields = $this->get('mbh.channel_manager.tripadvisor')->getRoomTypeRequiredUnfilledFields($roomType);
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
     * @Method({"POST"})
     * @Route("/api/hotel_availability")
     * @param Request $request
     * @return Response
     */
    public function getHotelAvailabilityAction(Request $request)
    {
        if (!$this->checkBaseAuthorization($request)) {
            return new JsonResponse(['error' => 'not_authorized'], 403);
        }

        $apiVersion = $request->get('api_version');
        $requestedHotels = json_decode($request->get('hotels'), true);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $requestedAdultsChildrenCombination = json_decode($request->get('party'), true);
        $language = $request->get('lang');
        $queryKey = $request->get('query_key');
        $currency = $request->get('currency');
        $userCountry = $request->get('user_country');
        $deviceType = $request->get('device_type');

        $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $configs = $dataFormatter->getTripAdvisorConfigs($requestedHotels);
        $availabilityData = $dataFormatter->getAvailabilityData($startDate, $endDate, $configs);
        $taHotelIds = [];
        foreach ($requestedHotels as $requestedHotelData) {
            $taHotelIds[] = $requestedHotelData['ta_id'];
        }
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatHotelAvailability($availabilityData, $apiVersion, $requestedHotels, $startDate, $endDate,
                $requestedAdultsChildrenCombination, $language, $queryKey, $currency, $userCountry, $deviceType,
                $configs);

        return new JsonResponse($response);
    }

    /**
     * @Method({"POST", "GET"})
     * @Route("/api/booking_availability")
     * @param Request $request
     * @return string
     */
    public function getBookingAvailabilityAction(Request $request)
    {
        if (!$this->checkBaseAuthorization($request)) {
            return new JsonResponse(['error' => 'not_authorized'], 403);
        }

        $apiVersion = $request->get('api_version');
        $requestedHotel = json_decode($request->get('hotel'), true);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $requestedAdultsChildrenCombination = json_decode($request->get('party'), true);
        $language = $request->get('lang');
        $queryKey = $request->get('query_key');
        $currency = $request->get('currency');
        $userCountry = $request->get('user_country');
        $deviceType = $request->get('device_type');
//        $bookingSessionId = $request->get('booking_session_id');
//        $bookingRequestId = $request->get('booking_request_id');

        $responseDataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $hotel = $responseDataFormatter->getHotelById($requestedHotel['partner_hotel_code']);
        $availabilityData = $responseDataFormatter->getBookingOptionsByHotel($startDate, $endDate, $hotel);

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingAvailability($availabilityData, $hotel, $apiVersion, $requestedHotel, $startDate, $endDate,
                $requestedAdultsChildrenCombination, $language, $queryKey, $userCountry, $deviceType, $currency);

        return new JsonResponse($response);
    }

    public function availabilityAction(Request $request)
    {
        if (!$this->checkBaseAuthorization($request)) {
            return new JsonResponse(['error' => 'not_authorized'], 403);
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $requestedAdultsChildrenCombination = json_decode($request->get('party'), true);
        $language = $request->get('lang');
        $queryKey = $request->get('query_key');
        $currency = $request->get('currency');
        $userCountry = $request->get('user_country');
        $requestedPayload = json_decode($request->get('requested_payload'), true);
        $hotelsData = json_decode($request->get('hotels'), true);

        $configs = $this->get('mbh.channelmanager.expedia_request_data_formatter')->getTripAdvisorConfigs($hotelsData);

        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatAvailability($startDate, $endDate, $requestedAdultsChildrenCombination, $language, $queryKey,
                $currency, $userCountry, $requestedPayload, $configs);

        return new JsonResponse($response);
    }

    /**
     * @Method({"POST", "GET"})
     * @Route("/api/booking_submit")
     * @param Request $request
     * @return JsonResponse
     */
    public function bookingSubmitAction(Request $request)
    {
        if (!$this->checkBaseAuthorization($request)) {
            return new JsonResponse(['error' => 'not_authorized'], 403);
        }

        try {
            $content = json_decode($request->getContent(), true);
            $checkInDate = $content['checkin_date'];
            $checkOutDate = $content['checkout_date'];
            $hotelId = $content['partner_hotel_code'];
            $bookingSession = $content['reference_id'];
//        $ipAddress = $content['ip_address'];
            $customerData = $content['customer'];
            $roomsData = $content['rooms'];
            $specialRequests = isset($content['special_requests']) ? $content['special_requests'] : '';
            $paymentData = $content['payment_method'];
            $finalPriceAtBooking = $content['final_price_at_booking'];
            $finalPriceAtCheckout = $content['final_price_at_checkout'];
            $bookingMainData = $content['partner_data'];
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
                    $this->get('mbh.channelmanager.helper')->notify($bookingCreationResult, 'tripadvisor');
                }

                $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
                    ->formatSubmitBookingResponse($bookingSession, $bookingCreationResult,
                        $orderInfo->getPackageAndOrderMessages(), $hotel);
            }} catch (\Throwable $exception) {
            $response = ['error' => $exception->getMessage()];
        }

        return new JsonResponse($response);
    }

    /**
     * @Method("GET")
     * @Route("/api/booking_verify")
     * @param Request $request
     * @return string
     */
    public function bookingVerifyAction(Request $request)
    {
        if (!$this->checkBaseAuthorization($request)) {
            return new JsonResponse(['error' => 'not_authorized'], 403);
        }

//        $hotelId = $request->get('partner_hotel_code');
        $orderId = $request->get('reservation_id');
        $channelManagerOrderId = $request->get('reference_id');
        $hotelId = $request->get('partner_hotel_code');

        $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $order = $dataFormatter->getOrderById($orderId);
        $hotel = $dataFormatter->getHotelById($hotelId);
        $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
            ->formatBookingVerificationResponse($order, $channelManagerOrderId, $hotel);

        return new JsonResponse($response);
    }

    /**
     * @Method("POST")
     * @Route("/api/booking_cancel")
     * @param Request $request
     * @return string
     */
    public function bookingCancelAction(Request $request)
    {
        if (!$this->checkBaseAuthorization($request)) {
            return new JsonResponse(['error' => 'not_authorized'], 403);
        }

        $requestData = json_decode($request->getContent(), true);
        $hotelId = $requestData['partner_hotel_code'];
        $orderId = $requestData['reservation_id'];

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
                $this->get('mbh.channelmanager.helper')->notify($order, 'tripadvisor', 'delete');
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
     * @Method({"POST", "GET"})
     * @Route("/api/room_information")
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function roomInformationAction(Request $request)
    {
        if (!$this->checkBaseAuthorization($request)) {
            return new JsonResponse(['error' => 'not_authorized'], 403);
        }

        $apiVersion = $request->get('api_version');
        $hotelData = json_decode($request->get('hotel'), true);
        $language = $request->get('language_request');
        $queryKey = $request->get('unique_query_key');

        try {
            $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
            $hotel = $dataFormatter->getHotelById($hotelData['partner_hotel_code']);

            $response = $this->get('mbh.channel_manager.trip_advisor_response_formatter')
                ->formatRoomInformationResponse($apiVersion, $hotelData, $language, $queryKey, $hotel);
        } catch(\Throwable $exception) {
            $response = ['error' => $exception->getMessage(), 'hotelData' => $request->getContent()];
        }

        return new JsonResponse($response);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function checkBaseAuthorization(Request $request)
    {
        $baseUsername = $this->getParameter('api_username');
        $basePassword = $this->getParameter('api_password');

        return $request->headers->get('authorization') == 'Basic '
            . base64_encode("$baseUsername:$basePassword");
    }

    /**
     * @param $unfilledFields
     * @param $transId
     * @return string
     */
    private function getUnfilledString($unfilledFields, $transId)
    {
        $translator = $this->get('translator');
        $unfilledFieldsString = '';
        foreach ($unfilledFields as $unfilledField) {
            $unfilledFieldsString .= '<br>"' . $translator->trans($unfilledField) . '", ';
        }

        $unfilledFieldsString = rtrim($unfilledFieldsString, ', ');
        $unfilledStringData = $translator->trans($transId, ['%fields%' => $unfilledFieldsString]);

        return $unfilledStringData;
    }
}
