<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Form\ExpediaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;

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

        $form = $this->createForm(ExpediaType::class, $config);

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
     */
    public function saveAction(Request $request)
    {
        $config = $this->hotel->getExpediaConfig();

        if (!$config) {
            $config = new ExpediaConfig();
            $config->setHotel($this->hotel);
        }

        $form = $this->createForm(ExpediaType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $errorMessage = $this->get('mbh.channelmanager.expedia')->safeConfigDataAndGetErrorMessage($config);
            if ($errorMessage === '' || !$config->getIsEnabled()) {
                $this->dm->persist($config);
                $this->dm->flush();

                $this->get('mbh.channelmanager')->updateInBackground();

                $this->addFlash('success', 'controller.expediaController.settings_saved_success');
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

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $serviceTariffs = $this->get('mbh.channelmanager.expedia')->pullTariffs($config);
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

            return $this->redirectToRoute('expedia_tariff');
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

        $roomTypeData = $this->get('mbh.channelmanager.expedia')->pullRooms($config);
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

            return $this->redirectToRoute('expedia_room');
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
        $this->get('mbh.channelmanager.expedia')->pullAllOrders();
        $this->addFlash(
            'warning',
            $this->get('translator')->trans('controller.expediaController.old_ordes_sync_start')
        );

        return $this->redirect($this->generateUrl('expedia'));
    }
}