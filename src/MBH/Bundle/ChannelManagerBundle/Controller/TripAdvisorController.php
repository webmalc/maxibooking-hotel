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
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorResponseCompiler;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
            foreach ($tariffs as $tariff) {
                $tripAdvisorTariff = (new TripAdvisorTariff())->setTariff($tariff);
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
     * @Method({"POST", "GET"})
     * @Route("/api/availability")
     * @param Request $request
     * @return JsonResponse
     */
    public function availabilityAction(Request $request)
    {
//        if (!$this->checkBaseAuthorization($request)) {
//            return new JsonResponse(['error' => 'not_authorized'], 403);
//        }

        $content = json_decode($request->getContent(), true);

        $startDate = $this->helper->getDateFromString($content['start_date'],
            TripAdvisorResponseCompiler::TRIP_ADVISOR_DATE_FORMAT);
        $endDate = $this->helper->getDateFromString($content['end_date'],
            TripAdvisorResponseCompiler::TRIP_ADVISOR_DATE_FORMAT);
        $requestedAdultsChildrenCombination = $content['party'];
        $language = $content['lang'];
        $currency = $content['currency'];
        $requestedPayload = $content['requested_payload'];
        $hotelsData = $content['hotels'];
        $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $configs = $dataFormatter->getTripAdvisorConfigs($hotelsData);
        $availabilityData = $dataFormatter->getAvailabilityData($startDate, $endDate, $configs);

        $response = $this->get('mbh.channel_manager.trip_advisor_response_compiler')
            ->getHotelsAvailabilityData($configs, $availabilityData, $requestedAdultsChildrenCombination, $language,
                $currency, $requestedPayload['categories'], $requestedPayload['category_modifiers'], $hotelsData);

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
//        if (!$this->checkBaseAuthorization($request)) {
//            return new JsonResponse(['error' => 'not_authorized'], 403);
//        }

        try {
            $content = json_decode($request->getContent(), true);
            $checkInDate = $content['start_date'];
            $checkOutDate = $content['end_date'];
            $hotelId = $content['partner_hotel_code'];
            $bookingSession = $content['reference_id'];
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

            $orderCreationErrorsData = $this->get('mbh.channel_manager.tripadvisor')->getOrderAvailability($orderInfo, substr($language, 0, 2));

            $responseFormatter = $this->get('mbh.channel_manager.trip_advisor_response_compiler');
            $hotel = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter')
                ->getHotelById($bookingMainData['hotelId']);

            if ($orderCreationErrorsData['isCorrupted']) {
                $response = $responseFormatter->formatSubmitBookingResponse($bookingSession, null,
                    $orderCreationErrorsData['errors'], $hotel);
            } else {
                $bookingCreationResult = $this->get('mbh.channel_manager.order_handler')->createOrder($orderInfo);
                if ($bookingCreationResult instanceof Order) {
                    $bookingCreationResult->addAdditionalData('language', $language);
                    $bookingCreationResult->addAdditionalData('currency', $currency);
                    $bookingCreationResult->addAdditionalData('countryCode', $countryCode);
                    $this->dm->flush();
                    $this->get('mbh.channelmanager.helper')->notify($bookingCreationResult, 'tripadvisor');
                }

                $response = $this->get('mbh.channel_manager.trip_advisor_response_compiler')
                    ->formatSubmitBookingResponse($bookingSession, $bookingCreationResult,
                        $orderInfo->getPackageAndOrderMessages(), $hotel);
            }
        } catch (\Throwable $exception) {
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
//        if (!$this->checkBaseAuthorization($request)) {
//            return new JsonResponse(['error' => 'not_authorized'], 403);
//        }

//        $hotelId = $request->get('partner_hotel_code');
        $orderId = $request->get('reservation_id');
        $channelManagerOrderId = $request->get('reference_id');
        $hotelId = $request->get('partner_hotel_code');

        $dataFormatter = $this->get('mbh.channel_manager.trip_advisor_response_data_formatter');
        $order = $dataFormatter->getOrderById($orderId);
        $hotel = $dataFormatter->getHotelById($hotelId);
        $response = $this->get('mbh.channel_manager.trip_advisor_response_compiler')
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
//        if (!$this->checkBaseAuthorization($request)) {
//            return new JsonResponse(['error' => 'not_authorized'], 403);
//        }

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

        $response = $this->get('mbh.channel_manager.trip_advisor_response_compiler')
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

            $response = $this->get('mbh.channel_manager.trip_advisor_response_compiler')
                ->formatRoomInformationResponse($apiVersion, $hotelData, $language, $queryKey, $hotel);
        } catch (\Throwable $exception) {
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
