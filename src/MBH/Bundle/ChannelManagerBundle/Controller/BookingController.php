<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\BookingType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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

        $form = $this->createForm(
            BookingType::class,
            $config
        );

        return [
            'doc' => $config,
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
            $this->get('mbh.channelmanager')->pullOrders('booking', true);
            $this->addFlash(
                'warning',
                $this->get('translator')->trans('controller.bookingController.packages_sync_start')
            );
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
     * @return Response
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->hotel;
        $config = $hotel->getBookingConfig();

        if (!$config) {
            $config = new BookingConfig();
            $config->setHotel($hotel);
        }
        $form = $this->createForm(
            BookingType::class,
            $config
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($config);
            $dm->flush();

            $this->get('mbh.channelmanager.booking')->syncServices($config);
            $this->get('mbh.channelmanager')->updateInBackground();

            $request->getSession()->getFlashBag()
                ->set(
                    'success',
                    $this->get('translator')->trans('controller.bookingController.settings_saved_success')
                );

            return $this->redirect($this->generateUrl('booking'));
        }

        return [
            'doc' => $config,
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
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getBookingConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.booking')->pullRooms($config),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
            foreach ($form->getData() as $id => $roomType) {
                if ($roomType) {
                    $configRoom = new Room();
                    $configRoom->setRoomType($roomType)->setRoomId($id);
                    $config->addRoom($configRoom);
                    $this->dm->persist($config);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $request->getSession()->getFlashBag()
                ->set(
                    'success',
                    $this->get('translator')->trans('controller.bookingController.settings_saved_success')
                );

            return $this->redirect($this->generateUrl('booking_room'));
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
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getBookingConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffsType::class, $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.booking')->pullTariffs($config),
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

            $request->getSession()->getFlashBag()
                ->set(
                    'success',
                    $this->get('translator')->trans('controller.bookingController.settings_saved_success')
                );

            return $this->redirect($this->generateUrl('booking_tariff'));
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
            'doc' => $config,
            'logs' => $this->logs($config)
        ];
    }
}
