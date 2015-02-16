<?php

namespace MBH\Bundle\CashBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\CashBundle\Form\CashDocumentType;

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
        return [];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="cash_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     * @Template()
     */
    public function jsonAction(Request $request)
    {

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $repo = $dm->getRepository('MBHCashBundle:CashDocument');
        $qb = $repo->createQueryBuilder('CashDocument')
            ->skip($request->get('start'))
            ->limit($request->get('length'));
        //Order
        $sort = 'createdAt';
        $dir = 'desc';
        $order = $request->get('order')['0'];
        if (!empty($order['column']) && in_array($order['column'], [2, 3, 6])) {
            ($order['column'] == 2 || $order['column'] == 3) ? $sort = 'total' : $sort = 'createdAt';
            $dir = $order['dir'];
        }
        $qb->sort($sort, $dir);


        //Search
        $search = $request->get('search')['value'];
        if (!empty($search)) {
            $qb->addOr($qb->expr()->field('total')->equals((int)$search));
            $qb->addOr($qb->expr()->field('prefix')->equals(new \MongoRegex('/.*' . $search . '.*/ui')));
        }

        $begin = $end = null;
        //Dates
        if (!empty($request->get('begin'))) {
            $begin = \DateTime::createFromFormat('d.m.Y H:i:s', $request->get('begin') . ' 00:00:00');
            $qb->field('createdAt')->gte($begin);
        }

        if (!empty($request->get('end'))) {
            $end = \DateTime::createFromFormat('d.m.Y H:i:s', $request->get('end') . ' 00:00:00');
            $qb->field('createdAt')->lte($end);
        }

        $orders = $this->container->get('mbh.package.permissions')->getAvailableOrders();
        $qb->field('order.id')->in($this->container->get('mbh.helper')->toIds($orders));

        $entities = $qb->getQuery()->execute();

        return [
            'entities' => $entities,
            'totalIn' => ($entities->count()) ? $repo->total('in', $search, $begin, $end) : 0,
            'totalOut' => ($entities->count()) ? $repo->total('out', $search, $begin, $end) : 0,
            'total' => $entities->count(),
            'draw' => $request->get('draw'),
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations')
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="cash_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHCashBundle:CashDocument')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new CashDocumentType(),
            $entity,
            [
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations'),
                'payer' => $entity->getPayer()
            ]
        );

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="cash_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     * @Template("MBHCashBundle:Cash:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHCashBundle:CashDocument')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new CashDocumentType(),
            $entity,
            [
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations'),
                'payer' => $entity->getPayer()
            ]
        );

        $form->bind($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();

            $payer = $dm->getRepository('MBHPackageBundle:Tourist')->find($form['payer_select']->getData());
            if ($payer) {
                $entity->setPayer($payer);
            } else {
                $entity->removePayer();
            }

            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', 'Запись успешно отредактирована.');

            return $this->afterSaveRedirect('cash', $entity->getId());
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
     * Delete entity.
     *
     * @Route("/{id}/confirm", name="cash_confirm", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKKEEPER')")
     */
    public function confirmAction(Request $request, $id)
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
        $entity->setIsConfirmed(true);
        $dm->persist($entity);
        $dm->flush();

        return new JsonResponse([
            'error' => false,
            'message' => 'Платеж успешно подтвержден'
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
