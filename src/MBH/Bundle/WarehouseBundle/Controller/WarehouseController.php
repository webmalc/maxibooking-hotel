<?php

namespace MBH\Bundle\WarehouseBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Form\WareCategoryType;
use MBH\Bundle\WarehouseBundle\Form\WareItemType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


/**
 * @Route("management/warehouse")
 */
class WarehouseController extends Controller
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="warehouse_category")
     * @Security("is_granted('ROLE_WAREHOUSE_ITEMS_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHWarehouseBundle:WareCategory')->createQueryBuilder('q')
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
     * @Route("/", name="warehouse_ware_category_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_ITEMS_EDIT')")
     * @Template()
     */
    public function saveChangesAction(Request $request)
    {
        $entries = $request->get('entries');
        $serviceRepository = $this->dm->getRepository('MBHWarehouseBundle:WareItem');

        foreach ($entries as $id => $data) {
            $entity = $serviceRepository->find($id);
            $price = (float) $data['price'];
            isset($data['enabled']) && $data['enabled'] ? $isEnabled = true : $isEnabled = false;

            $entity->setPrice((empty($price)) ? null : (float)$price)
                ->setIsEnabled($isEnabled)
            ;
            $this->dm->persist($entity);
            $this->dm->flush();
        };

        $request->getSession()->getFlashBag()->set('success', 'warehouse.items.saveSuccess');

        return $this->redirectToRoute('warehouse_category');
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/{id}/new/entry", name="warehouse_category_entry_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_ITEMS_NEW')")
     * @Template()
     * @ParamConverter(class="MBHWarehouseBundle:WareCategory")
     */
    public function newEntryAction(WareCategory $entity)
    {
        $entry = new WareItem();
        $form = $this->createForm(new WareItemType(), $entry);

        return [
            'entry' => $entry,
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/{id}/create/entry", name="warehouse_category_entry_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_ITEMS_NEW')")
     * @Template("MBHWarehouseBundle:Warehouse:newEntry.html.twig")
     * @ParamConverter(class="MBHWarehouseBundle:WareCategory")
     */
    public function createEntryAction(Request $request, WareCategory $entity)
    {
        $entry = new WareItem();
        $entry->setCategory($entity);

        $form = $this->createForm(new WareItemType(), $entry);

        $form->submit($request);

        if ($form->isValid()) {
			
            $this->dm->persist($entry);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.items.newSuccess');

            return $this->isSavedRequest() ?
                $this->redirectToRoute('warehouse_category_entry_edit', ['id' => $entry->getId()]) :
                $this->redirectToRoute('warehouse_category', ['tab' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/edit/entry", name="warehouse_category_entry_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_ITEMS_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHWarehouseBundle:WareItem")
     */
    public function editEntryAction(WareItem $entry)
    {
        $form = $this->createForm(new WareItemType(), $entry);

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
     * @Route("/{id}/update/entry", name="warehouse_category_entry_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_WAREHOUSE_ITEMS_EDIT')")
     * @Template("MBHWarehouseBundle:Warehouse:editEntry.html.twig")
     * @ParamConverter(class="MBHWarehouseBundle:WareItem")
     */
    public function updateEntryAction(Request $request, WareItem $entry)
    {
        $form = $this->createForm(new WareItemType(), $entry);

        $form->submit($request);
		
        if ($form->isValid()) {
            $this->dm->persist($entry);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.items.editSuccess');

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('warehouse_category_entry_edit', ['id' => $entry->getId()]);
            }

            return $this->redirectToRoute('warehouse_category', ['tab' => $entry->getCategory()->getId()]);
        }

        return [
            'entry' => $entry,
            'entity' => $entry->getCategory(),
            'form' => $form->createView(),
            'logs' => $this->logs($entry)
        ];
    }

    /**
     * Displays a form to create a new category.
     *
     * @Route("/new", name="warehouse_category_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_CAT_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new WareCategory();
        $form = $this->createForm(new WareCategoryType(), $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="warehouse_category_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_CAT_NEW')")
     * @Template("MBHWarehouseBundle:Warehouse:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new WareCategory();

        $form = $this->createForm(new WareCategoryType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.cat.newSuccess');

            return $this->afterSaveRedirect('warehouse_category', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="warehouse_category_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_CAT_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHWarehouseBundle:WareCategory")
     */
    public function editAction(WareCategory $entity)
    {
        $form = $this->createForm(new WareCategoryType(), $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="warehouse_category_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_WAREHOUSE_CAT_EDIT')")
     * @Template("MBHWarehouseBundle:Warehouse:edit.html.twig")
     * @ParamConverter(class="MBHWarehouseBundle:WareCategory")
     */
    public function updateAction(Request $request, WareCategory $entity)
    {
        $form = $this->createForm(new WareCategoryType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.cat.editSuccess');

            return $this->afterSaveRedirect('warehouse_category', $entity->getId(), ['tab' => $entity->getId()]);
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
     * @Route("/{id}/delete", name="warehouse_category_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_CAT_DELETE')")
     */
    public function deleteAction(WareCategory $category)
    {
        if ($category->getSystem()) {
            throw $this->createNotFoundException();
        }

        return $this->deleteEntity($category->getId(), 'MBHWarehouseBundle:WareCategory', 'warehouse_category');
    }

    /**
     * Delete entry.
     *
     * @Route("/{id}/entry/delete", name="warehouse_category_entry_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_ITEMS_DELETE')")
     * @ParamConverter(class="MBHWarehouseBundle:WareItem")
     */
    public function deleteEntryAction(Request $request, WareItem $entity)
    {
        try {
            $catId = $entity->getCategory()->getId();
            $this->dm->remove($entity);
            $this->dm->flush($entity);

            $request->getSession()->getFlashBag()->set('success', 'warehouse.items.delSuccess');
        } catch (DeleteException $e) {
            $request->getSession()->getFlashBag()->set('danger', $e->getMessage());
        }

        return $this->redirectToRoute('warehouse_category', ['tab' => $catId]);
    }
	
}
