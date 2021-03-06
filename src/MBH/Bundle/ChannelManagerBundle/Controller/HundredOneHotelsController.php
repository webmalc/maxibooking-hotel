<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\ChannelManagerBundle\Form\HundredOneHotelType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Services\HundredOneHotels;
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
 * @Route("/hundred_one_hotels")
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

        $isReadyResult = $this->get('mbh.cm_wizard_manager')->checkForReadinessOrGetStepUrl($config, 'hundred_one_hotels');
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }

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
     * @throws \Throwable
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
            if (!$config->isMainSettingsFilled()) {
                $config->setIsEnabled(true);
            }

            $errorMessage = $this->get('mbh.channelmanager.hundred_one_hotels')->sendTestRequestAndGetErrorMessage($config);
            if ($config->getIsEnabled() && isset($errorMessage)) {
                $this->addFlash('danger', $errorMessage);
            } else {
                /* @var $dm DocumentManager; */
                $dm = $this->get('doctrine_mongodb')->getManager();
                $channelManagerService = $this->get('mbh.channelmanager');
                $dm->persist($config);

                if (!$config->isReadyToSync()) {
                    $config->setIsMainSettingsFilled(true);
                    $channelManagerHumanName = $channelManagerService->getServiceHumanName('hundred_one_hotels');
                    $this->get('mbh.messages_store')
                        ->sendCMConfirmationMessage($config, $channelManagerHumanName, $this->get('mbh.notifier.mailer'));
                }
                $dm->flush();

                $this->addFlash('success', 'controller.bookingController.settings_saved_success');
                $channelManagerService->updateInBackground();
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
        $prevTariffs = $config->getTariffs()->toArray();
        $inGuide = !$config->isReadyToSync();

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

            $userName = $this->getUser()->getUsername();
            $this->get('mbh.channelmanager')->logCollectionChanges($config, 'tariffs', $userName, $prevTariffs);
            if (!$config->isReadyToSync()) {
                $config->setIsTariffsConfigured(true);
            }

            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.bookingController.settings_saved_success');

            $redirectRoute = $inGuide
                ? $this->generateUrl('cm_data_warnings', ['channelManagerName' => 'hundred_one_hotels'])
                : $this->generateUrl('hundred_one_hotels_tariff');

            return $this->redirect($redirectRoute);
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
     */
    public function roomAction(Request $request)
    {
        /** @var HundredOneHotelsConfig $config */
        $config = $this->hotel->getHundredOneHotelsConfig();
        $prevRooms = $config->getRooms()->toArray();

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

            $userName = $this->getUser()->getUsername();
            $this->get('mbh.channelmanager')->logCollectionChanges($config, 'rooms', $userName, $prevRooms);
            if (!$config->isReadyToSync()) {
                $config->setIsRoomsConfigured(true);
            }

            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.hundredOneHotelsController.settings_saved_success');

            $redirectRouteName = $config->isReadyToSync() ? 'hundred_one_hotels_room' : 'hundred_one_hotels_tariff';

            return $this->redirectToRoute($redirectRouteName);
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Sync old packages
     * @Route("/packages/sync", name="hoh_packages_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_101HOTELS')")
     */
    public function syncOldOrders()
    {
        $config = $this->hotel->getHundredOneHotelsConfig();
        if ($config) {
            $this->get('mbh.channelmanager')->pullOrdersInBackground('hundred_one_hotels', true);
            $this->addFlash('warning', 'controller.expediaController.old_ordes_sync_start');
        }

        return $this->redirect($this->generateUrl('hundred_one_hotels'));
    }
}