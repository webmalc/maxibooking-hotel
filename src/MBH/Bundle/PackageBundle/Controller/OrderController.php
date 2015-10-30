<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Form\CashDocumentType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
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
     * @Security("is_granted('ROLE_ORDER_CASH_DOCUMENTS') and (is_granted('EDIT', entity) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
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
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $this->dm->remove($cash);
        $this->dm->flush();

        $request->getSession()->getFlashBag()->set('success',
            $this->get('translator')->trans('controller.orderController.cash_register_paper_deleted_success'));

        return $this->redirect($this->generateUrl('package_order_cash',
            ['id' => $entity->getId(), 'packageId' => $package->getId()]));
    }

    /**
     * Order cash list
     *
     * @Route("/{id}/cash/{packageId}", name="package_order_cash")
     * @Method({"GET","PUT"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', entity) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @return Response
     * @Template()
     * @throws \Exception
     */
    public function cashAction(Order $entity, Package $package, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');
        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $cash = new CashDocument();
        $cash->setOrder($entity);

        $cashDocumentRepository = $this->dm->getRepository('MBHCashBundle:CashDocument');
        /** @var CashDocument[] $docs */
        $docs = $cashDocumentRepository
            ->createQueryBuilder('q')
            ->field('order.id')->equals($entity->getId())
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute();

        //Defaults
        if (!$request->isMethod(Request::METHOD_PUT)) {
            $cash
                ->setOperation('in')
                ->setMethod('cash')
                ->setDocumentDate(new \DateTime('now'))
                ->setIsPaid(true)
                ->setPaidDate(new \DateTime('now'))
                ->setOrder($entity)
                ->setNumber($cashDocumentRepository->generateNewNumber($cash));
        }

        $form = $this->createForm(new CashDocumentType($this->dm), $cash, [
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            'groupName' => $this->get('translator')->trans('controller.orderController.add_cash_register_paper'),
            'payer' => $entity->getMainTourist() ? $entity->getMainTourist()->getId() : null,
            'payers' => $cashDocumentRepository->getAvailablePayersByOrder($entity),
            'number' => $this->get('security.authorization_checker')->isGranted('ROLE_CASH_NUMBER')
        ]);

        if ($request->isMethod(Request::METHOD_PUT)  &&
            $this->container->get('security.authorization_checker')->isGranted('ROLE_ORDER_CASH_DOCUMENTS') && (
                $this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
                $this->container->get('security.authorization_checker')->isGranted('EDIT', $entity)
            )
        ) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->dm->persist($cash);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.orderController.cash_register_paper_added_success'));

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('package_order_cash', ['id' => $entity->getId(), 'packageId' => $package->getId()]) :
                    $this->redirectToRoute('package');
            }
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'docs' => $docs,
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            'package' => $package,
            'clientConfig' => $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()
        ];
    }

    /**
     * Order tourist edit
     *
     * @Route("/{id}/tourist/edit/{packageId}", name="package_order_tourist_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', order) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $order
     * @param Package $package
     * @return Response
     * @Template()
     */
    public function touristEditAction(Order $order, Package $package)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($order)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderTouristType(), ['addToPackage' => true]);


        return [
            'order' => $order,
            'logs' => $this->logs($order),
            'vegaDocumentTypes' => $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes(),
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
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', entity) and is_granted('ROLE_PACKAGE_VIEW'))")
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
            'isFull' => false,
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
     * @@Security("is_granted('ROLE_ORDER_PAYER') and (is_granted('EDIT', $order) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @Template("MBHPackageBundle:Order:touristEdit.html.twig")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $order
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function touristUpdateAction(Order $order, Package $package, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($order)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderTouristType());

        $form->submit($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                $data['lastName'], $data['firstName'], $data['patronymic'], $data['birthday'], $data['email'],
                $data['phone']
            );
            $order->setMainTourist($tourist);

            if ($data['addToPackage'] &&
                $this->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_GUESTS')
                && ($this->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
                    $this->get('security.authorization_checker')->isGranted('EDIT', $package))
            ) {
                $package->addTourist($tourist);
                $this->dm->persist($package);
            }

            $this->dm->persist($order);
            $this->dm->flush();

            $flashBag = $request->getSession()->getFlashBag();
            $flashBag->set('success', $this->get('translator')->trans('controller.orderController.payer_added_success'));
            if($tourist->getIsUnwelcome()) {
                $flashBag->set('warning', '<i class="fa fa-user-secret"></i> '.$this->get('translator')
                        ->trans('package.payer_in_unwelcome'));
            }

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('package_order_tourist_edit',
                    ['id' => $order->getId(), 'packageId' => $package->getId()]);
            }

            return $this->redirectToRoute('package');
        }

        return [
            'entity' => $order,
            'logs' => $this->logs($order),
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
     * @Security("is_granted('ROLE_ORDER_PAYER') and (is_granted('EDIT', entity) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @Template("MBHPackageBundle:Order:organizationEdit.html.twig")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function organizationUpdateAction(Order $entity, Package $package, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');
        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $organization = $request->get('organization');
        $existOrganization = null;
        if ($organization['inn']) {
            $existOrganization = $this->dm->getRepository('MBHPackageBundle:Organization')->findOneByInn($organization['inn']);
        }
        if (!$existOrganization) {
            $existOrganization = new Organization();
            $existOrganization->setType('contragents');
        }

        $form = $this->createForm(new OrganizationType($this->dm),
            $existOrganization, [
                'isFull' => false,
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

            $request->getSession()->getFlashBag()->set('success',
                $this->get('translator')->trans('controller.orderController.organization_added_success'));

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('package_order_organization_edit',
                    ['id' => $entity->getId(), 'packageId' => $package->getId()]);
            }

            return $this->redirectToRoute('package',
                ['id' => $entity->getId(), 'packageId' => $package->getId()]);
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
     * Order tourist delete
     *
     * @Route("/{id}/tourist/delete/{packageId}", name="package_order_tourist_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ORDER_PAYER') and (is_granted('EDIT', entity) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function touristDeleteAction(Order $entity, Package $package, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $entity->setMainTourist(null);
        $this->dm->persist($entity);
        $this->dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.orderController.payer_deleted_success'));

        return $this->redirect($this->generateUrl('package_order_tourist_edit',
            ['id' => $entity->getId(), 'packageId' => $package->getId()]));
    }

    /**
     * Order tourist delete
     *
     * @Route("/{id}/organization/delete/{packageId}", name="package_order_organization_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ORDER_PAYER') and (is_granted('EDIT', entity) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function organizationDeleteAction(Order $entity, Package $package, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $entity->setOrganization(null);
        $this->dm->persist($entity);
        $this->dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.orderController.payer_organization_success'));

        return $this->redirect($this->generateUrl('package_order_organization_edit',
            ['id' => $entity->getId(), 'packageId' => $package->getId()]));
    }

    /**
     * Order edit
     *
     * @Route("/{id}/edit/{packageId}", name="package_order_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', entity) and is_granted('ROLE_PACKAGE_VIEW'))")
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
     * @Security("(is_granted('ROLE_PACKAGE_EDIT_ALL') and is_granted('ROLE_ORDER_EDIT')) or (is_granted('ROLE_ORDER_EDIT') and is_granted('EDIT', entity))")
     * @Template("MBHPackageBundle:Order:edit.html.twig")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @param Request $request
     * @return Response
     */
    public function updateAction(Order $entity, Package $package, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.orderController.record_edited_success'));

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('package_order_edit',
                    ['id' => $entity->getId(), 'packageId' => $package->getId()]));
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
     * @Security("is_granted('ROLE_PACKAGE_DELETE') and (is_granted('DELETE', entity) or is_granted('ROLE_PACKAGE_DELETE_ALL'))")
     * @param Order $entity
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Order $entity, Request $request)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }
        $this->dm->remove($entity);
        $this->dm->flush($entity);

        $request->getSession()->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.orderController.record_deleted_success'));

        return $this->redirect($this->generateUrl('package'));
    }
}
