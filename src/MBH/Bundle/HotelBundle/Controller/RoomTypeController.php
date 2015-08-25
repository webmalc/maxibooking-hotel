<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\HotelBundle\Document\TaskSettings;
use MBH\Bundle\HotelBundle\Form\RoomTypeImageType;
use MBH\Bundle\HotelBundle\Form\RoomTypeTasksType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Form\RoomTypeType;

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
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
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
        $entity->setIsHostel($this->hotel->getIsHostel());
        $form = $this->createForm(new RoomTypeType(), $entity, [
            'facilities' => $this->getParameter('mbh.hotel')['facilities']
        ]);

        return array(
            'form' => $form->createView()
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
        $entity->setHotel($this->hotel);

        $form = $this->createForm(new RoomTypeType(), $entity, []);

        $form->submit($request);
        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();


//            $entity->uploadImage($form['imageFile']->getData());
//            $dm->persist($entity);
//            $dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

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
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
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

        $this->getRequest()->getSession()->getFlashBag()->set('success', 'Изображение успешно удалено.');

        return $this->redirect($this->generateUrl('room_type_edit', ['id' => $id, 'imageTab' => 'active']));

    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="room_type_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     */
    public function editAction(RoomType $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RoomTypeType(), $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'images' => $entity->getImages()
        );
    }


    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="room_type_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     * @Template("MBHHotelBundle:RoomType:edit.html.twig")
     */
    public function updateAction(Request $request, RoomType $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RoomTypeType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success',
                $this->get('translator')->trans('controller.roomTypeController.record_edited_success'));

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
     * @param Request $request
     * @param RoomType $entity
     * @Method({"GET", "POST"})
     * @Route("/{id}/edit/tasks", name="room_type_task_edit")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     * @Template()
     * @return array
     */
    public function editAutoTasksAction(Request $request, RoomType $entity)
    {
        if(!$entity->getTaskSettings()) {
            $entity->setTaskSettings(new TaskSettings());
        }
        $form = $this->createForm(new RoomTypeTasksType(), $entity->getTaskSettings());

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if($form->isValid()) {
                //$this->dm->persist($entity);
                $this->dm->persist($entity->getTaskSettings());
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.roomTypeController.record_edited_success'));

                return $this->afterSaveRedirect('room_type', $entity->getId(), [], '_task_edit');
            }
        }

        return [
            'form' => $form->createView(),
            'entity' => $entity,
            'logs' => $this->logs($entity),
        ];
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
     * @Route("/image/{imageId}/main/{id}/edit", name="room_type_image_make_main")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
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

        $request->getSession()->getFlashBag()
            ->set('success', 'Фотография успешно была сделана главной.');

        return $this->redirectToRoute('room_type_image_edit', ['id' => $entity->getId()]);
    }



    /**
     * Update room.
     *
     * @Route("/images/{id}/edit", name="room_type_image_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @ParamConverter(class="MBHHotelBundle:RoomType")
     * @Template()
     */
    public function editImagesAction(Request $request, RoomType $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(new RoomTypeImageType());
        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST) && $form->isValid()) {
            $image = new RoomTypeImage();
            $image->uploadImage($form['imageFile']->getData());
            $entity->addImage($image);
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Фотография успешно создана.');

            return $this->redirectToRoute('room_type_image_room_edit', [
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
}
