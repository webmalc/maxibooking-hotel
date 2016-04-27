<?php

namespace MBH\Bundle\WarehouseBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\WarehouseBundle\Document\Invoice;
use MBH\Bundle\WarehouseBundle\Document\Record;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\WarehouseBundle\Form\WareCategoryType;
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
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHWarehouseBundle:Invoice')->createQueryBuilder('q')
            ->sort('fullTitle', 'asc')
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
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Invoice();
		
		$rec1 = new Record();
        $rec1->setPrice(15);
        $entity->addRecord($rec1);
		
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

}
