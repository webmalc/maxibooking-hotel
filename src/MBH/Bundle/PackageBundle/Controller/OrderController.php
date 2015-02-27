<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Form\CashDocumentType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Form\OrderTouristType;
use MBH\Bundle\PackageBundle\Form\OrderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use MBH\Bundle\BaseBundle\Controller\DeletableControllerInterface;

/**
 * @Route("/order")
 */
class OrderController extends Controller implements CheckHotelControllerInterface, DeletableControllerInterface
{
    /**
     * Cash document delete
     *
     * @Route("/{id}/cash/{cash}/delete", name="package_order_cash_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param Order $entity
     * @param CashDocument $cash
     * @param Request $request
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("cash", class="MBHCashBundle:CashDocument", options={"id" = "cash"})
     * @return Response
     */
    public function cashDeleteAction(Request $request, Order $entity, CashDocument $cash)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $dm->remove($cash);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Кассовый документ успешно удален.');

        return $this->redirect($this->generateUrl('package_order_cash', ['id' => $entity->getId()]));
    }

    /**
     * Order cash list
     *
     * @Route("/{id}/cash", name="package_order_cash")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param Order $entity
     * @return Response
     * @Template()
     */
    public function cashAction(Order $entity)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $cash = new CashDocument();
        $cash->setOrder($entity);

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $docs = $dm->getRepository('MBHCashBundle:CashDocument')
            ->createQueryBuilder('q')
            ->field('order.id')->equals($entity->getId())
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute()
        ;

        $form = $this->createForm(
            new CashDocumentType(),
            $cash,
            [
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations'),
                'groupName' => 'Добавить кассовый документ',
                'payer' => $entity->getMainTourist()
            ]
        );

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'docs' => $docs,
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
        ];
    }

    /**
     * Order cash list
     *
     * @Route("/{id}/cash/save", name="package_order_cash_save")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Order:cash.html.twig")
     * @param Order $entity
     * @param Request $request
     * @return Response
     */
    public function cashSaveAction(Order $entity, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $docs = $dm->getRepository('MBHCashBundle:CashDocument')
            ->createQueryBuilder('q')
            ->field('order.id')->equals($entity->getId())
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute()
        ;

        $cash = new CashDocument();
        $cash->setOrder($entity);

        $form = $this->createForm(
            new CashDocumentType(),
            $cash,
            [
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations'),
                'groupName' => 'Добавить кассовый документ',
                'payer' => $entity->getMainTourist()
            ]
        );

        $form->submit($request);

        if ($form->isValid()) {
            $payer = $dm->getRepository('MBHPackageBundle:Tourist')->find($form['payer_select']->getData());
            if ($payer) {
                $cash->setPayer($payer);
            }

            $dm->persist($cash);
            $dm->flush();

            $request->getSession()
                ->getFlashBag()
                ->set('success', 'Кассовый документ успешно добавлен.');

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('package_order_cash', ['id' => $entity->getId()]));
            }

            return $this->redirect($this->generateUrl('package'));
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'docs' => $docs,
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
        ];
    }

    /**
     * Order packages list
     *
     * @Route("/{id}/packages", name="package_order_packages")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param Order $entity
     * @return Response
     * @Template()
     */
    public function packagesAction(Order $entity)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $packages = $dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder('q')
            ->field('order.id')->equals($entity->getId())
            ->sort('number', 'desc')
            ->getQuery()
            ->execute()
        ;

        return [
            'entity' => $entity,
            'packages' => $packages,
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Order tourist edit
     *
     * @Route("/{id}/tourist/edit", name="package_order_tourist_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param Order $entity
     * @return Response
     * @Template()
     */
    public function touristEditAction(Order $entity)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderTouristType());

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'genders' => $this->container->getParameter('mbh.gender.types'),
            'form' => $form->createView()
        ];
    }

    /**
     * Order tourist update
     *
     * @Route("/{id}/tourist/update", name="package_order_tourist_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Order:touristEdit.html.twig")
     * @param Order $entity
     * @param Request $request
     * @return Response
     */
    public function touristUpdateAction(Order $entity, Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderTouristType());

        $form->submit($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $tourist = $dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                $data['lastName'], $data['firstName'], $data['patronymic'], $data['birthday'], $data['email'], $data['phone']
            );
            $entity->setMainTourist($tourist);
            $dm->persist($entity);
            $dm->flush();

            $request->getSession()
                ->getFlashBag()
                ->set('success', 'Плательщик успешно добавлен.');

            //return $this->afterSaveRedirect('package', $entity->getId());

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('package_order_tourist_edit', ['id' => $entity->getId()]));
            }

            return $this->redirect($this->generateUrl('package'));
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'form' => $form->createView()
        ];
    }

    /**
     * Order tourist delete
     *
     * @Route("/{id}/tourist/delete", name="package_order_tourist_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param Order $entity
     * @param Request $request
     * @return Response
     */
    public function touristDeleteAction(Order $entity, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity->setMainTourist(null);
        $dm->persist($entity);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Плательщик успешно удален.');

        return $this->redirect($this->generateUrl('package_order_tourist_edit', ['id' => $entity->getId()]));
    }

    /**
     * Order edit
     *
     * @Route("/{id}/edit", name="package_order_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param Order $entity
     * @param Request $request
     * @return Response
     * @Template()
     */
    public function editAction(Order $entity, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderType(), $entity);

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'form' => $form->createView()
        ];
    }

    /**
     * Order update
     *
     * @Route("/{id}/update", name="package_order_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Order:edit.html.twig")
     * @param Order $entity
     * @param Request $request
     * @return Response
     */
    public function updateAction(Order $entity, Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderType(), $entity);

        $form->submit($request);

        if ($form->isValid()) {
            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Запись успешно отредактирована.');

            //return $this->afterSaveRedirect('package', $entity->getId());

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('package_order_edit', ['id' => $entity->getId()]));
            }

            return $this->redirect($this->generateUrl('package'));
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'form' => $form->createView()
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="package_order_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param Order $entity
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Order $entity, Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }
        $dm->remove($entity);
        $dm->flush($entity);

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Запись успешно удалена.');

        return $this->redirect($this->generateUrl('package'));

        return $response;
    }
}
