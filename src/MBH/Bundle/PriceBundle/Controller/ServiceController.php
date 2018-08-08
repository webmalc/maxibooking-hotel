<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use MBH\Bundle\PriceBundle\Form\ServiceCategoryType;
use MBH\Bundle\PriceBundle\Form\ServiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("service")
 */
class ServiceController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="price_service_category")
     * @Security("is_granted('ROLE_SERVICE_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHPriceBundle:ServiceCategory')->createQueryBuilder('q')
            ->field('hotel.id')->equals($this->get('mbh.hotel.selector')->getSelected()->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        return [
            'entities' => $entities,
            'config' => $this->container->getParameter('mbh.services')
        ];
    }

    /**
     * save entries prices
     *
     * @Route("/", name="price_service_category_save_prices")
     * @Method("POST")
     * @Security("is_granted('ROLE_SERVICE_EDIT')")
     * @Template()
     */
    public function savePricesAction(Request $request)
    {
        $entries = $request->get('entries');
        $serviceRepository = $this->dm->getRepository('MBHPriceBundle:Service');

        foreach ($entries as $id => $data) {
            $entity = $serviceRepository->find($id);
            $price = (float) $data['price'];
            isset($data['enabled']) && $data['enabled'] ? $isEnabled = true : $isEnabled = false;

            if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                continue;
            }

            $entity->setPrice((empty($price)) ? null : (float)$price)
                ->setIsEnabled($isEnabled)
            ;
            $this->dm->persist($entity);
            $this->dm->flush();
        };

        $request->getSession()->getFlashBag()->set('success', $this->get('translator')->trans('price.controller.servicecontroller.price_successful_saved'));

        return $this->redirectToRoute('price_service_category');
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/{id}/new/entry", name="price_service_category_entry_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_SERVICE_NEW')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:ServiceCategory")
     */
    public function newEntryAction(ServiceCategory $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $entry = new Service();
        $form = $this->createForm(ServiceType::class, $entry, [
            'calcTypes' => $this->container->getParameter('mbh.services')['calcTypes']
        ]);

        return [
            'entry' => $entry,
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/{id}/create/entry", name="price_service_category_entry_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_SERVICE_NEW')")
     * @Template("MBHPriceBundle:Service:newEntry.html.twig")
     * @ParamConverter(class="MBHPriceBundle:ServiceCategory")
     */
    public function createEntryAction(Request $request, ServiceCategory $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $entry = new Service();
        $entry->setCategory($entity);

        $form = $this->createForm(ServiceType::class, $entry, [
            'calcTypes' => $this->container->getParameter('mbh.services')['calcTypes']
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!empty($request->get("mbh_bundle_pricebundle_service_type")["time"])) {
                $date = date_create();
                $entry->setDate(date_format($date, 'U = H:i:s'));
            }
            $this->dm->persist($entry);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', $this->container->get('translator')->trans('price.controller.servicecontroller.entry_successful_saved'));

            return $this->isSavedRequest() ?
                $this->redirectToRoute('price_service_category_entry_edit', ['id' => $entry->getId()]) :
                $this->redirectToRoute('price_service_category', ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/edit/entry", name="price_service_category_entry_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_SERVICE_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Service")
     */
    public function editEntryAction(Service $entry)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entry->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ServiceType::class, $entry, [
            'calcTypes' => $this->container->getParameter('mbh.services')['calcTypes']
        ]);

        return [
            'entry' => $entry,
            'entity' => $entry->getCategory(),
            'form' => $form->createView(),
            'logs' => $this->logs($entry)
        ];
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/edit/entry", name="price_service_category_entry_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_SERVICE_EDIT')")
     * @Template("MBHPriceBundle:Service:editEntry.html.twig")
     * @ParamConverter(class="MBHPriceBundle:Service")
     */
    public function updateEntryAction(Request $request, Service $entry)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entry->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ServiceType::class, $entry, [
            'calcTypes' => $this->container->getParameter('mbh.services')['calcTypes']
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($entry);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', $this->container->get('translator')->trans('price.controller.servicecontroller.entry_successfully_updated'));

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('price_service_category_entry_edit', ['id' => $entry->getId()]);
            }

            return $this->redirectToRoute('price_service_category', ['tab' => $entry->getCategory()->getId()]);
        }

        return [
            'entry' => $entry,
            'entity' => $entry->getCategory(),
            'form' => $form->createView(),
            'logs' => $this->logs($entry)
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="price_service_category_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_SERVICE_CATEGORY_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ServiceCategory();
        $form = $this->createForm(ServiceCategoryType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="price_service_category_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_SERVICE_CATEGORY_NEW')")
     * @Template("MBHPriceBundle:Service:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ServiceCategory();
        $entity->setHotel($this->hotel);

        $form = $this->createForm(ServiceCategoryType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', $this->container->get('translator')->trans('price.controller.servicecontroller.entry_successful_saved'));

            return $this->afterSaveRedirect('price_service_category', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="price_service_category_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_SERVICE_CATEGORY_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:ServiceCategory")
     */
    public function editAction(ServiceCategory $entity)
    {
        if ($entity->getSystem() || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ServiceCategoryType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="price_service_category_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_SERVICE_CATEGORY_EDIT')")
     * @Template("MBHPriceBundle:Service:edit.html.twig")
     * @ParamConverter(class="MBHPriceBundle:ServiceCategory")
     */
    public function updateAction(Request $request, ServiceCategory $entity)
    {
        if ($entity->getSystem() || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ServiceCategoryType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', $this->container->get('translator')->trans('price.controller.servicecontroller.entry_successfully_updated'));

            return $this->afterSaveRedirect('price_service_category', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="price_service_category_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_SERVICE_CATEGORY_DELETE')")
     */
    public function deleteAction(ServiceCategory $category)
    {
        if ($category->getSystem()) {
            throw $this->createNotFoundException();
        }

        return $this->deleteEntity($category->getId(), 'MBHPriceBundle:ServiceCategory', 'price_service_category');
    }

    /**
     * Delete entry.
     *
     * @Route("/{id}/entry/delete", name="price_service_category_entry_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_SERVICE_DELETE')")
     * @ParamConverter(class="MBHPriceBundle:Service")
     */
    public function deleteEntryAction(Request $request, Service $entity)
    {
        if ($entity->getSystem()) {
            throw $this->createNotFoundException();
        }

        try {
            if ($entity->getSystem() || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                throw $this->createNotFoundException();
            }
            $catId = $entity->getCategory()->getId();
            $this->dm->remove($entity);
            $this->dm->flush($entity);

            $this->addFlash('success', 'controller.baseController.delete_record_success');
        } catch (DeleteException $e) {
            $this->addFlash('danger', $this->get('translator')->trans($e->getMessage(), ['%total%' => $e->total]));
        }

        return $this->redirectToRoute('price_service_category', ['tab' => $catId]);
    }
}
