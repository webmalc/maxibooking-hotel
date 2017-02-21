<?php

namespace MBH\Bundle\WarehouseBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\WarehouseBundle\Document\Record;
use MBH\Bundle\WarehouseBundle\Document\RecordFilter;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\WarehouseBundle\Form\RecordFilterType;
use MBH\Bundle\WarehouseBundle\Form\RecordType;
use MBH\Bundle\WarehouseBundle\Lib\RecordQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("warehouse/record")
 */
class RecordController extends Controller
{
	/**
     * Lists all records. Simple display using Ajax jsonAction.
     *
     * @Route("/", name="warehouse_record")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
		$form = $this->createForm(RecordFilterType::class);
		
        return [
			'form' => $form->createView(),
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="records_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_VIEW')")
     * @Template()
     */
    public function jsonAction(Request $request)
    {
        $tableParams = ClientDataTableParams::createFromRequest($request);
		
		$tableParams->setSortColumnFields([
			0 => 'createdAt',
            1 => 'wareItem',
            2 => 'recordDate',
            3 => 'operation',
            4 => 'hotel',
            5 => 'qtty',
            6 => 'unit',
            7 => 'price',
            8 => 'amount',
            9 => 'foo',
        ]);
		
        $formData = (array) $request->get('form');
        $formData['search'] = $tableParams->getSearch();
		
        $form = $this->createForm(RecordFilterType::class);

        $form->submit($formData);
		
        $criteria = $form->getData();
		
        if ($getFirstSort = $tableParams->getFirstSort()) {
            $criteria->setSortBy($getFirstSort[0]);
			
			if ($getFirstSort[0] == 'createdAt') { // at page load w/o settings made by user
				$criteria->setSortDirection(-1);
			} else {
				$criteria->setSortDirection($getFirstSort[1]); // 1 or -1
			}
        }
		
        $records = $this->dm->getRepository('MBHWarehouseBundle:Record')
			->findByQueryCriteria($criteria, $tableParams->getStart(), $tableParams->getLength());
		
        return [
            'records' => iterator_to_array($records),
            'total' => count($records),
            'draw' => $request->get('draw'),
        ];
    }

	/**
     * Lists inventory.
     *
     * @Route("/inventory", name="warehouse_record_inventory")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function inventoryAction()
    {
        $query = new RecordQuery();
        $wareCategories = $this->dm->getRepository('MBHWarehouseBundle:WareCategory')->createQueryBuilder()
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

		$form = $this->createForm(RecordFilterType::class, $query, [
		    'dm' => $this->dm,
            'wareCategories' => $wareCategories,
        ]);
		
        return [
			'form' => $form->createView(),
        ];
    }

    /**
     * Lists goods balance.
     *
     * @Route("/inventory/json", name="inventory_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_VIEW')")
     * @Template()
     */
    public function jsonInventoryAction(Request $request)
    {
        $query = new RecordQuery();
        $tableParams = ClientDataTableParams::createFromRequest($request);

		$tableParams->setSortColumnFields([
			0 => 'foo',
            1 => 'fullTitle',
            2 => 'category',
            3 => 'qtty'
        ]);

        $wareCategories = $this->dm->getRepository('MBHWarehouseBundle:WareCategory')->createQueryBuilder()
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        $formData = (array)$request->get('form');
        $formData['search'] = $tableParams->getSearch();

        $form = $this->createForm(RecordFilterType::class, $query, [
            'wareCategories' => $wareCategories
        ]);

        $form->submit($formData);
        $form->getErrors();
        $criteria = $form->getData();
        $criteria->wareItem = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->find($formData['wareItem']);

        if ($getFirstSort = $tableParams->getFirstSort()) {
			if ($getFirstSort[0] == 'foo') { // at page load w/o settings made by user
				$criteria->setSortBy('fullTitle');
			} else {
				$criteria->setSortDirection($getFirstSort[1]); // 1 or -1
			}

			$criteria->setSortBy($getFirstSort[0]);
        }

		$repository = $this->dm->getRepository('MBHWarehouseBundle:Record');

		// this step could be hidden, but is left here to see what is going on
        $summary = $repository->fetchSummary($criteria);

		$items = $repository->getItemsByIds($criteria, $summary, $tableParams->getStart(), $tableParams->getLength());

        return [
            'inventory' => $items,
            'total' => count($summary),
            'draw' => $request->get('draw'),
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

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.record.newSuccess');

            return $this->afterSaveRedirect('warehouse_record', $entity->getId());
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
     * @Route("/{id}/update", name="warehouse_record_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_WAREHOUSE_RECORD_EDIT')")
     * @Template("MBHWarehouseBundle:Record:edit.html.twig")
     * @ParamConverter(class="MBHWarehouseBundle:Record")
     */
    public function updateAction(Request $request, Record $entity) {
        $form = $this->getForm($entity);
		
		$items = $this->dm->getRepository('MBHWarehouseBundle:WareItem')->findAll();
		
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'warehouse.record.editSuccess');

            return $this->afterSaveRedirect('warehouse_record', $entity->getId());
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
		
        return $this->createForm(RecordType::class, $entity, [
            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
	}

}
