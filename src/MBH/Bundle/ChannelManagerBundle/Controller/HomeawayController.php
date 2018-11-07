<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ChannelManagerBundle\Document\ICalServiceRoom;
use MBH\Bundle\ChannelManagerBundle\Document\HomeawayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\ICalServiceRoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\ICalServiceTariffType;
use MBH\Bundle\ChannelManagerBundle\Form\AirbnbType;
use MBH\Bundle\ChannelManagerBundle\Services\ICalService\Homeaway;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/homeaway")
 * Class HomeawayController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 */
class HomeawayController extends BaseController
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
        $config = $this->hotel->getHomeawayConfig();

        $isReadyResult = $this->get('mbh.cm_wizard_manager')->checkForReadinessOrGetStepUrl($config, Homeaway::NAME);
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }

        if (!$config) {
            $config = new HomeawayConfig();
            $config->setHotel($this->hotel);
        }

        $form = $this->createForm(AirbnbType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($config);

            if (!$config->isReadyToSync()) {
                $this->get('mbh.messages_store')
                    ->sendMessageToTechSupportAboutNewConnection(Homeaway::HUMAN_NAME, $this->get('mbh.instant_notifier'));
            }
            $config->setIsMainSettingsFilled(true);
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.bookingController.settings_saved_success');

            return $this->redirectToRoute(Homeaway::NAME);
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
        $config = $this->hotel->getHomeawayConfig();
        $prevRooms = $config->getRooms()->toArray();
        $form = $this->createForm(ICalServiceRoomsType::class, null, [
            'config' => $config,
            //TODO: Заменить
            'exampleRoomUrl' => 'https://www.airbnb.com/calendar/ical/12356789.ics?s=23987d97234e089734598f45',
            'syncUrlBegin' => Homeaway::SYNC_URL_BEGIN,
            'channelManager' => Homeaway::HUMAN_NAME
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
            $syncUrlsByRoomTypeIds = $form->getData();
            foreach ($syncUrlsByRoomTypeIds as $roomTypeId => $syncUrl) {
                $roomType = $this->dm->find(RoomType::class, $roomTypeId);
                if (!empty($syncUrl)) {
                    $syncRoom = (new ICalServiceRoom())
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
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roomLinksAction(Request $request)
    {
        $config = $this->hotel->getHomeawayConfig();
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
     * @Route("/tariff", name="homeaway_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getHomeawayConfig();
        $prevTariffs = $config->getTariffs()->toArray();
        $inGuide = !$config->isReadyToSync();

        $form = $this->createForm(ICalServiceTariffType::class, null, [
            'hotel' => $this->hotel,
            'channelManager' => Homeaway::HUMAN_NAME
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
                ? $this->generateUrl('cm_data_warnings', ['channelManagerName' => Homeaway::NAME])
                : $this->generateUrl('homeaway_tariff');

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
        $config = $this->hotel->getHomeawayConfig();
        if ($config) {
            $this->get('mbh.channelmanager')->pullOrdersInBackground(Homeaway::NAME, true);
            $this->addFlash('warning', 'controller.bookingController.packages_sync_start');
        }

        return $this->redirect($returnUrl ?? $this->generateUrl(Homeaway::NAME));
    }
}