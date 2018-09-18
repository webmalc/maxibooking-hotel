<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Form\AirbnbRoomsType;
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
     */
    public function indexAction(Request $request)
    {
        $config = $this->hotel->getAirbnbConfig();

        $isReadyResult = $this->get('mbh.cm_wizard_manager')->checkForReadinessOrGetStepUrl($config, 'airbnb');
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
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $this->addFlash('success','controller.bookingController.settings_saved_success');
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
        $form = $this->createForm(AirbnbRoomsType::class, null, [
            'config' => $config
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $syncUrlsByRoomTypeIds = $form->getData();
            foreach ($syncUrlsByRoomTypeIds as $roomTypeId => $syncUrl) {
                $roomType = $this->dm->find(RoomType::class, $roomTypeId);
                $syncRoom = $config->getSyncRoomByRoomType($roomType);
                if (!empty($syncUrl)) {
                    if (is_null($syncRoom)) {
                        $syncRoom = (new AirbnbRoom())
                            ->setRoomType($roomType);
                        $config->addRoom($syncRoom);
                    }

                    $syncRoom->setSyncUrl($syncUrl);
                } elseif (!is_null($syncRoom)) {
                    $config->removeRoom($syncRoom);
                }
            }

            $this->dm->flush();
            $this->addFlash('success', 'controller.bookingController.settings_saved_success');

            $redirectRoute = !$config->isConfirmedWithDataWarnings()
                ? $this->generateUrl('cm_data_warnings', ['channelManagerName' => Airbnb::NAME])
                : $this->generateUrl('airbnb_room');

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
     */
    public function syncAllPackages()
    {
        $config = $this->hotel->getBookingConfig();
        if ($config) {
            $this->get('mbh.channelmanager')->pullOrdersInBackground(Airbnb::NAME, true);
            $this->addFlash('warning', 'controller.bookingController.packages_sync_start');
        }

        return $this->redirect($this->generateUrl(Airbnb::NAME));
    }
}