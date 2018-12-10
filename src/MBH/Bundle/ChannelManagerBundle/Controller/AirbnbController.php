<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\AirbnbRoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\AirbnbTariffType;
use MBH\Bundle\ChannelManagerBundle\Form\AirbnbType;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/airbnb")
 * Class AirbnbController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 */
class AirbnbController extends BaseController
{
    /**
     * @Route("/", name="airbnb")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_AIRBNB')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Throwable
     */
    public function indexAction(Request $request)
    {
        $config = $this->hotel->getAirbnbConfig();

        $isReadyResult = $this->get('mbh.cm_wizard_manager')->checkForReadinessOrGetStepUrl($config, Airbnb::NAME);
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }

        if (!$config) {
            $config = new AirbnbConfig();
            $config->setHotel($this->hotel);
        }

        $form = $this->createForm(AirbnbType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($config);

            if (!$config->isReadyToSync()) {
                $this->get('mbh.messages_store')
                    ->sendMessageToTechSupportAboutNewConnection('Airbnb', $this->get('mbh.instant_notifier'));
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.bookingController.settings_saved_success');

            return $this->redirectToRoute(Airbnb::NAME);
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="airbnb_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_AIRBNB')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getAirbnbConfig();
        $prevRooms = $config->getRooms()->toArray();
        $form = $this->createForm(AirbnbRoomsType::class, null, [
            'config' => $config
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
            $syncUrlsByRoomTypeIds = $form->getData();
            foreach ($syncUrlsByRoomTypeIds as $roomTypeId => $syncUrl) {
                $roomType = $this->dm->find(RoomType::class, $roomTypeId);
                if (!empty($syncUrl)) {
                    $syncRoom = (new AirbnbRoom())
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

            $redirectRouteName = $config->isReadyToSync() ? 'airbnb_room' : 'airbnb_room_links';

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
     * @Route("/room_links", name="airbnb_room_links")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roomLinksAction(Request $request)
    {
        $config = $this->hotel->getAirbnbConfig();
        if ($request->isMethod('POST')) {
            $config->setIsRoomLinksPageViewed(true);
            $this->dm->flush();

            if (!$config->isReadyToSync()) {
                return $this->redirectToRoute('airbnb_tariff');
            }
        }

        return [
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }
    
    /**
     * Tariff configuration page
     * @Route("/tariff", name="airbnb_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_AIRBNB')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getAirbnbConfig();
        $prevTariffs = $config->getTariffs()->toArray();
        $inGuide = !$config->isReadyToSync();

        $form = $this->createForm(AirbnbTariffType::class, null, [
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
                ? $this->generateUrl('cm_data_warnings', ['channelManagerName' => Airbnb::NAME])
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
     * @Route("/packages/sync_all", name="airbnb_all_packages_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_AIRBNB')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function syncAllPackages(Request $request)
    {
        $returnUrl = $request->query->get('returnUrl');
        $config = $this->hotel->getBookingConfig();
        if ($config) {
            $this->get('mbh.channelmanager')->pullOrdersInBackground(Airbnb::NAME, true);
            $this->addFlash('warning', 'controller.bookingController.packages_sync_start');
        }

        return $this->redirect($returnUrl ?? $this->generateUrl(Airbnb::NAME));
    }
}