<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Document\Room;

/**
 * Class HundredOneHotelsController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 * @Route("/hundredOneHotels")
 */
class HundredOneHotelsController extends Controller
{
    /**
     * Main configuration page
     * @Route("/", name="hundred_one_hotels")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getHundredOneHotelsConfig();

        $form = $this->createForm($this->get('mbh.channelmanager.hundred_one_hotels_type'), $config);

        return [
            'form' => $form->createView(),
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Template()
     * @Route("/test")
     */
    public function testAction()
    {
        $firstDate = \DateTime::createFromFormat('d.m.Y', '4.3.2017');
        $secondTime = \DateTime::createFromFormat('d.m.Y', '10.3.2017');
        $json = $this->get('mbh.channelmanager.hundred_one_hotels')->pullOrders();
        $a = 1;
        return [
            'json' => count($json)
        ];
    }

    /**
     * @Route("/", name="hundred_one_hotels_save")
     * @Method("POST")
     * @Template("MBHChannelManagerBundle:HundredOneHotels:index.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        $config = $this->hotel->getHundredOneHotelsConfig();

        if (!$config) {
            $config = new HundredOneHotelsConfig();
            $config->setHotel($this->hotel);
        }
        $form = $this->createForm($this->get('mbh.channelmanager.hundred_one_hotels_type'), $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /* @var $dm DocumentManager; */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($config);
            $dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $this->addFlash('success',
                $this->get('translator')->trans('controller.bookingController.settings_saved_success'));
        }

        return $this->redirectToRoute('hundred_one_hotels');
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="hundred_one_hotels_tariff")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getHundredOneHotelsConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new TariffsType(), $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.hundred_one_hotels')->pullTariffs($config),
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
            $this->addFlash('success',
                $this->get('translator')->trans('controller.bookingController.settings_saved_success'));

            return $this->redirectToRoute('hundred_one_hotels_tariff');
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="hundred_one_hotels_room")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        /** @var HundredOneHotelsConfig $config */
        $config = $this->hotel->getHundredOneHotelsConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RoomsType(), $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.hundred_one_hotels')->pullRooms($config),
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

            $this->addFlash('success',
                $this->get('translator')->trans('controller.hundredOneHotelsController.settings_saved_success'));

            return $this->redirectToRoute('hundred_one_hotels_room');
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }
}