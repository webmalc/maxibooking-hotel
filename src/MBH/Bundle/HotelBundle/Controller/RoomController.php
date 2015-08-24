<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Form\RoomTypeGenerateRoomsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Form\RoomType as RoomForm;


/**
 * Class RoomController
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 * @Route("/room")
 */
class RoomController extends BaseController
{
    /**
     * Show edit room form.
     *
     * @Route("/{id}/edit", name="room_type_room_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     * @ParamConverter(class="MBHHotelBundle:Room")
     */
    public function editAction(Room $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RoomForm(), $entity, [
            'isNew' => false,
            'hotelId' => $entity->getHotel()->getId()
        ]);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Delete room.
     *
     * @Route("/{roomType}/{id}/delete", name="room_type_room_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     */
    public function deleteAction($roomType, $id)
    {
        return $this->deleteEntity($id, 'MBHHotelBundle:Room', 'room_type', ['tab' => $roomType]);
    }

    /**
     * Update room.
     *
     * @Route("/{id}/edit", name="room_type_room_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:RoomType:editRoom.html.twig")
     * @ParamConverter(class="MBHHotelBundle:Room")
     */
    public function updateAction(Request $request, Room $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RoomForm(), $entity, [
            'isNew' => false,
            'hotelId' => $entity->getHotel()->getId()
        ]);

        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success',
                $this->get('translator')->trans('controller.roomTypeController.record_edited_success'));

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('room_type_room_edit', ['id' => $entity->getId()]));
            }

            return $this->redirect($this->generateUrl('room_type', ['tab' => $entity->getRoomType()->getId()]));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Show new room form.
     *
     * @Route("/{id}/new/", name="room_type_room_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     */
    public function newAction(RoomType $roomType)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($roomType->getHotel())) {
            throw $this->createNotFoundException();
        }

        $room = new Room();
        $room->setRoomType($roomType);
        $form = $this->createForm(new RoomForm(), $room , [
            'hotelId' => $this->hotel->getId()
        ]);

        return [
            'entity' => $roomType,
            'form' => $form->createView(),
            'logs' => $this->logs($roomType)
        ];
    }


    /**
     * Create room.
     *
     * @Route("/{id}/room/new/", name="room_type_room_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:Room:new.html.twig")
     */
    public function createRoomAction(Request $request, $id)
    {
        $entity = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($id);
        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $room = new Room();
        $room->setRoomType($entity)->setHotel($this->hotel);

        $form = $this->createForm(new RoomForm(), $room, [
            'hotelId' => $this->hotel->getId()
        ]);
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($room);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('room_type_room_edit', ['id' => $room->getId()]));
            }

            return $this->redirect($this->generateUrl('room_type', ['tab' => $id]));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * rooms json list.
     *
     * @Route("/{id}/room/", name="room_type_room_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function jsonListAction(Request $request, $id)
    {
        $qb = $this->dm->getRepository('MBHHotelBundle:Room')
            ->createQueryBuilder('r')
            ->field('roomType.id')->equals($id)
            ->skip($request->get('start'))
            ->limit($request->get('length'));

        $search = $request->get('search')['value'];
        if (!empty($search)) {
            $qb->addOr($qb->expr()->field('fullTitle')->equals(new \MongoRegex('/.*' . $search . '.*/ui')));
            $qb->addOr($qb->expr()->field('title')->equals(new \MongoRegex('/.*' . $search . '.*/ui')));
        }

        $entities = $qb->getQuery()->execute();

        return [
            'entities' => $entities,
            'total' => $entities->count(),
            'draw' => $request->get('draw')
        ];
    }

    /**
     * Show generate rooms form.
     *
     * @Route("/{id}/generate/", name="room_type_generate_rooms")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     */
    public function generateAction(RoomType $entity)
    {
        $form = $this->createForm(new RoomTypeGenerateRoomsType(), [], [
            'entity' => $entity,
            'hotel' => $this->hotel
        ]);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Generate rooms process.
     *
     * @Route("/{id}/generate/", name="room_type_generate_rooms_process")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:Room:generate.html.twig")
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     */
    public function generateProcessAction(Request $request, RoomType $entity)
    {
        $form = $this->createForm(new RoomTypeGenerateRoomsType(), null, [
            'hotel' => $this->hotel
        ]);
        $form->submit($request);

        if ($form->isValid()) {
            $data = $form->getData();

            for ($i = (int)round($data['from']); $i <= (int)round($data['to']); $i++) {
                $room = new Room();
                $room->setFullTitle($data['prefix'] . $i)
                    ->setRoomType($entity)
                    ->setHousing(!empty($data['housing']) ? $data['housing'] : null)
                    ->setFloor(!empty($data['floor']) ? $data['floor'] : null)
                    ->setHotel($this->hotel);

                if (!count($this->get('validator')->validate(($room)))) {
                    $this->dm->persist($room);
                }
            }

            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success',
                $this->get('translator')->trans('controller.roomTypeController.rooms_generation_success'));

            return $this->afterSaveRedirect('room_type', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }
}