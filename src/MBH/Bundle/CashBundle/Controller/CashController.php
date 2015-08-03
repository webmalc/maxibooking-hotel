<?php

namespace MBH\Bundle\CashBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\CashBundle\Form\CashDocumentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CashController extends Controller
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="cash")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     * @Template()
     */
    public function indexAction()
    {
        $methods = $this->container->getParameter('mbh.cash.methods');

        $methods = array_slice($methods, 0, 2, true) +
            ['cashless_electronic' => "Безнал (в т.ч. электронные)"] +
            array_slice($methods, 2, count($methods) - 1, true)
        ;

        return [
            'methods' => $methods,
            'users' => $this->dm->getRepository('MBHUserBundle:User')->findBy(['enabled' => true], ['username' => 'asc']),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
        ];
    }

    private function requestToCashCriteria(Request $request)
    {
        $queryCriteria = new CashDocumentQueryCriteria();
        $clientDataTableParams = ClientDataTableParams::createFromRequest($request);
        $clientDataTableParams->setSortColumnFields([
            1 => 'number',
            2 => 'total',
            3 => 'total',
            4 => 'operation',
            6 => 'documentDate',
            7 => 'paidDate',
            8 => 'createdBy',
            9 => 'deletedAt'
        ]);

        $queryCriteria->skip = $clientDataTableParams->getStart();
        $queryCriteria->limit = $clientDataTableParams->getLength();

        $queryCriteria->sortBy = 'createdAt';
        $queryCriteria->sortDirection = -1;//SORT_DESC;

        if ($getFirstSort = $clientDataTableParams->getFirstSort()) {
            $queryCriteria->sortBy = [$getFirstSort[0]];
            $queryCriteria->sortDirection = [$getFirstSort[1]];
        }

        $queryCriteria->search = $clientDataTableParams->getSearch();

        $method = $request->get('method');
        $availableMethods = $this->container->getParameter('mbh.cash.methods');

        if ($method) {

            if (array_key_exists($method, $availableMethods)) {
                $queryCriteria->methods = [$method];
            } elseif ($method == 'cashless_electronic') {
                $queryCriteria->methods = ['cashless', 'electronic'];
            }
        }

        $queryCriteria->isPaid = !$request->get('show_no_paid');
        $queryCriteria->begin = $this->get('mbh.helper')->getDateFromString($request->get('begin'));
        $queryCriteria->end = $this->get('mbh.helper')->getDateFromString($request->get('end'));

        if (!$queryCriteria->begin) {
            $queryCriteria->begin = new \DateTime('midnight -7 days');
        }

        if (!$queryCriteria->end) {
            $queryCriteria->end = new \DateTime('midnight +1 day');
        }

        $queryCriteria->filterByRange = $request->get('filter');
        $queryCriteria->orderIds = $this->get('mbh.helper')->toIds($this->get('mbh.package.permissions')->getAvailableOrders());

        $queryCriteria->deleted = $request->get('deleted');

        $queryCriteria->createdBy = $request->get('user');

        return $queryCriteria;
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="cash_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     * @Template()
     *
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function jsonAction(Request $request)
    {
        $repository = $this->dm->getRepository('MBHCashBundle:CashDocument');

        $queryCriteria = $this->requestToCashCriteria($request);
        $isByDay = $request->get('by_day');
        if ($isByDay) {
            $queryCriteria->isPaid = true;
        }

        $results = $repository->getListForCash($queryCriteria, $isByDay);

        $params = [
            "draw" => $request->get('draw'),
            'totalIn' => 0,
            'totalOut' => 0,
            'noConfirmedTotalIn' => 0,
            'noConfirmedTotalOut' => 0,
            'total' => 0,
            'recordsFiltered' => 0,
        ];

        if (count($results) > 0) {
            $params['recordsFiltered'] = count($results);
            $queryCriteria->isConfirmed = null;
            $params['totalIn'] = $repository->total('in', $queryCriteria);
            $params['totalOut'] = $repository->total('out', $queryCriteria);
            $params['total'] = $params['totalIn'] - $params['totalOut'];
            $queryCriteria->isConfirmed = false;
            $params['noConfirmedTotalIn'] = $repository->total('in', $queryCriteria);
            $params['noConfirmedTotalOut'] = $repository->total('out', $queryCriteria);
        }

        $this->dm->getFilterCollection()->enable('softdeleteable');

        if ($isByDay) {
            return $this->render('MBHCashBundle:Cash:jsonByDay.json.twig', $params + ['data' => $results]);
        } else {
            return $params + [
                'entities' => $results,
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations'),
            ];
        }
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="cash_edit")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     * @Template()
     * @ParamConverter("entity", class="MBHCashBundle:CashDocument")
     */
    public function editAction(CashDocument $entity, Request $request)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel()))
            throw $this->createNotFoundException();

        $cashDocumentRepository = $this->dm->getRepository('MBHCashBundle:CashDocument');

        $form = $this->createForm(new CashDocumentType($this->dm), $entity,
            [
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations'),
                'payers' => $cashDocumentRepository->getAvailablePayersByOrder($entity->getOrder()),
            ]
        );

        if($request->isMethod("PUT")){
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success', $this->get('translator')->trans('controller.cashController.edit_record_success'));

                return $this->afterSaveRedirect('cash', $entity->getId());
            }
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
     * @Route("/{id}/delete", name="cash_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHCashBundle:CashDocument', 'cash');
    }

    /**
     * Confirm entity.
     *
     * @Route("/{id}/confirm", name="cash_confirm", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     */
    public function confirmAction($id)
    {
        $this->dm->getFilterCollection()->disable('softdeleteable');
        $entity = $this->dm->getRepository('MBHCashBundle:CashDocument')->find($id);
        $this->dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity || !$entity->getIsPaid() || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            return new JsonResponse([
                'error' => true,
                'message' => 'CashDocument not found'
            ]);
        }
        $entity->setIsConfirmed(true);
        $this->dm->persist($entity);
        $this->dm->flush();

        return new JsonResponse([
            'error' => false,
            'message' => $this->get('translator')->trans('controller.cashController.payment_confirmed_success')
        ]);
    }

    /**
     * Pay entity.
     *
     * @Route("/{id}/pay", name="cash_pay", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted(['ROLE_MANAGER', 'ROLE_BOOKKEEPER'])")
     */
    public function payAction($id, Request $request)
    {
        $this->dm->getFilterCollection()->disable('softdeleteable');
        $entity = $this->dm->getRepository('MBHCashBundle:CashDocument')->find($id);
        $this->dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            return new JsonResponse([
                'error' => true,
                'message' => 'CashDocument not found'
            ]);
        }

        $paidDate = \DateTime::createFromFormat('d.m.Y', $request->get('paidDate'));
        if(!$paidDate)
            $paidDate = new \DateTime();

        $entity->setPaidDate($paidDate);
        $entity->setIsPaid(true);

        $violationList = $this->get('validator')->validate($entity);
        if($violationList->count() > 0){
            return new JsonResponse([
                'error' => true,
                'message' => $violationList->get(0)->getMessage()
            ]);
        }
        $this->dm->persist($entity);
        $this->dm->flush();

        return new JsonResponse([
            'error' => false,
            'message' => $this->get('translator')->trans('controller.cashController.payment_confirmed_success')
        ]);
    }
}
