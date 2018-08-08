<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Form\ChannelManagerConfigType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExpediaController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 * @Route("/expedia")
 */
class ExpediaController extends Controller
{
    /**
     * Main configuration page
     * @Route("/", name="expedia")
     * @Method("GET")
     * @Security("is_granted('ROLE_EXPEDIA')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getExpediaConfig();

        $isReadyResult = $this->get('mbh.channelmanager')->checkForReadinessOrGetStepUrl($config, 'expedia');
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }

        $form = $this->createForm(ChannelManagerConfigType::class, $config, [
            'data_class' => ExpediaConfig::class,
            'channelManagerName' => 'Expedia Partner Central'
        ]);

        return [
            'form' => $form->createView(),
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Route("/", name="expedia_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_EXPEDIA')")
     * @Template("MBHChannelManagerBundle:Expedia:index.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Throwable
     */
    public function saveAction(Request $request)
    {
        $config = $this->hotel->getExpediaConfig();

        if (!$config) {
            $config = new ExpediaConfig();
            $config->setHotel($this->hotel);
        }

        $form = $this->createForm(ChannelManagerConfigType::class, $config, [
            'data_class' => ExpediaConfig::class,
            'channelManagerName' => 'Expedia Partner Central'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $errorMessage = $this->get('mbh.channelmanager.expedia')->safeConfigDataAndGetErrorMessage();
            if ($errorMessage === '' || !$config->getIsEnabled()) {
                $this->dm->persist($config);
                $this->dm->flush();

                $this->get('mbh.channelmanager')->updateInBackground();

                $this->addFlash('success', 'controller.expediaController.settings_saved_success');
                if (!$config->isReadyToSync()) {
                    $this->get('mbh.messages_store')
                        ->sendMessageToTechSupportAboutNewConnection('Expedia', $this->get('mbh.instant_notifier'));
                }
            } else {
                $this->addFlash('danger', $errorMessage);
            }
        }

        return $this->redirectToRoute('expedia');
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="expedia_tariff")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @Security("is_granted('ROLE_EXPEDIA')")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getExpediaConfig();
        $inGuide = !$config->isReadyToSync();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        try {
            $serviceTariffs = $this->get('mbh.channelmanager.expedia')->pullTariffs($config);
        } catch (\Exception $exception) {
            $this->get('mbh.channelmanager.logger')->err($exception->getMessage());
            $this->addFlash('error', 'controller.channel_manager.pull_rooms.error');
            $serviceTariffs = [];
        }

        $form = $this->createForm(TariffsType::class, $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $serviceTariffs,
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
            $this->addFlash('success', 'controller.expediaController.settings_saved_success');

            $redirectRoute = $inGuide
                ? $this->generateUrl('cm_data_warnings', ['channelManagerName' => 'expedia'])
                : $this->generateUrl('expedia_tariff');

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
     * @Route("/room", name="expedia_room")
     * @Method({"GET", "POST"})
     * @Template()
     * @Security("is_granted('ROLE_EXPEDIA')")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roomAction(Request $request)
    {
        /** @var ExpediaConfig $config */
        $config = $this->hotel->getExpediaConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        try {
            $roomTypeData = $this->get('mbh.channelmanager.expedia')->pullRooms($config);
        } catch (\Exception $exception) {
            $this->get('mbh.channelmanager.logger')->err($exception->getMessage());
            $this->addFlash('error', 'controller.channel_manager.pull_rooms.error');
            $roomTypeData = [];
        }

        $form = $this->createForm(RoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $roomTypeData,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
            $this->dm->flush();
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
            $this->addFlash('success', 'controller.expediaController.settings_saved_success');

            $redirectRouteName = $config->isReadyToSync() ? 'expedia_room' : 'expedia_tariff';

            return $this->redirect($this->generateUrl($redirectRouteName));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Sync old packages
     * @Route("/packages/sync", name="expedia_packages_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_EXPEDIA')")
     */
    public function syncOldOrders()
    {
        $config = $this->hotel->getExpediaConfig();
        if ($config) {
            $this->get('mbh.channelmanager')->pullOrdersInBackground('expedia', true);
            $this->addFlash('warning', 'controller.expediaController.old_ordes_sync_start');
        }

        return $this->redirect($this->generateUrl('expedia'));
    }

    /**
     * @Method("POST")
     * @Route("/push_notification", name="expedia_push_notification")
     * @param Request $request
     * @return Response
     */
    public function handlePushNotificationAction(Request $request)
    {
        $requestXml = $request->getContent();

        $responseXml = $this->get('mbh.channelmanager.expedia')->handleNotificationOrder($requestXml);

        return new Response($responseXml, 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}