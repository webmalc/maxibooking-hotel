<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\HomeAwayRoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\HomeAwayTariffType;
use MBH\Bundle\ChannelManagerBundle\Form\HomeAwayType;
use MBH\Bundle\ChannelManagerBundle\Services\HomeAway\HomeAway;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/homeaway")
 * Class HomeAwayController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 */
class HomeAwayController extends BaseController
{
    /**
     * @Route("/", name="homeaway")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Throwable
     */
    public function indexAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();

        $isReadyResult = $this->get('mbh.cm_wizard_manager')->checkForReadinessOrGetStepUrl($config, HomeAway::NAME);
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }

        $form = $this->createForm(HomeAwayType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($config);

            if (!$config->isReadyToSync()) {
                $this->get('mbh.messages_store')
                    ->sendMessageToTechSupportAboutNewConnection('HomeAway', $this->get('mbh.instant_notifier'));
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.bookingController.settings_saved_success');

            return $this->redirectToRoute(HomeAway::NAME);
        }

        return [
            'config' => $config,
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
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();
        $prevRooms = $config->getRooms()->toArray();
        $form = $this->createForm(HomeAwayRoomsType::class, null, [
            'config' => $config
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
            $syncUrlsByRoomTypeIds = $form->getData();
            foreach ($syncUrlsByRoomTypeIds as $roomTypeId => $syncUrl) {
                $roomType = $this->dm->find(RoomType::class, $roomTypeId);
                if (!empty($syncUrl)) {
                    $syncRoom = (new HomeAwayRoom())
                        ->setSyncUrl($syncUrl)
                        ->setRoomType($roomType);
                    $config->addRoom($syncRoom);
                }
            }

            $userName = $this->getUser()->getUsername();
            $this->get('mbh.channelmanager')->logCollectionChanges($config, 'rooms', $userName, $prevRooms);
            if (!$config->isReadyToSync()) {
                $config->setIsRoomsConfigured(true);
            }

            $this->dm->flush();

            $this->addFlash('success', 'controller.bookingController.settings_saved_success');
            $this->get('mbh.channelmanager')->updateInBackground();

            $redirectRouteName = $config->isReadyToSync() ? 'homeaway_room' : 'homeaway_room_links';

            return $this->redirect($this->generateUrl($redirectRouteName));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Template()
     * @Route("/room_links", name="homeaway_room_links")
     * @param Request $request
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roomLinksAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();
        if ($request->isMethod('POST')) {
            $config->setIsRoomLinksPageViewed(true);
            $this->dm->flush();

            if (!$config->isReadyToSync()) {
                return $this->redirectToRoute('homeaway_tariff');
            }
        }

        return [
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="homeaway_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();
        $prevTariffs = $config->getTariffs()->toArray();
        $inGuide = !$config->isReadyToSync();

        $form = $this->createForm(HomeAwayTariffType::class, null, [
            'hotel' => $this->hotel
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllTariffs();

            $tariff = $form->getData()['tariff'];
            $tariffForSync = (new Tariff())
                ->setTariff($tariff);
            $config->addTariff($tariffForSync);

            $userName = $this->getUser()->getUsername();
            $this->get('mbh.channelmanager')->logCollectionChanges($config, 'tariffs', $userName, $prevTariffs);
            if (!$config->isReadyToSync()) {
                $config->setIsTariffsConfigured(true);
            }

            $this->dm->flush();

            $this->addFlash('success', 'controller.bookingController.settings_saved_success');
            $this->get('mbh.channelmanager')->updateInBackground();

            $redirectRoute = $inGuide
                ? $this->generateUrl('cm_data_warnings', ['channelManagerName' => HomeAway::NAME])
                : $this->generateUrl('airbnb_tariff');

            return $this->redirect($redirectRoute);
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Sync all old packages
     * @Route("/packages/sync_all", name="homeaway_all_packages_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function syncAllPackages(Request $request)
    {
        $returnUrl = $request->query->get('returnUrl');
        $config = $this->hotel->getBookingConfig();
        if ($config) {
            $this->get('mbh.channelmanager')->pullOrdersInBackground(HomeAway::NAME, true);
            $this->addFlash('warning', 'controller.bookingController.packages_sync_start');
        }

        return $this->redirect($returnUrl ?? $this->generateUrl(HomeAway::NAME));
    }

}
