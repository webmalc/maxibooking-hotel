<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\TaskSettings;
use MBH\Bundle\HotelBundle\Form\ImagePriorityType;
use MBH\Bundle\HotelBundle\Form\OnlineImageFileType;
use MBH\Bundle\HotelBundle\Form\RoomTypeTasksType;
use MBH\Bundle\HotelBundle\Form\RoomTypeType;
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
        $isDisableableOn = $this->clientConfig->isDisableableOn();
        $filterCollection = $this->dm->getFilterCollection();
        if ($isDisableableOn && !$filterCollection->isEnabled('disableable')) {
            $filterCollection->enable('disableable');
        }

        $entities = $this->dm->getRepository('MBHHotelBundle:RoomType')->createQueryBuilder('s')
            ->field('hotel.id')->equals($this->hotel->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();
        if ($isDisableableOn && $filterCollection->isEnabled('disableable')) {
            $filterCollection->disable('disableable');
        }

        if (!$entities->count()) {
            return $this->redirectToRoute('room_type_new');
        }

        return [
            'entities' => $entities,
            'displayDisabledRoomType' =>
                !$this->get('mbh.client_config_manager')->fetchConfig()->isDisableableOn()
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
            'useRoomTypeCategory' => $this->get('mbh.client_config_manager')->fetchConfig()->getUseRoomTypeCategory(),
            'hotel' => $this->hotel
        ]);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/new", name="room_type_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ROOM_TYPE_NEW')")
     * @Template("MBHHotelBundle:RoomType:new.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $entity = new RoomType();
        $entity->setHotel($this->hotel);
        $form = $this->createForm(RoomTypeType::class, $entity, [
            'useRoomTypeCategory' => $this->get('mbh.client_config_manager')->fetchConfig()->getUseRoomTypeCategory(),
            'hotel' => $entity->getHotel()
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.roomTypeController.success_room_type_creation');

            return $this->afterSaveRedirect('room_type', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }

    /**
     * Delete image
     *
     * @Route("/{id}/image/{imageId}/delete", name="room_type_image_delete")
     * @ParamConverter("roomType", class="MBHHotelBundle:RoomType",options={"id" = "id"})
     * @ParamConverter("image", class="MBHBaseBundle:Image",options={"id" = "imageId"})
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_TYPE_DELETE')")
     * @param RoomType $roomType
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function imageDelete(RoomType $roomType, Image $image)
    {
        if (!$roomType || !$this->container->get('mbh.hotel.selector')->checkPermissions($roomType->getHotel())) {
            throw $this->createNotFoundException();
        }
        if ($image) {
            $roomType->removeOnlineImage($image);
            $imageWasMain = $image->isMain();
            if($imageWasMain) {
                $roomType->makeFirstImageAsMain();
            }
        }

        $this->dm->flush();
        $this->addFlash('success', 'controller.roomTypeController.success_delete_photo');

        return $this->redirectToRoute('room_type_image_edit', ['id' => $roomType->getId(), 'imageTab' => 'active']);
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
            'useRoomTypeCategory' => $this->get('mbh.client_config_manager')->fetchConfig()->getUseRoomTypeCategory(),
            'hotel' => $entity->getHotel()
        ]);

        $this->get('mbh.site_manager')->addFormErrorsForFieldsMandatoryForSite($entity, $form, 'room_type_edit');

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
        $config = $this->get('mbh.client_config_manager')->fetchConfig();
        $form = $this->createForm(RoomTypeType::class, $entity, [
            'useRoomTypeCategory' => $config ? $config->getUseRoomTypeCategory() : false,
            'hotel' => $entity->getHotel()
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$this->get('mbh.client_config_manager')->hasSingleLanguage()) {
                $this->get('mbh.form_data_handler')
                    ->saveTranslationsFromMultipleFieldsForm($form, $request, ['description', 'fullTitle']);
            }

            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.roomTypeController.record_edited_success');

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
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAutoTasksAction(Request $request, RoomType $roomType)
    {
        if (!$roomType->getTaskSettings()) {
            $roomType->setTaskSettings(new TaskSettings());
        }
        $form = $this->createForm(RoomTypeTasksType::class, $roomType->getTaskSettings(), ['hotel' => $roomType->getHotel()]);

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                //$this->dm->persist($entity);
                $this->dm->persist($roomType->getTaskSettings());
                $this->dm->flush();

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('controller.roomTypeController.record_edited_success')
                );

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
    public function makeMainImageRoomTypeAction(RoomType $roomType, $imageId)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($roomType->getHotel())) {
            throw $this->createNotFoundException();
        }
        /* @var $roomType RoomType */
        $roomType->makeMainImageById($imageId);
        $this->dm->persist($roomType);
        $this->dm->flush();

        $this->addFlash('success', 'controller.roomTypeController.success_set_main_photo');

        return $this->redirectToRoute('room_type_image_edit', ['id' => $roomType->getId()]);
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
    public function editImagesAction(Request $request, RoomType $roomType)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($roomType->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(OnlineImageFileType::class);

        $this->get('mbh.site_manager')->addFormErrorsForFieldsMandatoryForSite($roomType, $form, 'room_type_image_edit');

        $form->handleRequest($request);
        /** @var Image $onlineImage */
        if ($form->isSubmitted() && $form->isValid()) {
            $onlineImage = $form->getData();
            $roomType->addOnlineImage($onlineImage);
            if ($onlineImage->getIsDefault()) {
                $roomType->makeMainImage($onlineImage);
            }

            $this->dm->flush();

            $this->addFlash('success', 'controller.roomTypeController.success_set_photo');

            return $this->redirectToRoute('room_type_image_edit', [
                'id' => $roomType->getId()
            ]);
        }

        $images = $roomType->getOnlineImagesByPriority();

        $imagePriorityForm = $this->createForm(ImagePriorityType::class, null, ['action' => '']);

        return array(
            'entity' => $roomType,
            'form' => $form->createView(),
            'logs' => $this->logs($roomType),
            'images' => $images,
            'priorityForm' => $imagePriorityForm->createView()
        );
    }

    /**
     * @param Request $request
     * @param Image $image
     * @Route("/images/{id}/{imageId}/priority/edit", name="room_type_image_edit_priority", options={"expose" = true })
     * @ParamConverter("roomType", class="MBHHotelBundle:RoomType",options={"id" = "id"})
     * @ParamConverter("image", class="MBHBaseBundle:Image",options={"id" = "imageId"})
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_ROOM_TYPE_EDIT')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeImagePriority(Request $request, RoomType $roomType, Image $image)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($roomType->getHotel())) {
            throw $this->createNotFoundException();
        }

        if ($image) {
            $form = $this->createForm(ImagePriorityType::class, $image);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->dm->flush();
            }
        }

        return $this->redirectToRoute('room_type_image_edit', ['id' => $roomType->getId()]);

    }
}
