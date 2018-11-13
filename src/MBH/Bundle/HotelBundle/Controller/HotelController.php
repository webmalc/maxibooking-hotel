<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelContactInformationType;
use MBH\Bundle\HotelBundle\Form\HotelExtendedType;
use MBH\Bundle\HotelBundle\Form\HotelImageType;
use MBH\Bundle\HotelBundle\Form\HotelType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
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
     * @Route("/{id}/edit", name="hotel_update")
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
    public function deleteImageLogoAction(Hotel $hotel)
    {
        $hotel->removeLogoImage();
        $this->dm->flush();

        return $this->redirect($this->generateUrl('hotel_edit', ['id' => $hotel->getId()]));
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

            if ($hotel->getStreet() && !$hotel->getInternationalStreetName()) {
                $hotel->setInternationalStreetName(Helper::translateToLat($hotel->getStreet()));
            }

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
     * @throws \Exception
     */
    public function imagesAction(Request $request, Hotel $hotel)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(HotelImageType::class);
        $panoramaForm = $this->createForm(HotelImageType::class, null, [
            'group_title' => 'form.hotel_images.groups.panorama',
            'buttonId' => 'panorama-button'
        ]);

        $this->get('mbh.site_manager')->addFormErrorsForFieldsMandatoryForSite($hotel, $form, 'hotel_images');

        if ($request->isMethod('POST')) {
            if ($request->request->get('panorama_image') === 'true') {
                $this->savePanoramaImage($request, $hotel);

                return $this->redirectToRoute('hotel_images', ['id' => $hotel->getId()]);
            } else {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $this->saveHotelImage($hotel, $form);

                    return $this->redirectToRoute('hotel_images', ['id' => $hotel->getId()]);
                }
            }
        }

        $mainImage = $hotel->getMainImage();
        $notMainImages = array_filter($hotel->getImages()->toArray(), function(Image $image) use ($mainImage) {
            return $image != $mainImage;
        });


        return [
            'entity' => $hotel,
            'images_form' => $form->createView(),
            'panorama_form' => $panoramaForm->createView(),
            'images' => $notMainImages,
            'mainImage' => $mainImage
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
        $hotel->setHotelMainImage($newMainImage);

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
        $warnings = $this->get('mbh.hotel.hotel_manager')->remove($hotel);
        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                $this->addFlash('danger', $warning);
            }

            return $this->redirectToRoute('hotel');
        }

        $response = $this->deleteEntity($hotel->getId(), 'MBHHotelBundle:Hotel', 'hotel');

        return $response;
    }

    /**
     * @param Request $request
     * @param Hotel $hotel
     * @throws \Exception
     */
    private function savePanoramaImage(Request $request, Hotel $hotel): void
    {
        $panoramaImage = $this
            ->get('mbh.image_manager')
            ->saveFromBase64StringToFile($request->request->get('imagebase64'), 'panorama');
        $hotel->addImage($panoramaImage);
        $hotel->setHotelMainImage($panoramaImage);

        $this->dm->persist($panoramaImage);
        $this->dm->flush();

        $this->addFlash('success', 'controller.hotelController.success_add_photo');
    }

    /**
     * @param Hotel $hotel
     * @param $form
     */
    private function saveHotelImage(Hotel $hotel, FormInterface $form): void
    {
        /** @var Image $image */
        $image = $form->getData();
        $hotel->addImage($image);
        if ($image->getIsDefault()) {
            $hotel->setHotelMainImage($image);
        }
        $this->dm->persist($image);
        $this->dm->flush();

        $this->addFlash('success', 'controller.hotelController.success_add_photo');
    }
}
