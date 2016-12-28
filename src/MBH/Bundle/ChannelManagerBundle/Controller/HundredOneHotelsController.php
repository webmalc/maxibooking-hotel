<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\ChannelManagerBundle\Form\HundredOneHotelType;
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
use Symfony\Component\HttpFoundation\Response;

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
     * @Security("is_granted('ROLE_101HOTELS')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getHundredOneHotelsConfig();

        $form = $this->createForm(HundredOneHotelType::class, $config);

        return [
            'form' => $form->createView(),
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }



    /**
     * @Route("/", name="hundred_one_hotels_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_101HOTELS')")
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
        $form = $this->createForm(HundredOneHotelType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $errorMessage = $this->get('mbh.channelmanager.hundred_one_hotels')->sendTestRequestAndGetErrorMessage($config);
            if (isset($errorMessage)) {
                $this->addFlash('danger',
                    $this->get('translator')->trans($errorMessage));
            } else {
                /* @var $dm DocumentManager; */
                $dm = $this->get('doctrine_mongodb')->getManager();
                $dm->persist($config);
                $dm->flush();

                $this->get('mbh.channelmanager')->updateInBackground();

                $this->addFlash('success',
                    $this->get('translator')->trans('controller.bookingController.settings_saved_success'));
            }
        }
        return $this->redirectToRoute('hundred_one_hotels');
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="hundred_one_hotels_tariff")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @Security("is_granted('ROLE_101HOTELS')")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getHundredOneHotelsConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffsType::class, $config->getTariffsAsArray(), [
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
     * @Security("is_granted('ROLE_101HOTELS')")
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

        $form = $this->createForm(RoomsType::class, $config->getRoomsAsArray(), [
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

    /**
     * @Route('/test')
     */
    public function test()
    {
        $this->get('mbh.channelmanager.hundred_one_hotels')->pullOrders();
    }
}