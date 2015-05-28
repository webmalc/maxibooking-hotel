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
        $methods = [
            'cash' => "Наличные",
            'cashless_electronic' => "Безнал (в том числе электронные)",
            'cashless' => "Безнал",
            'electronic' =>  "Электронный",
            'all' => "Все",
        ];

        return [
            'methods' => $methods,//$this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            //'form' => $form->createView()
        ];
    }

    private function requestToCashCriteria(Request $request)
    {
        $queryCriteria = new CashDocumentQueryCriteria();
        $clientDataTableParams = ClientDataTableParams::createFromRequest($request);
        $clientDataTableParams->setSortColumnFields([
            1 => 'number',
            3 => 'total',
            4 => 'total',
            6 => 'documentDate',
            7 => 'paidDate',
            8 => 'deletedAt'
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

        $methods = $request->get('methods');
        if ($methods == 'cashless_electronic') {
            $methods = ['cashless', 'electronic'];
        } elseif ($methods == 'all' || !$methods) {
            $methods = [];
        } else {
            $methods = [$methods];
        }

        $queryCriteria->methods = $methods;

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
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->getFilterCollection()->disable('softdeleteable');
        $entity = $dm->getRepository('MBHCashBundle:CashDocument')->find($id);
        $dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity || !$entity->getIsPaid() || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            return new JsonResponse([
                'error' => true,
                'message' => 'CashDocument not found'
            ]);
        }
        $entity->setIsConfirmed(true);
        $dm->persist($entity);
        $dm->flush();

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
    public function payAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->getFilterCollection()->disable('softdeleteable');
        $entity = $dm->getRepository('MBHCashBundle:CashDocument')->find($id);
        $dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            return new JsonResponse([
                'error' => true,
                'message' => 'CashDocument not found'
            ]);
        }
        $entity->setIsPaid(true);
        $dm->persist($entity);
        $dm->flush();

        return new JsonResponse([
            'error' => false,
            'message' => $this->get('translator')->trans('controller.cashController.payment_confirmed_success')
        ]);
    }

    /**
     * Get city by query
     *
     * @Route("/payer/{id}", name="cash_payer", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted(['ROLE_MANAGER', 'ROLE_BOOKKEEPER'])")
     * @return JsonResponse
     */
    public function payerAction(Request $request, $id = null)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (empty($id) && empty($request->get('query'))) {
            return new JsonResponse([]);
        }

        if (!empty($id)) {
            $payer =  $dm->getRepository('MBHPackageBundle:Tourist')->find($id);

            if ($payer) {

                $text = $payer->getFullName();

                if (!empty($payer->getBirthday())) {
                    $text .= ' (' . $payer->getBirthday()->format('d.m.Y')  . ')';
                }

                return new JsonResponse([
                    'id' => $payer->getId(),
                    'text' => $text
                ]);
            }
        }

        $payers = $dm->getRepository('MBHPackageBundle:Tourist')->createQueryBuilder('q')
            ->field('fullName')->equals(new \MongoRegex('/.*' . $request->get('query') . '.*/i'))
            ->sort(['fullName' => 'asc', 'birthday' => 'desc'])
            ->getQuery()
            ->execute()
        ;

        $data = [];

        foreach ($payers as $payer) {

            $text = $payer->getFullName();

            if (!empty($payer->getBirthday())) {
                $text .= ' (' . $payer->getBirthday()->format('d.m.Y')  . ')';
            }

            $data[] = [
                'id' => $payer->getId(),
                'text' => $text
            ];
        }

        return new JsonResponse($data);
    }

}
