<?php

namespace MBH\Bundle\CashBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Form\SearchType;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
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
    public function indexAction(Request $request)
    {
        /*$form = $this->createFormBuilder()->add('begin', 'date', [
            'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'datepicker end-datepiker form-control input-sm', 'id' => 'begin'],
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy',
        ])->getForm();*/

        return [
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            //'form' => $form->createView()
        ];
    }

    /**
     *
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

        $start = $request->get('start');
        $length = $request->get('length');
        $order = $request->get('order')['0'];
        $search = $request->get('search')['value'];
        $methods = $request->get('methods');
        $showNoPaid = $request->get('show_no_paid');
        $begin = $this->get('mbh.helper')->getDateFromString($request->get('begin'));
        $end = $this->get('mbh.helper')->getDateFromString($request->get('end'));
        $filter = $request->get('filter');
        $orderIds = $this->get('mbh.helper')->toIds($this->get('mbh.package.permissions')->getAvailableOrders());


        $sort = 'createdAt';
        $dir = 'desc';
        if (!empty($order['column']) && in_array($order['column'], [2, 3, 5, 6, 7])) {
            $sorts = [2 => 'total', 3 => 'total', 5 => 'createdAt', 6 => 'isPaid', 7 => 'deletedAt'];
            $sort = $sorts[$order['column']];
            $dir = $order['dir'];
        }


        $totalIn = 0;
        $totalOut = 0;
        $recordsTotal = 0;
        $recordsFiltered = 0;

        if ($request->get('by_day')) {
            $result = $repository->getListForCash($start, $length, $sort, $dir, $methods, $search, $showNoPaid, $begin, $end,
                $filter, $orderIds, true);

            //$recordsTotal = $qb->getQuery()->count();
            $totalIn = $repository->total('in', $search, $begin, $end);
            $totalOut = $repository->total('out', $search, $begin, $end);

            return $this->render('MBHCashBundle:Cash:jsonByDay.json.twig', [
                "draw" => $request->get('draw'),
                'totalIn' => $totalIn,
                'totalOut' => $totalOut,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $result
            ]);
        } else {
            $entities = $repository->getListForCash($start, $length, $sort, $dir, $methods, $search, $showNoPaid, $begin,
                $end, $filter, $orderIds, false);

            if(count($entities) > 0){
                $totalIn = $repository->total('in', $search, $begin, $end);
                $totalOut = $repository->total('out', $search, $begin, $end);
            }

            return [
                'draw' => $request->get('draw'),
                'totalIn' => $totalIn,
                'totalOut' => $totalOut,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'entities' => $entities,
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
                //'payer' => $entity->getPayer() ? $entity->getPayer()->getId() : null,
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
     * @Security("is_granted('ROLE_ADMIN')")
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
