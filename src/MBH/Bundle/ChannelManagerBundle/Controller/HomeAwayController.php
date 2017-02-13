<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom;
use MBH\Bundle\ChannelManagerBundle\Form\HomeAwayRoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\HomeAwayType;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\BaseBundle\Controller\BaseController;

/**
 * @Route("/homeaway")
 */
class HomeAwayController extends BaseController
{
    /**
     * @Route("/", name="homeaway")
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();

        if (!$config) {
            $config = new HomeAwayConfig();
            $config->setHotel($this->hotel);
        }

        $form = $this->createForm(HomeAwayType::class, $config, [
            'hotel' => $this->hotel
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->dm->persist($config);
            $this->dm->flush();
            $this->hotel->setHomeAwayConfig($config);

            $this->addFlash('success',
                $this->get('translator')->trans('controller.homeAwayController.settings_saved_success'));
        }

        return [
            'config' => $this->hotel->getHomeAwayConfig(),
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="homeaway_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $roomTypes = [
            '123' => 'Первая комната',
            '124' => 'Вторая комната'
        ];

        $rentalAgreementFieldPrefix = 'agreement';
        $roomFieldPrefix = 'room';

        $form = $this->createForm(HomeAwayRoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            //TODO: Вернуть когда будет реализована аутентификация
//            'booking' => $this->get('mbh.channelmanager.homeaway')->getRoomTypes(),
            'booking' => $roomTypes,
            'room_field_prefix' => $roomFieldPrefix,
            'rental_agreement_field_prefix' => $rentalAgreementFieldPrefix
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
            $formData = $form->getData();
            $groupedFormData = [];
            foreach ($formData as $index => $value) {
                if (!is_null($value)) {
                    if ($value instanceof RoomType) {
                        $homeAwayUnitId = substr($index, strlen($roomFieldPrefix));
                        $groupedFormData[$homeAwayUnitId]['roomType'] = $value;
                    } else {
                        if (!empty(trim($value))) {
                            $homeAwayUnitId = substr($index, strlen($rentalAgreementFieldPrefix));
                            $groupedFormData[$homeAwayUnitId]['agreement'] = $value;
                        }
                    }
                }
            }
            foreach ($groupedFormData as $homeAwayUnitId => $roomTypeData) {
                if (count($roomTypeData) == 2) {
                    $configRoom = new HomeAwayRoom();
                    $configRoom->setRoomType($roomTypeData['roomType'])
                        ->setRoomId($homeAwayUnitId)
                        ->setRentalAgreement($roomTypeData['agreement']);

                    $config->addRoom($configRoom);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $this->addFlash('success',
                $this->get('translator')->trans('controller.homeAwayController.settings_saved_success'));

            return $this->redirect($this->generateUrl('homeaway_room'));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Route("/rates/{listingId}", name="homeaway_rates")
     * @param Request $request
     * @return Response
     */
    public function ratesAction(Request $request)
    {
//        $this->get('mbh.channelmanager.homeaway_data_formatter')->formatListingContentIndex()
        return new Response();
    }

    /**
     * @Route("/routes/{listingId}", name="homeaway_availability")
     * @param $listingId
     * @return Response
     */
    public function availabilityAction($listingId)
    {
        $this->get('mbh.channelmanager.homeaway_data_formatter')->formatAvailabilityData($listingId);

        return new Response();
    }

    public function quoteRequestAction(Request $request)
    {

    }

    public function bookingRequestAction(Request $request)
    {
        //TODO: Поменять название
        $bookingRequest = $request->get('xml');
        $bookingRequestXML = new \SimpleXMLElement($bookingRequest);
        $documentVersion = (string)$bookingRequestXML->documentVersion;
        $bookingRequestDetails = $bookingRequestXML->bookingRequestDetails[0];
        $config = $this->hotel->getHomeAwayConfig();
        $orderInfo = $this->get('mbh.channelmanager.homeaway_order_info')->setInitData($bookingRequestDetails, $config);
        $resultOfCreation = $this->get('mbh.channel_manager.order_handler')->createOrder($orderInfo);
        $bookingCreationResponse = $this->get('mbh.channelmanager.homeaway_data_formatter')
            ->getBookingResponse($documentVersion, $orderInfo, $orderInfo->getMessages());


    }

    /**
     * @Route("/test")
     */
    public function testAction(Request $request)
    {
        $response = $this->get('mbh.channelmanager.homeaway')->testRequest();
        return new Response(true ? 'true' : 'false');
    }
}