<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\TariffType;
use MBH\Bundle\ChannelManagerBundle\Form\VashotelType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;

/**
 * @Route("/vashotel")
 */
class VashotelController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="vashotel")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        $entity = $this->get('mbh.hotel.selector')->getSelected()->getVashotelConfig();

        $form = $this->createForm(
            new VashotelType(), $entity
        );

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Main configuration page save
     * @Route("/", name="vashotel_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHChannelManagerBundle:Vashotel:index.html.twig")
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getVashotelConfig();
        $new = false;

        if (!$entity) {
            $entity = new VashotelConfig();
            $entity->setHotel($hotel);
            $new = true;
        }

        $form = $this->createForm(
            new VashotelType(), $entity
        );

        $form->bind($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            if ($new) {
                $this->get('mbh.channelmanager.vashotel')->roomSync($entity);
                $this->get('mbh.channelmanager.vashotel')->tariffSync($entity);
                $dm->persist($entity);
                $dm->flush();
            }

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.vashhotelController.settings_saved_success'))
            ;

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();

            return $this->redirect($this->generateUrl('vashotel'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/room", name="vashotel_room")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function roomAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getVashotelConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new RoomType(), [], ['entity' => $entity]
        );

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/room", name="vashotel_room_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHChannelManagerBundle:Vashotel:room.html.twig")
     */
    public function roomSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getVashotelConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new RoomType(), [], ['entity' => $entity]
        );

        $form->submit($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();

            $entity->removeAllRooms();

            foreach ($form->getData() as $roomTypeId => $value) {
                if ($value === null) {
                    continue;
                }

                $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);

                if (!$roomType) {
                    continue;
                }
                $room = new Room();
                $room->setRoomType($roomType)->setRoomId($value);
                $entity->addRoom($room);
            }
            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.vashhotelController.settings_saved_success'))
            ;
            if ($request->get('save') !== null) {

                return $this->redirect($this->generateUrl('vashotel_room'));
            }

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();

            return $this->redirect($this->generateUrl('vashotel'));
        }

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/room/sync", name="vashotel_room_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function roomSyncAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getVashotelConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $result = $this->get('mbh.channelmanager.vashotel')->roomSync($entity);

        if ($result) {
            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.vashhotelController.settings_saved_success'))
            ;

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();
        } else {
            $request->getSession()->getFlashBag()
                ->set('danger', $this->get('translator')->trans('controller.vashhotelController.sync_error'))
            ;
        }

        return $this->redirect($this->generateUrl('vashotel_room'));
    }

    /**
     * @Route("/tariff/sync", name="vashotel_tariff_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function tariffSyncAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getVashotelConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $result = $this->get('mbh.channelmanager.vashotel')->tariffSync($entity);

        if ($result) {
            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.vashhotelController.rooms_sync_success'))
            ;

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();
        } else {
            $request->getSession()->getFlashBag()
                ->set('danger', $this->get('translator')->trans('controller.vashhotelController.sync_error'))
            ;
        }

        return $this->redirect($this->generateUrl('vashotel_tariff'));
    }

    /**
     * @Route("/tariff", name="vashotel_tariff")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function tariffAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getVashotelConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new TariffType(), [], ['entity' => $entity]
        );

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/tariff", name="vashotel_tariff_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHChannelManagerBundle:Vashotel:tariff.html.twig")
     */
    public function tariffSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getVashotelConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new TariffType(), [], ['entity' => $entity]
        );

        $form->submit($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();

            $entity->removeAllTariffs();

            foreach ($form->getData() as $tariffId => $value) {
                if ($value === null) {
                    continue;
                }

                $tariff = $dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);

                if (!$tariff) {
                    continue;
                }

                $vashotelTariff = new Tariff();
                $vashotelTariff->setTariff($tariff)->setTariffId($value);
                $entity->addTariff($vashotelTariff);
            }

            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.vashhotelController.settings_saved_success'))
            ;

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();

            if ($request->get('save') !== null) {

                return $this->redirect($this->generateUrl('vashotel_tariff'));
            }

            return $this->redirect($this->generateUrl('vashotel'));
        }

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }
}
