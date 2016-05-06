<?php

namespace MBH\Bundle\WarehouseBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\WarehouseBundle\Document\Record;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\WarehouseBundle\Form\RecordType;
use MBH\Bundle\WarehouseBundle\Form\RecordFilterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


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
		$form = $this->createForm(new RecordFilterType());
		
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
		
// TODO:
		$tableParams->setSortColumnFields([
            1 => 'fullTitle',
            2 => 'recordDate',
            3 => 'operation',
            4 => 'hotel',
            5 => 'amount',
            6 => 'qtty',
            7 => 'price',
            8 => 'amount',
            9 => 'bar',
        ]);

//
		
        $formData = (array) $request->get('form');
		
        $form = $this->createForm(new RecordFilterType());
        $formData['search'] = $tableParams->getSearch();

        $form->submit($formData);
		
        $criteria = $form->getData();

        if ($getFirstSort = $tableParams->getFirstSort()) {
            $criteria->sortBy = [$getFirstSort[0]];
            $criteria->sortDirection = [$getFirstSort[1]];
        }
		
//		'order' => $request->get('order')['0']['column'],
//        'dir' => $request->get('order')['0']['dir'],
		
        $records = $this->dm->getRepository('MBHWarehouseBundle:Record')
			->findByQueryCriteria($criteria, $tableParams->getStart(), $tableParams->getLength());
		
//		var_dump($request->get('order')['0']);
		
		//var_dump($criteria->sortBy);
		
        return [
            'records' => iterator_to_array($records),
            'total' => count($records),
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

        $form->submit($request);

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
		
        return $this->createForm(new RecordType(), $entity, [
            'operations' => $this->container->getParameter('mbh.warehouse.operations'),
        ]);
	}

}
