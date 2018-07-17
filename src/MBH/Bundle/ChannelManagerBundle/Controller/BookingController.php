<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Document\BookingRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\BookingRoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\BookingType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use MBH\Bundle\ChannelManagerBundle\Services\ChannelManager;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * @Route("/booking")
 */
class BookingController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="booking")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getBookingConfig();

        $isReadyResult = $this->get('mbh.channelmanager')->checkForReadinessOrGetStepUrl($config, 'booking');
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }
        
        $form = $this->createForm(
            BookingType::class,
            $config
        );

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Sync old packages
     * @Route("/packages/sync", name="booking_packages_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKING')")
     */
    public function syncPackages()
    {
        $config = $this->hotel->getBookingConfig();
        if ($config) {
            $this->get('mbh.channelmanager')->pullOrders('booking', ChannelManager::OLD_PACKAGES_PULLING_PARTLY_STATUS);
            $this->addFlash(
                'warning',
                $this->get('translator')->trans('controller.bookingController.packages_sync_start')
            );
        }

        return $this->redirect($this->generateUrl('booking'));
    }

    /**
     * Sync all old packages
     * @Route("/packages/sync_all", name="booking_all_packages_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKING')")
     */
    public function syncAllPackages()
    {
        $config = $this->hotel->getBookingConfig();
        if ($config) {
            $this->get('mbh.channelmanager')->pullOrdersInBackground('booking', true);
            $this->addFlash('warning', 'controller.bookingController.packages_sync_start');
        }

        return $this->redirect($this->generateUrl('booking'));
    }
    
    /**
     * Main configuration save
     * @Route("/", name="booking_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template("MBHChannelManagerBundle:Booking:index.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->hotel;
        $config = $hotel->getBookingConfig();

        if (!$config) {
            $config = new BookingConfig();
            $config->setHotel($hotel);
        }
        $form = $this->createForm(BookingType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($config);
            $this->dm->flush();

//            $this->get('mbh.channelmanager.booking')->syncServices($config);
//            $this->get('mbh.channelmanager')->updateInBackground();

            $this->addFlash('success','controller.bookingController.settings_saved_success');

            return $this->redirect($this->generateUrl('booking'));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="booking_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getBookingConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(BookingRoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.booking')->pullRooms($config),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();

            $bookingRoomsDataByRoomIds = [];
            foreach ($form->getData() as $fieldName => $fieldData) {
                $fieldsPrefixes = [BookingRoomsType::ROOM_TYPE_FIELD_PREFIX, BookingRoomsType::SINGLE_PRICES_FIELD_PREFIX];
                foreach ($fieldsPrefixes as $prefix) {
                    if ($this->helper->startsWith($fieldName, $prefix)) {
                        $roomId = substr($fieldName, strlen($prefix));
                        isset($bookingRoomsDataByRoomIds[$roomId])
                            ? $bookingRoomsDataByRoomIds[$roomId][$prefix] = $fieldData
                            : $bookingRoomsDataByRoomIds[$roomId] = [$prefix => $fieldData];
                    }
                }
            }

            foreach ($bookingRoomsDataByRoomIds as $roomId => $roomData) {
                if (!empty($roomData[BookingRoomsType::ROOM_TYPE_FIELD_PREFIX])) {
                    $room = (new BookingRoom())
                        ->setRoomType($roomData[BookingRoomsType::ROOM_TYPE_FIELD_PREFIX])
                        ->setRoomId($roomId)
                        ->setUploadSinglePrices($roomData[BookingRoomsType::SINGLE_PRICES_FIELD_PREFIX])
                    ;
                    $config->addRoom($room);
                }
            }

            $this->dm->flush();
            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.bookingController.settings_saved_success');

            $redirectRouteName = $config->isReadyToSync() ? 'booking_room' : 'booking_tariff';

            return $this->redirect($this->generateUrl($redirectRouteName));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="booking_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getBookingConfig();
        $inGuide = !$config->isReadyToSync();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffsType::class, $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.booking')->pullTariffs($config),
            'constraints' => [new Callback([TariffsType::class, 'check'])]
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllTariffs();
            foreach ($form->getData() as $id => $tariff) {
                if ($tariff) {
                    $configTariff = new Tariff();
                    $configTariff->setTariff($tariff)->setTariffId($id);
                    $config->addTariff($configTariff);
                    $this->dm->persist($config);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success','controller.bookingController.settings_saved_success');

            $redirectRouteName = $inGuide ? 'booking_all_packages_sync' : 'booking_tariff';

            return $this->redirectToRoute($redirectRouteName);
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Services configuration page
     * @Route("/service", name="booking_service")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template()
     */
    public function serviceAction()
    {
        $config = $this->get('mbh.hotel.selector')->getSelected()->getBookingConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        return [
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }
}
