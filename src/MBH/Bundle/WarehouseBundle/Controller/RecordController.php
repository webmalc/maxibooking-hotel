<?php

namespace MBH\Bundle\WarehouseBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\WarehouseBundle\Document\Record;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\WarehouseBundle\Form\WareCategoryType;
use MBH\Bundle\WarehouseBundle\Form\RecordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


/**
 * @Route("warehouse/record")
 */
class RecordController extends Controller
{
	/**
     * Lists all records.
     *
     * @Route("/", name="warehouse_record")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHWarehouseBundle:Record')->createQueryBuilder('q')
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();
		
        return [
            'entities' => $entities,
            'config' => $this->container->getParameter('mbh.services')
        ];
    }

    /**
     * Displays a form to create a new record.
     *
     * @Route("/new", name="warehouse_record_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Record();
		
        $form = $this->getForm($entity);
		
		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();

        return [
            'entity' => $entity,
            'form' => $form->createView(),
			'items' => $items,
        ];
    }

    /**
     * Creates a new record.
     *
     * @Route("/create", name="warehouse_record_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_NEW')")
     * @Template("MBHWarehouseBundle:Record:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Record();

        $form = $this->getForm($entity);
		
		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();

        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.record.newSuccess');

            return $this->afterSaveRedirect('warehouse_record', $entity->getId()/*, ['tab' => $entity->getId()]*/);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
			'items' => $items,
        ];
    }

    /**
     * Displays a form to edit an existing record.
     *
     * @Route("/{id}/edit", name="warehouse_record_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHWarehouseBundle:Record")
     */
    public function editAction(Record $entity)
    {
        $form = $this->getForm($entity);

		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
			'items' => $items,
        ];
    }

    /**
     * Edits an existing record.
     *
     * @Route("/{id}", name="warehouse_record_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_EDIT')")
     * @Template("MBHWarehouseBundle:Record:edit.html.twig")
     * @ParamConverter(class="MBHWarehouseBundle:Record")
     */
    public function updateAction(Request $request, Record $entity) {
        $form = $this->getForm($entity);
		
		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();
		
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.record.editSuccess');

            return $this->afterSaveRedirect('warehouse_record', $entity->getId()/*, ['tab' => $entity->getId()]*/);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
			'items' => $items,
        ];
    }

    /**
     * Delete record.
     *
     * @Route("/{id}/delete", name="warehouse_record_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_DELETE')")
     */
    public function deleteAction(Record $category) {
        if ($category->getIsSystem()) {
            throw $this->createNotFoundException();
        }

        return $this->deleteEntity($category->getId(), 'MBHWarehouseBundle:Record', 'warehouse_record');
    }
	
	/**
	 * Aux for this controller.
	 * 
	 * @param Record $entity
	 * @return Form
	 */
	private function getForm(Record $entity) {
		
        return $this->createForm(new RecordType(), $entity, [
            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
	}

}
