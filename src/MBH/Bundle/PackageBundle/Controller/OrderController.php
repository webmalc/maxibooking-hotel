<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\DeletableControllerInterface;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Form\CashDocumentType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Form\OrderDeleteReasonType;
use MBH\Bundle\PackageBundle\Form\OrderTouristType;
use MBH\Bundle\PackageBundle\Form\OrderType;
use MBH\Bundle\PackageBundle\Form\OrganizationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', entity) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @return array|Response
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
            ->createQueryBuilder()
            ->field('order.id')->equals($entity->getId())
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute();

        //Defaults
        if (!$request->isMethod(Request::METHOD_POST)) {
            $cash
                ->setOperation('in')
                ->setMethod('cash')
                ->setDocumentDate(new \DateTime('now'))
                ->setIsPaid(true)
                ->setPaidDate(new \DateTime('now'))
                ->setOrder($entity)
                ->setNumber($cashDocumentRepository->generateNewNumber($cash));
        }

        $form = $this->createForm(CashDocumentType::class, $cash, [
            'groupName' => $this->get('translator')->trans('controller.orderController.add_cash_register_paper'),
            'payer' => $entity->getMainTourist() ? $entity->getMainTourist()->getId() : null,
            'payers' => $cashDocumentRepository->getAvailablePayersByOrder($entity),
            'number' => $this->get('security.authorization_checker')->isGranted('ROLE_CASH_NUMBER'),
        ]);

        if ($request->isMethod(Request::METHOD_POST)  &&
            $this->container->get('security.authorization_checker')->isGranted('ROLE_ORDER_CASH_DOCUMENTS') && (
                $this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
                $this->container->get('security.authorization_checker')->isGranted('EDIT', $entity)
            )
        ) {
            $form->handleRequest($request);
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
            'clientConfig' => $this->clientConfig
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

        $form = $this->createForm(OrderTouristType::class, ['addToPackage' => true]);


        return [
            'order' => $order,
            'logs' => $this->logs($order),
            'documentTypes' => $this->container->get('mbh.fms_dictionaries')->getDocumentTypes(),
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

        $form = $this->createForm(OrganizationType::class, null, [
            'isFull' => false,
            'typeList' => $this->container->getParameter('mbh.organization.types'),
            'dm' => $this->dm
        ]);

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'package' => $package
        ];
    }

    /**
     * Order tourist update
     *
     * @Route("/{id}/tourist/update/{packageId}", name="package_order_tourist_update")
     * @Method("POST")
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

        $form = $this->createForm(OrderTouristType::class);

        $form->handleRequest($request);

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
     * @Method("POST")
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

        $form = $this->createForm(OrganizationType::class,
            $existOrganization, [
                'isFull' => false,
                'typeList' => $this->container->getParameter('mbh.organization.types'),
                'dm' => $this->dm
            ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Organization $organization */
            $organization = $form->getData();
            $organization->setType('contragents');
            $entity->setOrganization($organization);
            $this->dm->persist($organization);
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success',
                $this->get('translator')->trans('controller.orderController.payer_saved_success'));

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
     * @Route("/{id}/edit/{packageId}", name="package_order_edit", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', entity) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @param Order $entity
     * @param Package $package
     * @return array
     * @Template()
     */
    public function editAction(Order $entity, Package $package)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(OrderType::class, $entity);

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'online_payments' => $this->container->getParameter('mbh.online.form')['payment_types'],
            'form' => $form->createView(),
            'package' => $package
        ];
    }

    /**
     * Order update
     *
     * @Route("/{id}/edit/{packageId}", name="package_order_update")
     * @Method("POST")
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

        $form = $this->createForm(OrderType::class, $entity);
        $form->handleRequest($request);

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

        $response = $this->deleteEntity($entity->getId(), 'MBHPackageBundle:Order', 'package');
        return $response;
    }

    /**
     * Order_delete_modal
     *
     * @param Request $request
     * @param Order $entity
     *
     * @Route("/{id}/modal/order_delete_modal", name="order_delete", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_DELETE') and (is_granted('DELETE', entity) or is_granted('ROLE_PACKAGE_DELETE_ALL'))")
     * @Template("@MBHPackage/Package/deleteModalContent.html.twig")
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteOrderModalAction(Order $entity, Request $request)
    {
        $previousComment = $entity->getNote();
        $entity->setNote('');

        $form = $this->createForm(OrderDeleteReasonType::class, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $permissions = $this->container->get('mbh.package.permissions');

            if (!$permissions->checkHotel($entity)) {
                throw $this->createNotFoundException();
            }

            $entity->setNote($entity->getNote() . "\n" . $previousComment);
            $this->deleteEntity($entity->getId(), 'MBHPackageBundle:Order', 'package');

            return new Response('', 302);
        }

        return [
            'entity' => $entity,
            'controllerName' => 'order_delete',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{packageId}/confirm/{id}", name="package_order_confirm", defaults={"confirmed": false})
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or is_granted('ROLE_NO_OWN_ONLINE_VIEW') or (is_granted('EDIT', order) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("order", options={"mapping": {"id": "id", "confirmed": "confirmed"}})
     * @ParamConverter("package", options={"id" = "packageId"})
     * @param Order $order
     * @param Package $package
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function confirmAction(Order $order, Package $package)
    {
        $user = $this->getUser();
        $order->setConfirmed(true);
        $this->dm->flush();

        /** SetOwner && ACL */
        $om = $this->get('mbh.acl_document_owner_maker');
        $om->assignOwnerToDocument($user, $order);
        $om->assignOwnerToDocument($user, $package);

        return $this->redirectToRoute('package_order_edit', ['id' => $order->getId(), 'packageId' => $package->getId()]);
    }
}
