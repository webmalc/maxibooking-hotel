<?php

namespace MBH\Bundle\CashBundle\Controller;

use GuzzleHttp\Client;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentArticle;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\CashBundle\Document\CashDocumentRepository;
use MBH\Bundle\CashBundle\Form\CashDocumentType;
use MBH\Bundle\CashBundle\Form\NewCashDocumentType;
use MBH\Bundle\ClientBundle\Document\Uniteller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CashController

 */
class CashController extends Controller
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="cash")
     * @Method("GET")
     * @Security("is_granted('ROLE_CASH_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $methods = $this->container->getParameter('mbh.cash.methods');

        $methods = array_slice($methods, 0, 2, true) +
            ['cashless_electronic' => "Безнал (в т.ч. электронные)"] +
            array_slice($methods, 2, count($methods) - 1, true);

        $queryCriteria = new CashDocumentQueryCriteria();
        $queryCriteria->methods = ['cash'];
        $queryCriteria->isPaid = true;
        $in = $this->dm->getRepository('MBHCashBundle:CashDocument')->total('in', $queryCriteria);
        $out = $this->dm->getRepository('MBHCashBundle:CashDocument')->total('out', $queryCriteria);
        $total = $in - $out;

        $cashArticleRepository = $this->dm->getRepository(CashDocumentArticle::class);

        /*$cashArticleRepository->createQueryBuilder()->remove()->getQuery()->execute();
        $d = new CashDocumentArticleData();
        $d->setContainer($this->container);
        $d->load($this->dm);*/

        $articles = $cashArticleRepository->findBy(['parent' => ['$exists' => false]], ['code' => 1]);

        return [
            'methods' => $methods,
            'total' => $total,
            'articles' => $articles,
            'users' => $this->dm->getRepository('MBHUserBundle:User')->findBy(['enabled' => true],
                ['username' => 'asc']),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            'typeList' => CashDocumentQueryCriteria::getTypeList()
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="cash_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_CASH_VIEW')")
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
        $this->handleClientDataTableParams($queryCriteria, $request);

        $isByDay = $request->get('by_day');
        if ($isByDay) {
            $queryCriteria->isPaid = true;
        }

        $results = $repository->findByCriteria($queryCriteria, $isByDay);

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
     * @param Request $request
     * @return CashDocumentQueryCriteria
     */
    private function requestToCashCriteria(Request $request)
    {
        $queryCriteria = new CashDocumentQueryCriteria();

        $queryCriteria->sortBy = 'createdAt';
        $queryCriteria->sortDirection = -1;//SORT_DESC;

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

        if ($request->get('show_no_confirmed')) {
            $queryCriteria->isConfirmed = false;
        }

        $queryCriteria->begin = $this->get('mbh.helper')->getDateFromString($request->get('begin'));
        $queryCriteria->end = $this->get('mbh.helper')->getDateFromString($request->get('end'));

        if (!$queryCriteria->begin) {
            $queryCriteria->begin = new \DateTime('midnight -7 days');
        }

        if (!$queryCriteria->end) {
            $queryCriteria->end = new \DateTime('midnight +1 day');
        }

        $queryCriteria->filterByRange = empty($request->get('filter')) ? 'paidDate' : $request->get('filter');

        // TODO: Add acl
        //$queryCriteria->orderIds = $this->get('mbh.helper')->toIds($this->get('mbh.package.permissions')->getAvailableOrders());

        $queryCriteria->deleted = $request->get('deleted');

        $queryCriteria->createdBy = $request->get('user');

        if ($request->get('article')) {
            $queryCriteria->article = $this->dm->getRepository(CashDocumentArticle::class)->find($request->get('article'));
        }

        $queryCriteria->type = $request->get('type');

        return $queryCriteria;
    }

    /**
     * @param CashDocumentQueryCriteria $queryCriteria
     * @param Request $request
     */
    private function handleClientDataTableParams(CashDocumentQueryCriteria $queryCriteria, Request $request)
    {
        $clientDataTableParams = ClientDataTableParams::createFromRequest($request);
        $clientDataTableParams->setSortColumnFields([
            1 => 'number',
            2 => 'order',
            3 => 'total',
            4 => 'total',
            5 => 'operation',
            6 => 'payer',
            7 => 'documentDate',
            8 => 'paidDate',
            9 => 'createdBy',
            10 => 'deletedAt',
            11 => 'note',
        ]);

        $queryCriteria->skip = $clientDataTableParams->getStart();
        $queryCriteria->limit = $clientDataTableParams->getLength();

        if ($getFirstSort = $clientDataTableParams->getFirstSort()) {
            $queryCriteria->sortBy = [$getFirstSort[0]];
            $queryCriteria->sortDirection = [$getFirstSort[1]];
        }

        $queryCriteria->search = $clientDataTableParams->getSearch();
    }

    /**
     * @Route("/export/1c/{method}", name="cash_1c_export", options={"expose"=true}, defaults={"method" = null})
     * @Method("GET")
     * @Security("is_granted('ROLE_CASH_VIEW')")
     * @param Request $request
     * @param string|null $method
     * @return Response
     */
    public function export1cAction(Request $request, $method = null)
    {
        $queryCriteria = $this->requestToCashCriteria($request);
        $queryCriteria->limit = 1000;

        /** @var CashDocumentRepository $cashDocumentRepository */
        $cashDocumentRepository = $this->dm->getRepository('MBHCashBundle:CashDocument');
        $queryCriteria->methods = ['electronic'];
        if($method) {
            $queryCriteria->methods[] = $method;
        }
        $queryCriteria->isPaid = true;
        $cashDocuments = $cashDocumentRepository->findByCriteria($queryCriteria);
        $result = $this->get('mbh.cash.1c_exporter')->export($cashDocuments, $queryCriteria, $this->hotel->getOrganization());

        $result = str_replace("\n", "\r\n",$result);
        $result = mb_convert_encoding($result, 'windows-1251', 'utf-8');
        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/plain; charset=windows-1251');
        $response->headers->set('Content-Disposition','attachment; filename="1cExport.txt"');
        return $response;
    }


    /**
     * @Route("/new", name="cash_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_CASH_NEW')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $cashDocument = new CashDocument();
        $cashDocument->setMethod('cash');

        $form = $this->createForm(NewCashDocumentType::class, $cashDocument, [
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            'payers' => [],
            'number' => $this->get('security.authorization_checker')->isGranted('ROLE_CASH_NUMBER'),
            'dm' => $this->dm
        ]);

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($cashDocument);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.cashController.edit_record_success'));

                return $this->afterSaveRedirect('cash', $cashDocument->getId());
            }
        } else {
            if (!$cashDocument->getId() && !$cashDocument->getNumber()) {
                $collection = $this->dm->getFilterCollection();
                $collection->disable('softdeleteable');
                $inc = $this->dm->getRepository('MBHCashBundle:CashDocument')
                    ->createQueryBuilder()->field('order')->exists(false)
                    ->getQuery()->count() + 1;

                $cashDocument->setDocumentDate(new \DateTime());
                $cashDocument->setPaidDate(new \DateTime());

                $cashDocument->setNumber($inc);
                $collection->enable('softdeleteable');
                $form->setData($cashDocument);
            }
        }

        return [
            'cashDocument' => $cashDocument,
            'form' => $form->createView()
        ];
    }


    /**
     * @Route("/{id}/edit", name="cash_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_CASH_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHCashBundle:CashDocument")
     */
    public function editAction(CashDocument $cashDocument, Request $request)
    {
        $this->dm->getFilterCollection()->disable('softdeleteable');
        if ($cashDocument->getHotel() && !$this->container->get('mbh.hotel.selector')->checkPermissions($cashDocument->getHotel())
            || ($cashDocument->getIsConfirmed() && !$this->get('security.authorization_checker')->isGranted('ROLE_CASH_CONFIRM'))
        ) {
            throw $this->createAccessDeniedException();
        }
        $this->dm->getFilterCollection()->enable('softdeleteable');

        //$cashDocumentRepository = $this->dm->getRepository('MBHCashBundle:CashDocument');

        $formType = $cashDocument->getOrder() ? CashDocumentType::class : NewCashDocumentType::class;

        $options = [
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            'number' => $this->get('security.authorization_checker')->isGranted('ROLE_CASH_NUMBER'),
            'dm' => $this->dm
        ];
        if($cashDocument->getOrder()) {
            $cashDocumentRepository = $this->dm->getRepository(CashDocument::class);
            $options['payers'] = $cashDocumentRepository->getAvailablePayersByOrder($cashDocument->getOrder());
        }
        $form = $this->createForm($formType, $cashDocument, $options);

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($cashDocument);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.cashController.edit_record_success'));

                return $this->afterSaveRedirect('cash', $cashDocument->getId());
            }
        }

        return [
            'entity' => $cashDocument,
            'form' => $form->createView(),
            'logs' => $this->logs($cashDocument)
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="cash_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_CASH_DELETE')")
     * @ParamConverter("entity", class="MBHCashBundle:CashDocument")
     */
    public function deleteAction(CashDocument $entity)
    {
        if ($entity->getIsConfirmed() && !$this->get('security.authorization_checker')->isGranted('ROLE_CASH_CONFIRM')) {
            throw $this->createAccessDeniedException();
        }

        $this->dm->getFilterCollection()->disable('softdeleteable');

        return $this->deleteEntity($entity->getId(), 'MBHCashBundle:CashDocument', 'cash');
    }

    /**
     * Confirm entity.
     *
     * @Route("/{id}/confirm", name="cash_confirm", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_CASH_CONFIRM')")
     */
    public function confirmAction($id)
    {
        $this->dm->getFilterCollection()->disable('softdeleteable');
        $entity = $this->dm->getRepository('MBHCashBundle:CashDocument')->find($id);

        if (!$entity || !$entity->getIsPaid() || ($entity->getHotel() && !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel()))) {
            return new JsonResponse([
                'error' => true,
                'message' => 'CashDocument not found'
            ]);
        }
        $this->dm->getFilterCollection()->enable('softdeleteable');

        $entity->setIsConfirmed(true);
        $entity->setResponsible($this->getUser());
        $this->dm->persist($entity);
        $this->dm->flush();

        $responsible = $entity->getResponsible() ? $entity->getResponsible()->getName() : null;

        return new JsonResponse([
            'error' => false,
            'message' => $this->get('translator')->trans('controller.cashController.payment_confirmed_success'),
            'responsible' => $responsible
        ]);
    }

    /**
     * Get money from order card.
     *
     * @Route("/{id}/card/money", name="cash_card_money")
     * @Method("GET")
     * @Security("is_granted('ROLE_CASH_EDIT')")
     * @ParamConverter("entity", class="MBHCashBundle:CashDocument")
     */
    public function getMoneyFromCardAction(CashDocument $entity)
    {
        $this->dm->getFilterCollection()->disable('softdeleteable');
        $order = $entity->getOrder();
        $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        if ($entity->getHotel() && !$this->get('mbh.hotel.selector')->checkPermissions($entity->getHotel()) || !$order->getCreditCard()
            || !$clientConfig || $clientConfig->getPaymentSystem() != 'uniteller'
        ) {
            throw $this->createNotFoundException();
        }

        /** @var Uniteller $uniteller */
        $uniteller = $clientConfig->getPaymentSystemDoc();

        try {

            $client = new Client();
            $client->post(Uniteller::DO_CHECK_URL, ['form_params' => $uniteller->getCheckPaymentData($entity)]);

        } catch (Exception $e) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * Pay entity.
     *
     * @Route("/{id}/pay", name="cash_pay", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_CASH_EDIT')")
     */
    public function payAction($id, Request $request)
    {
        $this->dm->getFilterCollection()->disable('softdeleteable');
        $entity = $this->dm->getRepository('MBHCashBundle:CashDocument')->find($id);

        if (!$entity || $entity->getHotel() && !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            return new JsonResponse([
                'error' => true,
                'message' => 'CashDocument not found'
            ]);
        }
        $this->dm->getFilterCollection()->enable('softdeleteable');

        $paidDate = \DateTime::createFromFormat('d.m.Y', $request->get('paidDate'));
        if (!$paidDate) {
            $paidDate = new \DateTime();
        }

        $entity->setPaidDate($paidDate);
        $entity->setIsPaid(true);

        $violationList = $this->get('validator')->validate($entity);
        if ($violationList->count() > 0) {
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

    /**
    * @Route("/payers", name="get_payers", options={"expose"=true})
    * @Method("GET")
    * @Security("is_granted('ROLE_CASH_EDIT')")
    */
    public function getPayersAction(Request $request)
    {
        $query = $request->get('query');
        $payers = [];

        if($query) {
            $payers = $this->get('mbh.package.payer_repository')->search($query);
        }

        return new JsonResponse(['results' => $payers, 'success' => true]);
    }
}
