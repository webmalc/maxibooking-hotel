<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber\Relationship;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelContactInformationType;
use MBH\Bundle\HotelBundle\Form\HotelExtendedType;
use MBH\Bundle\HotelBundle\Form\HotelImageType;
use MBH\Bundle\HotelBundle\Form\HotelType;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HotelController extends Controller
{
    /**
     * Select hotel.
     *
     * @Route("/notfound", name="hotel_not_found")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     * @Template()
     */
    public function notFoundAction()
    {
        return [];
    }

    /**
     * Select hotel.
     *
     * @Route("/{id}/select", name="hotel_select")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_VIEW')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function selectHotelAction(Request $request, $id)
    {
        $this->get('mbh.hotel.selector')->setSelected($id);

        if ($request->get('url')) {
            return $this->redirect($request->get('url'));
        }

        return $this->redirect($this->generateUrl('_welcome'));
    }

    /**
     * Lists all entities.
     *
     * @Route("/", name="hotel")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_VIEW')")
     * @Template()
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function indexAction()
    {
        $entities = $this->dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder()
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        return [
            'entities' => $entities,
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="hotel_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Hotel();
        $form = $this->createForm(HotelType::class, $entity);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/new", name="hotel_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_HOTEL_NEW')")
     * @Template("MBHHotelBundle:Hotel:new.html.twig")
     * @see HotelManager::create
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $entity = new Hotel();
        $form = $this->createForm(HotelType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('mbh.hotel.hotel_manager')->create($entity);
            $this->get('mbh.form_data_handler')->saveTranslationsFromMultipleFieldsForm($form, $request, ['description']);
            $this->addFlash('success', 'controller.hotelController.record_created_success');

            return $this->afterSaveRedirect('hotel', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="hotel_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template("MBHHotelBundle:Hotel:edit.html.twig")
     * @ParamConverter("entity", class="MBHHotelBundle:Hotel")
     * @param Request $request
     * @param Hotel $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, Hotel $entity)
    {
        $form = $this->createForm(HotelType::class, $entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('mbh.form_data_handler')
                ->saveTranslationsFromMultipleFieldsForm($form, $request, ['description', 'fullTitle']);
            $this->dm->flush();
            $this->dm->refresh($entity);
            $this->addFlash('success', 'controller.hotelController.record_edited_success');

            return $this->afterSaveRedirect('hotel', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="hotel_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template()
     * @param Hotel $entity
     * @return array
     */
    public function editAction(Hotel $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity)) {
            throw $this->createNotFoundException();
        }

        $logoImageDeleteUrl = $this->generateUrl('hotel_delete_logo_image', ['id' => $entity->getId()]);

        $logoDownloadUrl = null;
        if ($entity->getLogoImage()) {
            $logoImageId = $entity->getLogoImage()->getId();
            $logoDownloadUrl = $this->generateUrl('hotel_logo_download', ['id' => $logoImageId]);
        }

        $form = $this->createForm(HotelType::class, $entity, [
            'logo_image_delete_url' => $logoImageDeleteUrl,
            'logo_image_download_url' => $logoDownloadUrl
        ]);

        $this->get('mbh.site_manager')->addFormErrorsForFieldsMandatoryForSite($entity, $form, 'hotel_edit');

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }


    /**
     * Download Logo
     *
     * @Route("/{id}/logo/download", name="hotel_logo_download")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadLogo(Image $image)
    {
        $downloader = $this->get('mbh.protected.file.downloader');

        return $downloader->downloadPublicImage($image);
    }

    /**
     *
     * @Route("/{id}/delete/logo", name="hotel_delete_logo")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @param Hotel $entity
     * @return Response
     */
    public function deleteLogoAction(Hotel $entity)
    {
        if ($entity->getFile() || $entity->getLogo()) {
            $entity->setLogo(null);
            $entity->deleteFile();

            $this->dm->persist($entity);
            $this->dm->flush();
        }
        return $this->redirect($this->generateUrl('hotel_edit', ['id' => $entity->getId()]));
    }

    /**
     * @param Hotel $hotel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{id}/logoImage/delete", name="hotel_delete_logo_image")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     */
    public function deleteImageLogoAction(Request $request, Hotel $hotel)
    {
        $hotel->removeLogoImage();
        $this->dm->flush();
        $redirectUrl = $request->get('redirect_url')
            ? $request->get('redirect_url')
            : $this->generateUrl('hotel_edit', ['id' => $hotel->getId()]) ;

        return $this->redirect($redirectUrl);
    }

    /**
     * Displays a form to edit extended config of an existing entity.
     *
     * @Route("/{id}/edit/extended", name="hotel_edit_extended")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template()
     * @param Hotel $entity
     * @return array
     */
    public function extendedAction(Hotel $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity)) {
            throw $this->createNotFoundException();
        }


        $form = $this->createForm(HotelExtendedType::class, $entity, [
            'config' => $this->container->getParameter('mbh.hotel'),
        ]);

        $this->get('mbh.site_manager')->addFormErrorsForFieldsMandatoryForSite($entity, $form, 'hotel_edit_extended');

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Save extended config of an existing entity.
     *
     * @Route("/{id}/edit/extended", name="hotel_edit_extended_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template("MBHHotelBundle:Hotel:extended.html.twig")
     * @param Request $request
     * @param Hotel $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function extendedUpdateAction(Request $request, Hotel $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(HotelExtendedType::class, $entity, [
            'config' => $this->container->getParameter('mbh.hotel'),
            'method' => Request::METHOD_POST
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->flush();

            $this->addFlash('success', $this->get('translator')->trans('controller.hotelController.record_edited_success'));

            return $this->afterSaveRedirect('hotel', $entity->getId(), [], '_edit_extended');
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @param Request $request
     * @param Hotel $hotel
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Route("/{id}/edit/contact", name="hotel_contact_information")
     * @Template()
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function contactInformationAction(Request $request, Hotel $hotel)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(HotelContactInformationType::class, $hotel);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('mbh.form_data_handler')
                ->saveTranslationsFromMultipleFieldsForm($form, $request, ['settlement', 'street']);

            $this->dm->persist($hotel);
            $this->dm->flush();

            $this->addFlash('success', 'controller.hotelController.record_edited_success');

            return $this->afterSaveRedirect('hotel', $hotel->getId(), [], '_contact_information');
        }

        $this->get('mbh.site_manager')->addFormErrorsForFieldsMandatoryForSite($hotel, $form, 'hotel_contact_information');

        return [
            'entity' => $hotel,
            'form' => $form->createView(),
            'logs' => $this->logs($hotel)
        ];
    }

    /**
     * @Route("/{id}/edit/images", name="hotel_images")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template()
     * @param Request $request
     * @param Hotel $hotel
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function imagesAction(Request $request, Hotel $hotel)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(HotelImageType::class);

        $this->get('mbh.site_manager')->addFormErrorsForFieldsMandatoryForSite($hotel, $form, 'hotel_images');

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var Image $image */
            $image = $form->getData();
            $hotel->addImage($image);
            if ($image->getIsDefault()) {
                $hotel->setDefaultImage($image);
            }
            $this->dm->persist($image);
            $this->dm->flush();

            $this->addFlash('success', 'controller.hotelController.record_edited_success');

            return $this->afterSaveRedirect('hotel', $hotel->getId(), [], '_images');
        }

        return [
            'entity' => $hotel,
            'form' => $form->createView(),
            'images' => $hotel->getImages()
        ];
    }

    /**
     * Delete image
     *
     * @Route("/{id}/delete/images/{imageId}", name="hotel_image_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_DELETE')")
     * @param Hotel $hotel
     * @ParamConverter("image", options={"id" = "imageId"})
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function imageDelete(Hotel $hotel, Image $image)
    {
        if (!$hotel || !$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
            throw $this->createNotFoundException();
        }
        $hotel->removeImage($image);
        $this->dm->flush();

        $this->addFlash('success', 'controller.hotelController.success_delete_photo');

        return $this->redirectToRoute('hotel_images', ['id' => $hotel->getId()]);
    }

    /**
     * Make image main.
     *
     * @Route("/{id}/set_main/images/{imageId}", name="hotel_image_make_main")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @ParamConverter("newMainImage", options={"id" = "imageId"})
     * @param Hotel $hotel
     * @param Image $newMainImage
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makeMainHotelImageAction(Hotel $hotel, Image $newMainImage)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
            throw $this->createNotFoundException();
        }
        $hotel->setDefaultImage($newMainImage);

        $this->dm->flush();
        $this->addFlash('success', 'controller.hotelController.success_main_image_set');

        return $this->redirectToRoute('hotel_images', ['id' => $hotel->getId()]);
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="hotel_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_DELETE')")
     * @param Hotel $hotel
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \MBH\Bundle\PackageBundle\Lib\DeleteException
     */
    public function deleteAction(Hotel $hotel)
    {
        $relatedDocumentsData = $this->helper->getRelatedDocuments($hotel);

        foreach ($relatedDocumentsData as $relatedDocumentData) {
            /** @var Relationship $relationship */
            $relationship = $relatedDocumentData['relation'];
            $quantity = $relatedDocumentData['quantity'];
            if (!in_array($relationship->getDocumentClass(), [ServiceCategory::class, Service::class])
                && $quantity > 0
                //If there are tariffs in addition to the main
                && ($relationship->getDocumentClass() !== Tariff::class || $relatedDocumentData['quantity'] > 1)
            ) {
                $messageId = $relationship->getErrorMessage() ? $relationship->getErrorMessage() : 'exception.relation_delete.message';
                $flashMessage = $this->get('translator')->trans($messageId, ['%total%' =>  $quantity]);
                $this->addFlash('danger', $flashMessage);

                return $this->redirectToRoute('hotel');
            }
        }

        $hotelMainTariff = $this->dm
            ->getRepository('MBHPriceBundle:Tariff')
            ->findOneBy(['isDefault' => true, 'hotel.id' => $hotel->getId()]);

        if (!empty($hotelMainTariff)) {
            $this->get('mbh.tariff_manager')->forceDelete($hotelMainTariff);
        }

        foreach ($hotel->getServices() as $service) {
            $this->dm->remove($service);
        }
        $this->dm->flush();

        foreach ($hotel->getServicesCategories() as $serviceCategory) {
            $this->dm->remove($serviceCategory);
        }
        $this->dm->flush();

        $response = $this->deleteEntity($hotel->getId(), 'MBHHotelBundle:Hotel', 'hotel');

        return $response;
    }
}
