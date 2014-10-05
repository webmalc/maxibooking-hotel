<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Form\RoomTypeType;
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
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteRoomAction($roomType, $id)
    {
        $response =  $this->deleteEntity($id, 'MBHHotelBundle:Room', 'room_type', ['tab' => $roomType]);
        $this->get('mbh.room.cache.generator')->generateForRoomTypeInBackground(
                $this->get('doctrine_mongodb')
                     ->getManager()
                     ->getRepository('MBHHotelBundle:RoomType')
                     ->find($roomType)
        );
        return $response;
    }

    /**
     * Show edit room form.
     *
     * @Route("/room/{id}/edit", name="room_type_room_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function editRoomAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:Room')->find($id);

        if (!$entity) {
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
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:RoomType:editRoom.html.twig")
     */
    public function updateRoomAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:Room')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new RoomForm(), $entity, ['isNew' => false, 'hotelId' => $entity->getHotel()->getId()]
        );

        $form->bind($request);

        if ($form->isValid()) {
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно отредактирована.')
            ;

            $this->get('mbh.room.cache.generator')->generateForRoomTypeInBackground($entity->getRoomType());
            
            if ($this->getRequest()->get('save') !== null) {
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
     * Show new room form.
     *
     * @Route("/{id}/room/new/", name="room_type_room_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newRoomAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);
        if (!$entity) {
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
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:RoomType:newRoom.html.twig")
     */
    public function createRoomAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);
        if (!$entity) {
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

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно создана.')
            ;

            $this->get('mbh.room.cache.generator')->generateForRoomTypeInBackground($entity);
            
            if ($this->getRequest()->get('save') !== null) {
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
     * @Security("is_granted('ROLE_ADMIN')")
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

        $form = $this->createForm(new RoomTypeGenerateRoomsType());

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
     * @Security("is_granted('ROLE_ADMIN')")
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
        $form->bind($request);

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
            
            $this->get('mbh.room.cache.generator')->generateForRoomTypeInBackground($entity);
            
            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Номера успешно сгенерированы.')
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
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new RoomType();
        $form = $this->createForm(
                new RoomTypeType(), $entity, ['calculationTypes' => $this->container->getParameter('mbh.calculation.types')]
        );

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="room_type_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:RoomType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new RoomType();
        $entity->setHotel($this->get('mbh.hotel.selector')->getSelected());

        $form = $this->createForm(
                new RoomTypeType(), $entity, ['calculationTypes' => $this->container->getParameter('mbh.calculation.types')]
        );
        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $entity->uploadImage($form['imageFile']->getData());
            $dm->persist($entity);
            $dm->flush();

            $this->get('mbh.room.cache.generator')->generateForRoomTypeInBackground($entity);
            
            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно создана.')
            ;

            return $this->afterSaveRedirect('room_type', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Delete image
     *
     * @Route("/{id}/image/delete", name="room_type_image_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function imageDelete($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $entity->imageDelete();

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($entity);
        $dm->flush();

        $this->getRequest()->getSession()->getFlashBag()
            ->set('success', 'Изображение успешно удалено.')
        ;

        return $this->redirect($this->generateUrl('room_type_edit', ['id' => $id]));

    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="room_type_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:RoomType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new RoomTypeType(), $entity, [
                'calculationTypes' => $this->container->getParameter('mbh.calculation.types'),
                'imageUrl' => $entity->getImage(true),
                'deleteImageUrl' => $this->generateUrl('room_type_image_delete', ['id' => $id])
            ]
        );
        $form->bind($request);

        if ($form->isValid()) {

            $entity->uploadImage($form['imageFile']->getData());

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->get('mbh.room.cache.generator')->generateForRoomTypeInBackground($entity);
            
            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно отредактирована.')
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
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="room_type_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new RoomTypeType(), $entity, [
                    'calculationTypes' => $this->container->getParameter('mbh.calculation.types'),
                    'imageUrl' => $entity->getImage(true),
                    'deleteImageUrl' => $this->generateUrl('room_type_image_delete', ['id' => $id])
                ]
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="room_type_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $response =  $this->deleteEntity($id, 'MBHHotelBundle:RoomType', 'room_type');
        $this->get('mbh.room.cache.generator')->generateInBackground();
        
        return $response;
    }

}
