<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\DeletableControllerInterface;
use MBH\Bundle\BaseBundle\Document\ProtectedFile;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use MBH\Bundle\PackageBundle\Document\OrderRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Form\OrderDocumentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 * @Method("GET")
 */
class DocumentsController extends Controller implements CheckHotelControllerInterface, DeletableControllerInterface
{
    /**
     * @param Request $request
     * @param Order $entity
     * @param Package $package
     * @return array|RedirectResponse
     * @Route("/{id}/documents/{packageId}", name="order_documents")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', entity) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @Template()
     */
    public function indexAction(Request $request, Order $entity, Package $package)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $orderDocument = new OrderDocument();
        $touristIds = $this->get('mbh.helper')->toIds($package->getTourists());

        if ($mainTourist = $package->getMainTourist()) {
            $touristIds[] = $mainTourist->getId();
        }

        $orderDocumentTypes = $this->container->getParameter('mbh.order.document.types');
        $documentTypes = $this->container->get('mbh.fms_dictionaries')->getDocumentTypes();
        $docTypes = $orderDocumentTypes + $documentTypes;

        $groupDocTypes = ['' => $orderDocumentTypes, 'Vega' => $documentTypes];
        $scanTypes = $this->container->get('mbh.vega.dictionary_provider')->getScanTypes();

        $form = $this->createForm(OrderDocumentType::class, $orderDocument, [
            'documentTypes' => $groupDocTypes,
            'scanTypes' => $scanTypes,
            'touristIds' => $touristIds
        ]);

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $package->getOrder()->addDocument($orderDocument);
                $this->dm->persist($package);
                $this->dm->flush();

                return $this->redirect($this->generateUrl("order_documents",
                    ['id' => $entity->getId(), 'packageId' => $package->getId()]));
            }
        }

        return [
            'package' => $package,
            'entity' => $entity,
            'docTypes' => $docTypes,
            'form' => $form->createView(),
            'logs' => $this->logs($package),
        ];
    }

    /**
     * @param Order $entity
     * @param Package $package
     * @param $name
     * @return RedirectResponse
     *
     * @Route("/document/{id}/{packageId}/{name}/remove", name="order_remove_document", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ORDER_DOCUMENTS') and (is_granted('EDIT', order) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     */
    public function removeAction(Order $entity, Package $package, $name)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $entity->removeDocumentByName($name);
        $this->dm->persist($entity);
        $this->dm->flush();

        return $this->redirect($this->generateUrl('order_documents',
            ['id' => $entity->getId(), 'packageId' => $package->getId()]));
    }


    /**
     *
     * @Route("/document/{order}/{protected}/view/{download}", name="order_document_view", options={"expose"=true}, defaults={"download" = 0})
     * @Method("GET")
     * @param Order $order
     * @param ProtectedFile $protected
     * @param int $download
     * @return Response|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function viewAction(Order $order, ProtectedFile $protected, $download = 0)
    {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_VIEW_ALL')
            && !($this->get('security.authorization_checker')->isGranted('VIEW', $order)
                && $this->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_VIEW'))) {
            throw $this->createAccessDeniedException();
        }
        $downloader = $this->get('mbh.protected.file.downloader');
        if ($download) {
            return $downloader->downloadProtectedFile($protected);
        }

        return $downloader->streamOutputFile($protected);
    }

    /**
     * @param Order $entity
     * @param Package $package
     * @param $name
     * @param Request $request
     * @Route("/document/{id}/edit/{packageId}/{name}", name="order_document_edit", options={"expose"=true}, defaults={"download" = 0})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ORDER_DOCUMENTS') and (is_granted('EDIT', order) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @Template()
     * @return array|null|RedirectResponse
     */
    public function editAction(Order $entity, Package $package, $name, Request $request)
    {
        $orderDocument = $entity->getDocument($name);
        $permissions = $this->container->get('mbh.package.permissions');
        
        if (!$orderDocument || !$permissions->checkHotel($entity) || $package->getDeletedAt()) {
            throw $this->createNotFoundException();
        }

        $touristIds = $this->get('mbh.helper')->toIds($package->getTourists());

        if ($mainTourist = $package->getMainTourist()) {
            $touristIds[] = $mainTourist->getId();
        }
        $docTypes = $this->container->getParameter('mbh.order.document.types');
        $scanTypes = $this->container->get('mbh.vega.dictionary_provider')->getScanTypes();

        $form = $this->createForm(OrderDocumentType::class, $orderDocument, [
            'documentTypes' => $docTypes,
            'touristIds' => $touristIds,
            'scenario' => OrderDocumentType::SCENARIO_EDIT,
            'document' => $orderDocument,
            'scanTypes' => $scanTypes
        ]);

        if ($request->isMethod("POST")) {
            $oldOrderDocument = clone($orderDocument);
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var string|null $client */
                $client = $this->container->get('kernel')->getClient();

//                if (!$orderDocument->isUploaded($client)) {
//                    $orderDocument->upload($client);
//                    $oldOrderDocument->deleteFile($client);
//                }
                $this->dm->persist($orderDocument);
                $this->dm->flush();

                return $this->redirect($this->generateUrl("order_documents", ['id' => $entity->getId(), 'packageId' => $package->getId()]));
            }
        }

        return [
            'entity' => $entity,
            'package' => $package,
            'document' => $orderDocument,
            'form' => $form->createView(),
            'logs' => $this->logs($package),
        ];
    }
}