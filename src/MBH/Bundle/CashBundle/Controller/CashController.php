<?php

namespace MBH\Bundle\CashBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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

        $entities = $qb->getQuery()->execute();

        return [
            'entities' => $entities,
            'totalIn' => $repo->total('in', $search, $begin, $end),
            'totalOut' => $repo->total('out', $search, $begin, $end),
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

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new CashDocumentType(),
            $entity,
            [
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations')
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

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new CashDocumentType(),
            $entity,
            [
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations')
            ]
        );

        $form->bind($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
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

}
