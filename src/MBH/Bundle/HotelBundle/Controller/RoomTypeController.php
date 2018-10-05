<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\HotelBundle\Document\TaskSettings;
use MBH\Bundle\HotelBundle\Form\RoomTypeImageType;
use MBH\Bundle\HotelBundle\Form\RoomTypeTasksType;
use MBH\Bundle\HotelBundle\Form\RoomTypeType;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/roomtype")
 */
class RoomTypeController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Lists all entities.
     *
     * @Route("/", name="room_type")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_TYPE_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHHotelBundle:RoomType')->createQueryBuilder('s')
            ->field('hotel.id')->equals($this->hotel->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        if (!$entities->count()) {
            return $this->redirectToRoute('room_type_new');
        }

        return [
            'entities' => $entities
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="room_type_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_TYPE_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new RoomType();
        $entity->setIsHostel($this->hotel->getIsHostel());
        $form = $this->createForm(RoomTypeType::class, $entity, [
            'facilities' => $this->getParameter('mbh.hotel')['facilities'],
            'useRoomTypeCategory' => $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()->getUseRoomTypeCategory(),
            'hotel' => $this->hotel
        ]);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="room_type_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ROOM_TYPE_NEW')")
     * @Template("MBHHotelBundle:RoomType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new RoomType();
        $entity->setHotel($this->hotel);
        $form = $this->createForm(RoomTypeType::class, $entity, [
            'facilities' => $this->getParameter('mbh.hotel')['facilities'],
            'useRoomTypeCategory' => $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()->getUseRoomTypeCategory(),
            'hotel' => $entity->getHotel()
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            try {
                $this->invalidateCache($entity);
            } catch (InvalidateException $e) {
                $request->getSession()->getFlashBag()
                    ->set('error', 'Ошибка при инвалидации кэша.');
            }

            $this->addFlash('success', 'controller.roomTypeController.success_room_type_creation');

            return $this->afterSaveRedirect('room_type', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Delete image
     *
     * @Route("/{id}/image/{imageId}/delete", name="room_type_image_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_TYPE_DELETE')")
     */
    public function imageDelete($id, $imageId)
    {
        /* @var $entity RoomType */
        $entity = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $entity->deleteImageById($imageId);
        $this->dm->persist($entity);
        $this->dm->flush();

        $this->addFlash('success', 'controller.TaskTypeController.success_delete_photo');

        return $this->redirectToRoute('room_type_image_edit', ['id' => $id, 'imageTab' => 'active']);

    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="room_type_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_TYPE_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     */
    public function editAction(RoomType $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(RoomTypeType::class, $entity, [
            'useRoomTypeCategory' => $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()->getUseRoomTypeCategory(),
            'hotel' => $entity->getHotel()
        ]);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'images' => $entity->getImages()
        ];
    }


    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="room_type_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_ROOM_TYPE_EDIT')")
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     * @Template("MBHHotelBundle:RoomType:edit.html.twig")
     */
    public function updateAction(Request $request, RoomType $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        /** @var ClientConfig $config */
        $config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $form = $this->createForm(RoomTypeType::class, $entity, [
            'useRoomTypeCategory' => $config ? $config->getUseRoomTypeCategory() : false,
            'hotel' => $entity->getHotel()
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success',
                $this->get('translator')->trans('controller.roomTypeController.record_edited_success'));

            try {
                $this->invalidateCache($entity);
            } catch (InvalidateException $e) {
                $request->getSession()->getFlashBag()
                    ->set('error', 'Ошибка при инвалидации кэша.');
            }

            return $this->afterSaveRedirect('room_type', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'images' => $entity->getImages()
        ];
    }


    /**
     * @param Request $request
     * @param RoomType $roomType
     * @Method({"GET", "POST"})
     * @Route("/{id}/edit/tasks", name="room_type_task_edit")
     * @Security("is_granted('ROLE_ROOM_TYPE_EDIT')")
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     * @Template()
     * @return array
     */
    public function editAutoTasksAction(Request $request, RoomType $roomType)
    {
        if(!$roomType->getTaskSettings()) {
            $roomType->setTaskSettings(new TaskSettings());
        }
        $form = $this->createForm(RoomTypeTasksType::class, $roomType->getTaskSettings(), ['hotel' => $roomType->getHotel()]);

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if($form->isValid()) {
                //$this->dm->persist($entity);
                $this->dm->persist($roomType->getTaskSettings());
                $this->dm->flush();

                $this->ad('success',
                    $this->get('translator')->trans('controller.roomTypeController.record_edited_success'));

                return $this->afterSaveRedirect('room_type', $roomType->getId(), [], '_task_edit');
            }
        }

        return [
            'form' => $form->createView(),
            'roomType' => $roomType,
            'logs' => $this->logs($roomType),
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="room_type_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_TYPE_DELETE')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHHotelBundle:RoomType', 'room_type');
    }

    /**
     * Make image main.
     *
     * @Route("/image/{imageId}/main/{id}/edit", name="room_type_image_make_main")
     * @Security("is_granted('ROLE_ROOM_TYPE_EDIT')")
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     * @Template("MBHHotelBundle:RoomType:editRoom.html.twig")
     */
    public function makeMainImageRoomTypeAction(Request $request, RoomType $entity, $imageId)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        /* @var $entity RoomType */
        $entity->makeMainImageById($imageId);
        $this->dm->persist($entity);
        $this->dm->flush();

        $this->addFlash('success', 'controller.roomTypeController.success_set_main_photo');

        return $this->redirectToRoute('room_type_image_edit', ['id' => $entity->getId()]);
    }



    /**
     * Update room.
     *
     * @Route("/images/{id}/edit", name="room_type_image_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_ROOM_TYPE_EDIT')")
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     * @Template()
     */
    public function editImagesAction(Request $request, RoomType $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(RoomTypeImageType::class);
        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST) && $form->isValid()) {
            $image = new RoomTypeImage();
            $image->uploadImage($form['imageFile']->getData());
            $entity->addImage($image);
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.roomTypeController.success_set_photo');

            return $this->redirectToRoute('room_type_image_edit', [
                'id' => $entity->getId()
            ]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'images' => $entity->getImages(),
        );
    }

    /**
     * @param RoomType $roomType
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     */
    private function invalidateCache(RoomType $roomType)
    {
        $this->get('mbh_search.invalidate_queue_creator')->addToQueue($roomType);
    }
}
