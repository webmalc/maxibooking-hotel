<?php

namespace MBH\Bundle\CashBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\ClientBundle\Document\Uniteller;
use MBH\Bundle\CashBundle\Document\CashDocumentRepository;
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
use Symfony\Component\HttpFoundation\Response;

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
            array_slice($methods, 2, count($methods) - 1, true);

        return [
            'methods' => $methods,
            'users' => $this->dm->getRepository('MBHUserBundle:User')->findBy(['enabled' => true], ['username' => 'asc']),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
        ];
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
        $queryCriteria->begin = $this->get('mbh.helper')->getDateFromString($request->get('begin'));
        $queryCriteria->end = $this->get('mbh.helper')->getDateFromString($request->get('end'));

        if (!$queryCriteria->begin) {
            $queryCriteria->begin = new \DateTime('midnight -7 days');
        }

        if (!$queryCriteria->end) {
            $queryCriteria->end = new \DateTime('midnight +1 day');
        }

        empty($request->get('filter')) ? $queryCriteria->filterByRange = 'paidDate' : $queryCriteria->filterByRange = $request->get('filter');

        $queryCriteria->orderIds = $this->get('mbh.helper')->toIds($this->get('mbh.package.permissions')->getAvailableOrders());

        $queryCriteria->deleted = $request->get('deleted');

        $queryCriteria->createdBy = $request->get('user');

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

        if ($getFirstSort = $clientDataTableParams->getFirstSort()) {
            $queryCriteria->sortBy = [$getFirstSort[0]];
            $queryCriteria->sortDirection = [$getFirstSort[1]];
        }

        $queryCriteria->search = $clientDataTableParams->getSearch();
    }

    /**
     * @Route("/export/1c", name="cash_1c_export", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     * @param Request $request
     * @return Response
     */
    public function export1cAction(Request $request)
    {
        $queryCriteria = $this->requestToCashCriteria($request);
        $queryCriteria->limit = 1000;

        /** @var CashDocumentRepository $cashDocumentRepository */
        $cashDocumentRepository = $this->dm->getRepository('MBHCashBundle:CashDocument');
        $cashDocuments = $cashDocumentRepository->findByCriteria($queryCriteria);

        $text = '';
        foreach($cashDocuments as $cashDocument) {
            $organizationPayer = $cashDocument->getOrganizationPayer() ? $cashDocument->getOrganizationPayer() : new Organization();
            $hotelOrganization = $cashDocument->getHotel()->getOrganization();

            $text .= sprintf('СекцияДокумент=Платежное поручение
Номер='.$cashDocument->getNumber().'
Дата='.$cashDocument->getCreatedAt()->format('d.m.Y').'
Сумма='.$cashDocument->getTotal().'
ПлательщикСчет='.$organizationPayer->getCheckingAccount().'
ДатаСписано='.($cashDocument->getIsPaid() ? $cashDocument->getPaidDate()->format('d.m.Y') : '').'
Плательщик='.$cashDocument->getPayer()->getName(). //ЗАПАДНО-УРАЛЬСКИЙ БАНК ОАО "СБЕРБАНК РОССИИ"//ЗЫРЯНОВА ЕЛЕНА СЕРГЕЕВНА//26859356266//614000 ПЕРМЬ МЕХАНОШИНА д.10 кв.44//
'
ПлательщикИНН='.$organizationPayer->getInn().'
ПлательщикКПП='.$organizationPayer->getKpp().'
ПлательщикРасчСчет='.$organizationPayer->getCheckingAccount().'
ПлательщикБанк1='.$organizationPayer->getBank().'
ПлательщикБИК='.$organizationPayer->getBankBik().'
ПлательщикКорсчет='.$organizationPayer->getCorrespondentAccount().'
ПолучательСчет='.$hotelOrganization->getCorrespondentAccount().'
ДатаПоступило='.$cashDocument->getPaidDate()->format('d.m.Y').'
Получатель='.$hotelOrganization->getName().'
ПолучательИНН='.$hotelOrganization->getInn().'
ПолучательКПП='.$hotelOrganization->getKpp().'
ПолучательРасчСчет='.$hotelOrganization->getCheckingAccount().'
ПолучательБанк1='.$hotelOrganization->getBank().'
ПолучательБИК='.$hotelOrganization->getBankBik().'
ПолучательКорсчет='.$hotelOrganization->getCorrespondentAccount().'
ВидПлатежа='.$cashDocument->getMethod().'
ВидОплаты=01
Код=
СтатусСоставителя=
ПоказательКБК=
ОКАТО=
ПоказательОснования=
ПоказательПериода=
ПоказательНомера=
ПоказательДаты=
ПоказательТипа=
Очередность=5
НазначениеПлатежа=ЗА 15/07/2014; ФИО: ЗЫРЯНОВА ЕЛЕНА СЕРГЕЕВНА; АДРЕС: Г ПЕРМЬ УЛ МЕХАНОШИНА Д 10 КВ 44; ДОП_ИНФ: ЗА ТУРИСТИЧЕСКУЮ ПУТЕВКУ ПО ДОГОВОРУ N 2-16696 ОТ 14.07.2014; КОНТАКТ: 89197101886;
КонецДокумента
');
        }

        $responseContent = sprintf(
            '1CClientBankExchange
ВерсияФормата=1.02
Кодировка=Windows
Отправитель=
Получатель=
ДатаСоздания='.date('d.m.Y').'
ВремяСоздания='.date('H.i.s').'
ДатаНачала='.$queryCriteria->begin->format('d.m.Y').'
ДатаКонца='.$queryCriteria->end->format('d.m.Y').'
РасчСчет=40702810938250018461
СекцияРасчСчет
ДатаНачала=16.07.2014
ДатаКонца=16.07.2014
НачальныйОстаток=216692.84
РасчСчет=40702810938250018461
ВсегоСписано=215711
ВсегоПоступило=190574
КонечныйОстаток=191555.84
КонецРасчСчет
'.$text.'КонецФайла
');

        $response = new Response($responseContent);
        $response->headers->set('Content-Type','text/plain');
        return $response;
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
        $this->dm->getFilterCollection()->disable('softdeleteable');
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $this->dm->getFilterCollection()->enable('softdeleteable');

        $cashDocumentRepository = $this->dm->getRepository('MBHCashBundle:CashDocument');

        $form = $this->createForm(new CashDocumentType($this->dm), $entity, [
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            'payers' => $cashDocumentRepository->getAvailablePayersByOrder($entity->getOrder()),
        ]);

        if ($request->isMethod("PUT")) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.cashController.edit_record_success'));

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
        $this->dm->getFilterCollection()->disable('softdeleteable');
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

        if (!$entity || !$entity->getIsPaid() || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            return new JsonResponse([
                'error' => true,
                'message' => 'CashDocument not found'
            ]);
        }
        $this->dm->getFilterCollection()->enable('softdeleteable');

        $entity->setIsConfirmed(true);
        $this->dm->persist($entity);
        $this->dm->flush();

        return new JsonResponse([
            'error' => false,
            'message' => $this->get('translator')->trans('controller.cashController.payment_confirmed_success')
        ]);
    }

    /**
     * Get money from order card.
     *
     * @Route("/{id}/card/money", name="cash_card_money")
     * @Method("GET")
     * @Security("is_granted(['ROLE_MANAGER', 'ROLE_BOOKKEEPER'])")
     * @ParamConverter("entity", class="MBHCashBundle:CashDocument")
     */
    public function getMoneyFromCardAction(CashDocument $entity)
    {
        $this->dm->getFilterCollection()->disable('softdeleteable');
        $order = $entity->getOrder();
        $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        if (!$this->get('mbh.hotel.selector')->checkPermissions($entity->getHotel()) || !$order->getCreditCard()
            || !$clientConfig || $clientConfig->getPaymentSystem() != 'uniteller'
        ) {
            throw $this->createNotFoundException();
        }

        /** @var Uniteller $uniteller */
        $uniteller = $clientConfig->getPaymentSystemDoc();

        try {
            $request = $this->get('guzzle.client')
                ->post(Uniteller::DO_CHECK_URL)
                ->addPostFields($uniteller->getCheckPaymentData($entity))
                ->send()
            ;

            dump((string) $request);

        } catch (Exception $e) {
            throw $this->createNotFoundException();
        }
        exit();
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

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            return new JsonResponse([
                'error' => true,
                'message' => 'CashDocument not found'
            ]);
        }
        $this->dm->getFilterCollection()->enable('softdeleteable');

        $paidDate = \DateTime::createFromFormat('d.m.Y', $request->get('paidDate'));
        if (!$paidDate)
            $paidDate = new \DateTime();

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
}
