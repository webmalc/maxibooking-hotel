<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\ChannelManagerConfigType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/ostrovok")
 */
class OstrovokController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="ostrovok")
     * @Method("GET")
     * @Security("is_granted('ROLE_OSTROVOK')")
     * @Template()
     */
    public function indexAction()
    {
        $entity = $this->hotel->getOstrovokConfig();

        $isReadyResult = $this->get('mbh.channelmanager')->checkForReadinessOrGetStepUrl($entity, 'ostrovok');
        if ($isReadyResult !== true) {
            return $this->redirect($isReadyResult);
        }

        $form = $this->createForm(
            ChannelManagerConfigType::class, $entity, [
                'data_class' => OstrovokConfig::class,
                'channelManagerName' => 'Ostrovok.ru'
            ]
        );

        return [
            'config' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Main configuration save
     * @Route("/", name="ostrovok_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_OSTROVOK')")
     * @Template("MBHChannelManagerBundle:Ostrovok:index.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->hotel;
        $entity = $hotel->getOstrovokConfig();

        if (!$entity) {
            $entity = new OstrovokConfig();
            $entity->setHotel($hotel);
        }
        $form = $this->createForm(
            ChannelManagerConfigType::class, $entity, [
                'data_class' => OstrovokConfig::class,
                'channelManagerName' => 'Ostrovok.ru'
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $this->get('mbh.channelmanager.ostrovok')->syncServices($entity);
            $this->get('mbh.channelmanager')->updateInBackground();

            $this->addFlash('success', 'controller.ostrovokController.settings_saved_success');

            return $this->redirect($this->generateUrl('ostrovok'));
        }

        return [
            'config' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="ostrovok_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_OSTROVOK')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        $entity = $this->hotel->getOstrovokConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomsType::class, $entity->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.ostrovok')->pullRooms($entity),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity->removeAllRooms();
            foreach ($form->getData() as $id => $roomType) {
                if ($roomType) {
                    $entityRoom = new Room();
                    $entityRoom->setRoomType($roomType)->setRoomId($id);
                    $entity->addRoom($entityRoom);
                    $this->dm->persist($entity);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();
            $this->addFlash('success', 'controller.ostrovokController.settings_saved_success');

            $redirectRouteName = $entity->isReadyToSync() ? 'ostrovok_room' : 'ostrovok_tariff';

            return $this->redirect($this->generateUrl($redirectRouteName));
        }

        return [
            'config' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Tariff configuration page
     * @Route("/tariff", name="ostrovok_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_OSTROVOK')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function tariffAction(Request $request)
    {
        $entity = $this->hotel->getOstrovokConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffsType::class, $entity->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.ostrovok')->pullTariffs($entity),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity->removeAllTariffs();
            foreach ($form->getData() as $id => $tariff) {
                if ($tariff) {
                    $entityTariff = new Tariff();
                    $entityTariff->setTariff($tariff)->setTariffId($id);
                    $entity->addTariff($entityTariff);
                    $this->dm->persist($entity);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $request->getSession()->getFlashBag()
                ->set('success',
                    $this->get('translator')->trans('controller.ostrovokController.settings_saved_success'));

            return $this->redirect($this->generateUrl('ostrovok_tariff'));
        }


        return [
            'config' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Services configuration page
     * @Route("/service", name="ostrovok_service")
     * @Method("GET")
     * @Security("is_granted('ROLE_OSTROVOK')")
     * @Template()
     */
    public function serviceAction()
    {
        $entity = $this->get('mbh.hotel.selector')->getSelected()->getOstrovokConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        return [
            'config' => $entity,
            'logs' => $this->logs($entity)
        ];
    }
}
