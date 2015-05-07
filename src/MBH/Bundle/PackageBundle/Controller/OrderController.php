<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Form\CashDocumentType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Form\OrderTouristType;
use MBH\Bundle\PackageBundle\Form\OrderType;
use MBH\Bundle\PackageBundle\Form\OrganizationType;
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
     * @Route("/{id}/cash/{cash}/delete/{packageId}", name="package_order_cash_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param Order $entity
     * @param CashDocument $cash
     * @param Request $request
     * @param Package $package
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("cash", class="MBHCashBundle:CashDocument", options={"id" = "cash"})
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @return Response
     */
    public function cashDeleteAction(Request $request, Order $entity, CashDocument $cash, Package $package)
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
            ->set('success', $this->get('translator')->trans('controller.orderController.cash_register_paper_deleted_success'));

        return $this->redirect($this->generateUrl('package_order_cash', ['id' => $entity->getId(), 'packageId' => $package->getId()]));
    }

    /**
     * Order cash list
     *
     * @Route("/{id}/cash/{packageId}", name="package_order_cash")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @return Response
     * @Template()
     */
    public function cashAction(Order $entity, Package $package)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
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
                'groupName' => $this->get('translator')->trans('controller.orderController.add_cash_register_paper'),
                'payer' => $entity->getMainTourist(),
            ]
        );

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'docs' => $docs,
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            'package' => $package,
            'clientConfig' => $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()
        ];
    }

    /**
     * Order cash list
     *
     * @Route("/{id}/cash/save/{packageId}", name="package_order_cash_save")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @Template("MBHPackageBundle:Order:cash.html.twig")
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function cashSaveAction(Order $entity, Package $package, Request $request)
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
                ->set('success', $this->get('translator')->trans('controller.orderController.cash_register_paper_added_success'));

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('package_order_cash', ['id' => $entity->getId(), 'packageId' => $package->getId()]));
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
            'package' => $package,
            'clientConfig' => $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()
        ];
    }

    /**
     * Order tourist edit
     *
     * @Route("/{id}/tourist/edit/{packageId}", name="package_order_tourist_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @return Response
     * @Template()
     */
    public function touristEditAction(Order $entity, Package $package)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderTouristType());

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'genders' => $this->container->getParameter('mbh.gender.types'),
            'form' => $form->createView(),
            'package' => $package
        ];
    }


    /**
     * Order tourist edit
     *
     * @Route("/{id}/organization/edit/{packageId}", name="package_order_organization_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @return Response
     * @Template()
     */
    public function organizationEditAction(Order $entity, Package $package)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrganizationType($this->dm), null, [
            'scenario' => OrganizationType::SCENARIO_SHORT,
            'typeList' => $this->container->getParameter('mbh.organization.types'),
        ]);

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'genders' => $this->container->getParameter('mbh.gender.types'),
            'form' => $form->createView(),
            'package' => $package
        ];
    }

    /**
     * Order tourist update
     *
     * @Route("/{id}/tourist/update/{packageId}", name="package_order_tourist_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Order:touristEdit.html.twig")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function touristUpdateAction(Order $entity, Package $package, Request $request)
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
                ->set('success', $this->get('translator')->trans('controller.orderController.payer_added_success'));

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('package_order_tourist_edit', ['id' => $entity->getId(), 'packageId' => $package->getId()]));
            }

            return $this->redirect($this->generateUrl('package'));
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'form' => $form->createView(),
            'package' => $package
        ];
    }

    /**
     * Order tourist update
     *
     * @Route("/{id}/organization/update/{packageId}", name="package_order_organization_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Order:organizationEdit.html.twig")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function organizationUpdateAction(Order $entity, Package $package, Request $request)
    {
        /*if($request->isMethod("GET"))
            return $this->redirect()
        */
        $permissions = $this->container->get('mbh.package.permissions');
        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $organization = $request->get('organization');
        $existOrganization = null;
        if($organization['inn'])
            $existOrganization = $this->dm->getRepository('MBHPackageBundle:Organization')->findOneByInn($organization['inn']);

        $form = $this->createForm(new OrganizationType($this->dm), $existOrganization ? $existOrganization : new Organization(), [
            'scenario' => OrganizationType::SCENARIO_SHORT,
            'typeList' => $this->container->getParameter('mbh.organization.types'),
        ]);

        $form->submit($request);

        if ($form->isValid()) {
            /** @var Organization $organization */
            $organization = $form->getData();
            $organization->setType('contragents');
            $entity->setOrganization($organization);
            $this->dm->persist($organization);
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()
                ->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.orderController.organization_added_success'));
        }

        //return $this->redirect($this->generateUrl('package_order_tourist_edit', ['id' => $entity->getId(), 'packageId' => $package->getId()]));
        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'form' => $form->createView(),
            'package' => $package
        ];
    }

    /**
     * Order tourist delete
     *
     * @Route("/{id}/tourist/delete/{packageId}", name="package_order_tourist_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function touristDeleteAction(Order $entity, Package $package, Request $request)
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
            ->set('success', $this->get('translator')->trans('controller.orderController.payer_deleted_success'));

        return $this->redirect($this->generateUrl('package_order_tourist_edit', ['id' => $entity->getId(), 'packageId' => $package->getId()]));
    }

    /**
     * Order tourist delete
     *
     * @Route("/{id}/organization/delete/{packageId}", name="package_order_organization_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function organizationDeleteAction(Order $entity, Package $package, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->check($entity) || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity->setOrganization(null);
        $dm->persist($entity);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.orderController.payer_organization_success'));

        return $this->redirect($this->generateUrl('package_order_organization_edit', ['id' => $entity->getId(), 'packageId' => $package->getId()]));
    }

    /**
     * Order edit
     *
     * @Route("/{id}/edit/{packageId}", name="package_order_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @return Response
     * @Template()
     */
    public function editAction(Order $entity, Package $package)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderType(), $entity);

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'form' => $form->createView(),
            'package' => $package
        ];
    }

    /**
     * Order update
     *
     * @Route("/{id}/update/{packageId}", name="package_order_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Order:edit.html.twig")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function updateAction(Order $entity, Package $package, Request $request)
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
                ->set('success', $this->get('translator')->trans('controller.orderController.record_edited_success'));

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('package_order_edit', ['id' => $entity->getId(), 'packageId' => $package->getId()]));
            }

            return $this->redirect($this->generateUrl('package'));
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'form' => $form->createView(),
            'package' => $package
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
            ->set('success', $this->get('translator')->trans('controller.orderController.record_deleted_success'));

        return $this->redirect($this->generateUrl('package'));

        return $response;
    }
}
