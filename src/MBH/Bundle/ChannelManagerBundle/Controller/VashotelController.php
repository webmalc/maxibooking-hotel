<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig;
use MBH\Bundle\ChannelManagerBundle\Document\VashotelRoom;
use MBH\Bundle\ChannelManagerBundle\Document\VashotelTariff;
use MBH\Bundle\ChannelManagerBundle\Form\VashotelTariffType;
use MBH\Bundle\ChannelManagerBundle\Form\VashotelType;
use MBH\Bundle\ChannelManagerBundle\Form\VashotelRoomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("/vashotel")
 */
class VashotelController extends Controller implements CheckHotelControllerInterface
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

        if (!$entity) {
            $entity = new VashotelConfig();
            $entity->setHotel($hotel);
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

            $request->getSession()->getFlashBag()
                ->set('success', 'Настройки успешно сохранены.')
            ;

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
            new VashotelRoomType(), [], ['entity' => $entity]
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
            new VashotelRoomType(), [], ['entity' => $entity]
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
                $room = new VashotelRoom();
                $room->setRoomType($roomType)->setRoomId($value);
                $entity->addRoom($room);
            }
            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Настройки успешно сохранены.')
            ;
            if ($request->get('save') !== null) {

                return $this->redirect($this->generateUrl('vashotel_room'));
            }

            return $this->redirect($this->generateUrl('vashotel'));
        }

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
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
            new VashotelTariffType(), [], ['entity' => $entity]
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
            new VashotelTariffType(), [], ['entity' => $entity]
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

                $vashotelTariff = new VashotelTariff();
                $vashotelTariff->setTariff($tariff)->setTariffId($value);
                $entity->addTariff($vashotelTariff);
            }

            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Настройки успешно сохранены.')
            ;
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
