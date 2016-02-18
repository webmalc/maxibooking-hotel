<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\DeletableControllerInterface;
use MBH\Bundle\PackageBundle\Document\OrderRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Form\OrderDocumentType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("/")
 * @Method("GET")
 *

 */
class DocumentsController extends Controller implements CheckHotelControllerInterface, DeletableControllerInterface
{
    /**
     * @param Request $request
     * @param Order $entity
     * @param Package $package
     * @return array|RedirectResponse
     * @Route("/{id}/documents/{packageId}", name="order_documents")
     * @Method({"GET", "PUT"})
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
        $vagaDocumentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $docTypes = $orderDocumentTypes + $vagaDocumentTypes;

        $groupDocTypes = ['' => $orderDocumentTypes, 'Vega' => $vagaDocumentTypes];
        $scanTypes = $this->container->get('mbh.vega.dictionary_provider')->getScanTypes();

        $form = $this->createForm(new OrderDocumentType(), $orderDocument, [
            'documentTypes' => $groupDocTypes,
            'scanTypes' => $scanTypes,
            'touristIds' => $touristIds
        ]);

        if ($request->isMethod("PUT")) {
            $form->submit($request);

            if ($form->isValid()) {
                $orderDocument->upload();
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
     * @Route("/document/{name}/view/{download}", name="order_document_view", options={"expose"=true}, defaults={"download" = 0})
     * @Method("GET")
     * @param $name
     * @param $download
     * @return Response
     */
    public function viewAction($name, $download = 0)
    {
        /** @var OrderRepository $packageRepository */
        $orderRepository = $this->dm->getRepository('MBHPackageBundle:Order');
        /** @var Order $order */
        $order = $orderRepository->findOneBy(['documents.name' => $name]);

        if (!$order) {
            throw $this->createNotFoundException();
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_VIEW_ALL')
            && !($this->get('security.authorization_checker')->isGranted('VIEW', $order)
                && $this->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_VIEW'))) {
            throw $this->createAccessDeniedException();
        }

        $document = null;

        foreach ($order->getDocuments()->getIterator() as $d) {
            /** @var OrderDocument $d */
            if ($d->getName() == $name) {
                $document = $d;
            }
        }

        if (!$document) {
            throw $this->createNotFoundException();
        }

        $fp = fopen($document->getPath(), "rb");
        $str = stream_get_contents($fp);
        fclose($fp);

        $headers = [];
        $headers['Content-Type'] = $document->getMimeType();

        if ($download) {
            $headers['Content-Disposition'] = 'attachment; filename="'.$document->getOriginalName().'"';
            $headers['Content-Length'] = filesize($document->getPath());
        }

        $response = new Response($str, 200, $headers);

        return $response;
    }

    /**
     * @param Order $entity
     * @param Package $package
     * @param $name
     * @param Request $request
     * @Route("/document/{id}/edit/{packageId}/{name}", name="order_document_edit", options={"expose"=true}, defaults={"download" = 0})
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_ORDER_DOCUMENTS') and (is_granted('EDIT', order) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @Template()
     * @return RedirectResponse|null
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

        $form = $this->createForm(new OrderDocumentType(), $orderDocument, [
            'documentTypes' => $docTypes,
            'touristIds' => $touristIds,
            'scenario' => OrderDocumentType::SCENARIO_EDIT,
            'document' => $orderDocument
        ]);

        if ($request->isMethod("PUT")) {
            $oldOrderDocument = clone($orderDocument);
            $form->submit($request);

            if ($form->isValid()) {
                if (!$orderDocument->isUploaded()) {
                    $orderDocument->upload();
                    $oldOrderDocument->deleteFile();
                }
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