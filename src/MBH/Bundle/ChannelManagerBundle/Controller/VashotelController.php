<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use MBH\Bundle\ChannelManagerBundle\Form\VashotelType;
use MBH\Bundle\ChannelManagerBundle\Services\Vashotel;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/vashotel")
 */
class VashotelController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="vashotel")
     * @Method("GET")
     * @Security("is_granted('ROLE_VASHOTEL')")
     * @Template()
     */
    public function indexAction()
    {
        $entity = $this->hotel->getVashotelConfig();

        $isReadyResult = $this->get('mbh.channelmanager')->checkForReadinessOrGetStepUrl($entity, 'vashotel');
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }

        $form = $this->createForm(
            VashotelType::class, $entity
        );

        return [
            'config' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Main configuration page save
     * @Route("/", name="vashotel_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_VASHOTEL')")
     * @Template("MBHChannelManagerBundle:Vashotel:index.html.twig")
     */
    public function saveAction(Request $request)
    {
        $entity = $this->hotel->getVashotelConfig();

        if (!$entity) {
            $entity = new VashotelConfig();
            $entity->setHotel($this->hotel);
        }

        $form = $this->createForm(
            VashotelType::class, $entity
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.vashotelController.settings_saved_success'))
            ;

            $this->get('mbh.channelmanager.vashotel')->syncServices($entity);
            $this->get('mbh.channelmanager')->updateInBackground();

            return $this->redirect($this->generateUrl('vashotel'));
        }

        return [
            'config' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/room", name="vashotel_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_VASHOTEL')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getVashotelConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.vashotel')->pullRooms($config),
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

            $this->addFlash('success', 'controller.vashotelController.settings_saved_success');

            $redirectRouteName = $config->isReadyToSync() ? 'vashotel_room' : 'vashotel_tariff';

            return $this->redirect($this->generateUrl($redirectRouteName));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }


    /**
     * @Route("/tariff", name="vashotel_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_VASHOTEL')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function tariffAction(Request $request)
    {
        $config = $this->hotel->getVashotelConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffsType::class, $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.vashotel')->pullTariffs($config),
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
                    $this->get('translator')->trans('controller.vashotelController.settings_saved_success'));

            return $this->redirect($this->generateUrl('vashotel_tariff'));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Services configuration page
     * @Route("/service", name="vashotel_service")
     * @Method("GET")
     * @Security("is_granted('ROLE_VASHOTEL')")
     * @Template()
     */
    public function serviceAction()
    {
        $config = $this->hotel->getVashotelConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        return [
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }

}
