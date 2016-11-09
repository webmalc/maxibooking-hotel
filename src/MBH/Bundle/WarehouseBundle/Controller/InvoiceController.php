<?php

namespace MBH\Bundle\WarehouseBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\WarehouseBundle\Document\Invoice;
use MBH\Bundle\WarehouseBundle\Document\Record;
use MBH\Bundle\WarehouseBundle\Form\InvoiceType;
use MBH\Bundle\WarehouseBundle\Form\RecordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


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
		
		$res = $this->dm->getRepository('MBHWarehouseBundle:Record')->createQueryBuilder('q')
			->map('function() { emit(this.invoice, this.amount); }')
			->reduce('function(k, v) {				
				return Array.sum(v);
			}')
			->getQuery()
			->execute();
		
		$amounts = [];
		
		foreach ($res as $v) {
			$amounts[$v['_id']['$id']->{'$id'}] = $v['value'];
		}
		
        return [
            'entities' => $entities,
            'config' => $this->container->getParameter('mbh.services'),
			'amounts' => $amounts,
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
		
        $form = $this->createForm(InvoiceType::class, $entity, [
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
		
        $form = $this->createForm(InvoiceType::class, $entity, [
            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
				
		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();
		
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Новая накладная успешно создана');

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
        $form = $this->createForm(InvoiceType::class, $entity, [
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
     * @Route("/{id}/update", name="warehouse_invoice_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_INVOICE_EDIT')")
     * @Template("MBHWarehouseBundle:Invoice:edit.html.twig")
     * @ParamConverter(class="MBHWarehouseBundle:Invoice")
     */
    public function updateAction(Request $request, Invoice $entity) {
        $form = $this->createForm(InvoiceType::class, $entity, [
            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
				
		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();
		
		$invoices = $this->dm->getRepository('MBHWarehouseBundle:Invoice')->find($entity->getId());
		$originalRecords = new \Doctrine\Common\Collections\ArrayCollection();
		
		foreach ($invoices->getRecords() as $rec) {
			$originalRecords->add($rec);
		}
		
        $form->handleRequest($request);

        if ($form->isValid()) {
			foreach ($originalRecords as $rec) {
				if ($entity->getRecords()->contains($rec) === false) {
					$entity->removeRecord($rec);
					
					$this->dm->persist($rec);
				}
			}
			
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Накладная сохранена');

            return $this->afterSaveRedirect('warehouse_invoice', $entity->getId());
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
    public function deleteAction(Invoice $invoice) {
        if ($invoice->getIsSystem()) {
            throw $this->createNotFoundException();
        }

        return $this->deleteEntity($invoice->getId(), 'MBHWarehouseBundle:Invoice', 'warehouse_invoice');
    }
	
}
