<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\ChannelManagerBundle\Form\OktogoType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffType;

/**
 * @Route("/oktogo")
 */
class OktogoController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Main configuration page
     * @Route("/", name="oktogo")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        $entity = $this->get('mbh.hotel.selector')->getSelected()->getOktogoConfig();

        $form = $this->createForm(
            new OktogoType(), $entity
        );

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Main configuration page save
     * @Route("/", name="oktogo_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHChannelManagerBundle:Oktogo:index.html.twig")
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();
        $new = false;

        if (!$entity) {
            $entity = new OktogoConfig();
            $entity->setHotel($hotel);
            $new = true;
        }

        $form = $this->createForm(
            new OktogoType(), $entity
        );

        $form->submit($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            if ($new) {
                //$this->get('mbh.channelmanager.oktogo')->roomSync($entity);
                //$this->get('mbh.channelmanager.oktogo')->tariffSync($entity);
                //$dm->persist($entity);
                //$dm->flush();
            }

            $request->getSession()->getFlashBag()
                ->set('success', 'Настройки успешно сохранены.')
            ;

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();

            return $this->redirect($this->generateUrl('oktogo'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/room", name="oktogo_room")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function roomAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

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
     * @Route("/room", name="oktogo_room_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHChannelManagerBundle:Oktogo:room.html.twig")
     */
    public function roomSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

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
                ->set('success', 'Настройки успешно сохранены.')
            ;
            if ($request->get('save') !== null) {

                return $this->redirect($this->generateUrl('oktogo_room'));
            }

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();

            return $this->redirect($this->generateUrl('oktogo'));
        }

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/room/sync", name="oktogo_room_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function roomSyncAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $result = $this->get('mbh.channelmanager.oktogo')->roomSync($entity);


        echo($result); exit();

        //return $this->redirect($this->generateUrl('oktogo_room'));
    }

    /**
     * @Route("/tariff/sync", name="oktogo_tariff_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function tariffSyncAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $result = $this->get('mbh.channelmanager.oktogo')->tariffSync($entity);

        if ($result) {
            $request->getSession()->getFlashBag()
                ->set('success', 'Тарифы успешно синхронизированы.')
            ;

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();
        } else {
            $request->getSession()->getFlashBag()
                ->set('danger', 'Во время синхронизации произошла ошибка. Попробуйте еще раз.')
            ;
        }

        return $this->redirect($this->generateUrl('oktogo_tariff'));
    }

    /**
     * @Route("/tariff", name="oktogo_tariff")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function tariffAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

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
     * @Route("/tariff", name="oktogo_tariff_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHChannelManagerBundle:Oktogo:tariff.html.twig")
     */
    public function tariffSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

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

                $oktogoTariff = new Tariff();
                $oktogoTariff->setTariff($tariff)->setTariffId($value);
                $entity->addTariff($oktogoTariff);
            }

            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Настройки успешно сохранены.')
            ;

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();

            if ($request->get('save') !== null) {

                return $this->redirect($this->generateUrl('oktogo_tariff'));
            }

            return $this->redirect($this->generateUrl('oktogo'));
        }

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }
}
