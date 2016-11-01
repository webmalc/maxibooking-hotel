<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\ChannelManagerBundle\Form\OktogoType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;


/**
 * @Route("/oktogo")
 */
class OktogoController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="oktogo")
     * @Method("GET")
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getOktogoConfig();
        $form = $this->createForm(
            $this->get('mbh.channelmanager.oktogo_type'), $config
        );

        return [
            'doc' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Main configuration page save
     * @Route("/", name="oktogo_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template("MBHChannelManagerBundle:Oktogo:index.html.twig")
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

        if (!$entity) {
            $entity = new OktogoConfig();
            $entity->setHotel($hotel);
            $new = true;
        }

        $form = $this->createForm(
            $this->get('mbh.channelmanager.oktogo_type'), $entity
        );

//        $form->submit($request);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();


            $this->get('mbh.channelmanager.oktogo')->syncServices($entity);
            $this->get('mbh.channelmanager')->updateInBackground();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.oktogoController.settings_saved_success'));


            return $this->redirect($this->generateUrl('oktogo'));
        }

        return [
            'doc' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/room", name="oktogo_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function roomAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $config = $this->hotel->getOktogoConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new RoomsType, $config->getRoomsAsArray(), [
                'hotel' => $this->hotel,
                'booking' => $this->get('mbh.channelmanager.oktogo')->pullRooms($config),
            ]
        );

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
                ->set('success',
                    $this->get('translator')->trans('controller.bookingController.settings_saved_success'));

            return $this->redirect($this->generateUrl('oktogo_room'));
        }

        return array(
            'config' => $config,
            'logs' => $this->logs($config),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/tariff/sync", name="oktogo_tariff_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function tariffSyncAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $result = $this->get('mbh.channelmanager.oktogo')->tariffSync($entity);

        if ($result) {
            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.oktogoController.tariffs_sync_success'));

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();
        } else {
            $request->getSession()->getFlashBag()
                ->set('danger', $this->get('translator')->trans('controller.oktogoController.sync_error'));
        }

        return $this->redirect($this->generateUrl('oktogo_tariff'));
    }

    /**
     * @Route("/tariff", name="oktogo_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function tariffAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $config = $hotel->getOktogoConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }
        $this->get('mbh.channelmanager.oktogo')->syncServices($config);
        $this->get('mbh.channelmanager.oktogo')->pullOrders();
//        $this->get('mbh.channelmanager.oktogo')->updateRooms();
//        $this->get('mbh.channelmanager.oktogo')->updatePrices();
//        $this->get('mbh.channelmanager.oktogo')->closeForConfig($config);
//        $this->get('mbh.channelmanager.oktogo')->updateRestrictions();

        $form = $this->createForm(new TariffsType(), $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.oktogo')->pullTariffs($config),
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
                ->set('success',
                    $this->get('translator')->trans('controller.bookingController.settings_saved_success'));

            return $this->redirect($this->generateUrl('oktogo_tariff'));
        }

        return array(
            'config' => $config,
            'logs' => $this->logs($config),
            'form' => $form->createView(),
        );
    }

    /**
     * Services configuration page
     * @Route("/service", name="oktogo_service")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template()
     */
    public function serviceAction()
    {
        $config = $this->get('mbh.hotel.selector')->getSelected()->getOktogoConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        return [
            'doc' => $config,
            'logs' => $this->logs($config)
        ];
    }
}
