<?php

namespace MBH\Bundle\WarehouseBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\WarehouseBundle\Document\Invoice;
use MBH\Bundle\WarehouseBundle\Document\Record;
use MBH\Bundle\WarehouseBundle\Form\RecordType;
use MBH\Bundle\WarehouseBundle\Form\InvoiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


/**
 * @Route("warehouse/invoice")
 */
class InvoiceController extends Controller
{
	/**
     * Lists all records.
     *
     * @Route("/", name="warehouse_invoice")
     * @Security("is_granted('ROLE_WAREHOUSE_INVOICE_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHWarehouseBundle:Invoice')->createQueryBuilder('q')
            ->sort('invoiceDate', 'desc')
            ->getQuery()
            ->execute();
		
        return [
            'entities' => $entities,
            'config' => $this->container->getParameter('mbh.services')
        ];
    }

    /**
     * Displays a form to create a new invoice.
     *
     * @Route("/new", name="warehouse_invoice_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_INVOICE_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Invoice();
		
        $form = $this->createForm(new InvoiceType(), $entity, [
            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
				
		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();
		
        return [
            'entity' => $entity,
            'form' => $form->createView(),
			'items' => $items,
        ];
    }

    /**
     * Actually creates a new invoice.
     *
     * @Route("/create", name="warehouse_invoice_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_INVOICE_NEW')")
     * @Template("MBHWarehouseBundle:Invoice:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Invoice();
		
        $form = $this->createForm(new InvoiceType(), $entity, [
            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
				
		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();
		
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.record.newSuccess');

            return $this->afterSaveRedirect('warehouse_invoice', $entity->getId()/*, ['tab' => $entity->getId()]*/);
		}

        return [
            'entity' => $entity,
            'form' => $form->createView(),
			'items' => $items,
        ];
    }

    /**
     * Displays a form to edit an existing invoice.
     *
     * @Route("/{id}/edit", name="warehouse_invoice_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_INVOICE_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHWarehouseBundle:Invoice")
     */
    public function editAction(Invoice $entity)
    {
        $form = $this->createForm(new InvoiceType(), $entity, [
            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
				
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
     * @Route("/{id}", name="warehouse_invoice_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_WAREHOUSE_INVOICE_EDIT')")
     * @Template("MBHWarehouseBundle:Invoice:edit.html.twig")
     * @ParamConverter(class="MBHWarehouseBundle:Invoice")
     */
    public function updateAction(Request $request, Invoice $entity) {
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
     * @Route("/{id}/delete", name="warehouse_invoice_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_WAREHOUSE_INVOICE_DELETE')")
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
//            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
	}

}
