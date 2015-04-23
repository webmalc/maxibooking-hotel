<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Form\RoomTypeType;
use MBH\Bundle\HotelBundle\Form\RoomTypeImageType;
use MBH\Bundle\HotelBundle\Form\RoomTypeGenerateRoomsType;
use MBH\Bundle\HotelBundle\Form\RoomType as RoomForm;

/**
 * @Route("/roomtype")
 */
class RoomTypeController extends Controller implements CheckHotelControllerInterface
{

    /**
     * rooms json list.
     *
     * @Route("/{id}/room/", name="room_type_room_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function jsonRoomsListAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $qb = $dm->getRepository('MBHHotelBundle:Room')
                 ->createQueryBuilder('r')
                 ->field('roomType.id')->equals($id)
                 ->skip($request->get('start'))
                 ->limit($request->get('length'))
        ;

        $search = $request->get('search')['value'];
        if (!empty($search)) {
            $qb->addOr($qb->expr()->field('fullTitle')->equals(new \MongoRegex('/.*'. $search .'.*/ui')));
            $qb->addOr($qb->expr()->field('title')->equals(new \MongoRegex('/.*'. $search .'.*/ui')));
        }

        $entities = $qb->getQuery()->execute();

        return [
            'entities' => $entities,
            'total' => $entities->count(),
            'draw' => $request->get('draw')
        ];
    }

    /**
     * Delete room.
     *
     * @Route("/{roomType}/room/{id}/delete", name="room_type_room_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     */
    public function deleteRoomAction($roomType, $id)
    {
        return $this->deleteEntity($id, 'MBHHotelBundle:Room', 'room_type', ['tab' => $roomType]);
    }

    /**
     * Show edit room form.
     *
     * @Route("/room/{id}/edit", name="room_type_room_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function editRoomAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:Room')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new RoomForm(), $entity, ['isNew' => false, 'hotelId' => $entity->getHotel()->getId()]
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Update room.
     *
     * @Route("/room/{id}/edit", name="room_type_room_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:RoomType:editRoom.html.twig")
     */
    public function updateRoomAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:Room')->find($id);

        if (!$entity  || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new RoomForm(), $entity, ['isNew' => false, 'hotelId' => $entity->getHotel()->getId()]
        );

        $form->bind($request);

        if ($form->isValid()) {
            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.roomTypeController.record_edited_success'))
            ;
            
            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('room_type_room_edit', ['id' => $id]));
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
     * Update room.
     *
     * @Route("/room/image/{id}/edit", name="room_type_image_room_update")

     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:RoomType:edit.html.twig")
     */
    public function updateRoomImageAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new RoomTypeType(), $entity
        );
        $formImage = $this->createForm(
            new RoomTypeImageType()
        );
        $formImage->handleRequest($request);

        if ($request->getMethod() === 'POST') {

            if ($formImage->isValid()) {
                $image = new RoomTypeImage();
                $image->uploadImage($formImage['imageFile']->getData());
                $entity->addImage($image);
                $dm->persist($entity);
                $dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success', 'Фотография успешно создана.')
                ;
                return $this->redirect($this->generateUrl('room_type_image_room_update', ['id' => $id, 'imageTab' => 'active' ]));
            }
        }

        return array(

            'entity' => $entity,
            'form' => $form->createView(),
            'formImage' => $formImage->createView(),
            'logs' => $this->logs($entity),
            'images' => $entity->getImages(),
        );
    }

    /**
     * Show new room form.
     *
     * @Route("/{id}/room/new/", name="room_type_room_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function newRoomAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);
        if (!$entity  || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RoomForm(), new Room());

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Create room.
     *
     * @Route("/{id}/room/new/", name="room_type_room_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:RoomType:newRoom.html.twig")
     */
    public function createRoomAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);
        if (!$entity  || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $room = new Room();
        $room->setRoomType($entity)
                ->setHotel($this->get('mbh.hotel.selector')->getSelected())
        ;
        $form = $this->createForm(new RoomForm(), $room);
        $form->bind($request);

        if ($form->isValid()) {
            $dm->persist($room);
            $dm->flush();

            $request->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно создана.')
            ;
            
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
     * Show generate rooms form.
     *
     * @Route("/{id}/generate/rooms/", name="room_type_generate_rooms")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function generateRoomsAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RoomTypeGenerateRoomsType(), [], ['entity' => $entity]);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Generate rooms process.
     *
     * @Route("/{id}/generate/rooms/", name="room_type_generate_rooms_process")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:RoomType:generateRooms.html.twig")
     */
    public function generateRoomsProcessAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RoomTypeGenerateRoomsType());
        $form->submit($request);

        if ($form->isValid()) {
            $data = $form->getData();

            for ($i = (int) round($data['from']); $i <= (int) round($data['to']); $i++) {
                $room = new Room();
                $room->setFullTitle($data['prefix'] . $i)
                        ->setRoomType($entity)
                        ->setHotel($this->get('mbh.hotel.selector')->getSelected())
                ;

                if (!count($this->get('validator')->validate(($room)))) {
                    $dm->persist($room);
                }
            }

            $dm->flush();

            $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.roomTypeController.rooms_generation_success'))
            ;

            return $this->afterSaveRedirect('room_type', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Lists all entities.
     *
     * @Route("/", name="room_type")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entities = $dm->getRepository('MBHHotelBundle:RoomType')->createQueryBuilder('s')
                ->field('hotel.id')->equals($this->get('mbh.hotel.selector')->getSelected()->getId())
                ->sort('fullTitle', 'asc')
                ->getQuery()
                ->execute()
        ;

        if (!$entities->count()) {
            return $this->redirect($this->generateUrl('room_type_new'));
        }

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="room_type_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new RoomType();
        $formImage = $this->createForm(
            new RoomTypeImageType()
        );
        ($this->get('mbh.hotel.selector')->getSelected()->getIsHostel()) ? $type = 'hostel' : $type = 'hotel';
        $form = $this->createForm(new RoomTypeType(), $entity, []);

        return array(
            'form' => $form->createView(),
            'formImage' => $formImage->createView()
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="room_type_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:RoomType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new RoomType();
        $entity->setHotel($this->get('mbh.hotel.selector')->getSelected());

        ($this->get('mbh.hotel.selector')->getSelected()->getIsHostel()) ? $type = 'hostel' : $type = 'hotel';
        $form = $this->createForm(new RoomTypeType(), $entity, []);

        $form->bind($request);
        $formImage = $this->createForm(
            new RoomTypeImageType()
        );
        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();


//            $entity->uploadImage($form['imageFile']->getData());
//            $dm->persist($entity);
//            $dm->flush();
            
            $request->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно создана.')
            ;

            return $this->afterSaveRedirect('room_type', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formImage' => $formImage->createView()
        );
    }

    /**
     * Delete image
     *
     * @Route("/{id}/image/{imageId}/delete", name="room_type_image_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     */
    public function imageDelete($id,$imageId)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        /* @var $entity RoomType */
        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity  || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $entity->deleteImageById($entity,$imageId);
        $dm->persist($entity);
        $dm->flush();

        $this->getRequest()->getSession()->getFlashBag()
            ->set('success', 'Изображение успешно удалено.')
        ;

        return $this->redirect($this->generateUrl('room_type_edit', ['id' => $id, 'imageTab' => 'active' ]));

    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="room_type_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:RoomType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        /* @var $entity RoomType */
        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity  || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        ($this->get('mbh.hotel.selector')->getSelected()->getIsHostel()) ? $type = 'hostel' : $type = 'hotel';
        $form = $this->createForm(
            new RoomTypeType(), $entity /*[
                'imageUrl' => $entity->getImage(true),
                'deleteImageUrl' => $this->generateUrl('room_type_image_delete', ['id' => $id])
            ]*/
        );
        /* @var $images RoomTypeImage */

        $form->submit($request);

        if ($form->isValid()) {

//            $entity->uploadImage($form['imageFile']->getData());

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();
            
            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.roomTypeController.record_edited_success'))
            ;

            return $this->afterSaveRedirect('room_type', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'images' => $entity->getImages()
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="room_type_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        ($this->get('mbh.hotel.selector')->getSelected()->getIsHostel()) ? $type = 'hostel' : $type = 'hotel';
        $form = $this->createForm(
            new RoomTypeType(), $entity
        );
        $formImage = $this->createForm(
            new RoomTypeImageType()
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formImage' => $formImage->createView(),
            'logs' => $this->logs($entity),
            'images' => $entity->getImages()
        );
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="room_type_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHHotelBundle:RoomType', 'room_type');
    }

    /**
     * Make image main.
     *
     * @Route("/room/image/{imageId}/main/{id}/edit", name="room_type_image_make_main")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHHotelBundle:RoomType:editRoom.html.twig")
     */
    public function makeMainImageRoomTypeAction(Request $request, $id, $imageId)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new RoomTypeType(), $entity
        );
        $formImage = $this->createForm(
            new RoomTypeImageType()
        );
        /* @var $entity RoomType */
        $entity->makeMainImageById($entity,$imageId);
        $dm->persist($entity);
        $dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', 'Фотография успешно была сделана главной.')
        ;
        return $this->redirect($this->generateUrl('room_type_edit', ['id' => $id, 'imageTab' => 'active' ]));

    }
}
