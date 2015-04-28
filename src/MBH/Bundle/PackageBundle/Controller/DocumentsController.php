<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageDocument;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Form\PackageDocumentType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/")
 * @Method("GET")
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class DocumentsController extends Controller
{
    /**
     * @param Request $request
     * @param Package $package
     * @return array|RedirectResponse
     *
     * @Route("/{id}/documents", name="package_documents")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("order", class="MBHPackageBundle:Package")
     * @Template()
     */
    public function indexAction(Request $request, Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package))
            throw $this->createNotFoundException();

        $packageDocument = new PackageDocument();
        $documentTypes = [];
        foreach ($this->container->getParameter('mbh.package.document.types') as $type)
            $documentTypes[$type] = $this->get('translator')->trans('package.document.type_' . $type, [], 'MBHPackageBundle');

        $touristIds = $this->get('mbh.helper')->toIds($package->getTourists());

        if($mainTourist = $package->getOrder()->getMainTourist()){
            $touristIds[] = $mainTourist->getId();
        }

        $form = $this->createForm(new PackageDocumentType(), $packageDocument, [
            'documentTypes' => $documentTypes,
            'touristIds' => $touristIds
        ]);

        if ($request->isMethod("PUT")) {
            $form->submit($request);

            if ($form->isValid()) {
                $packageDocument->upload();

                /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
                $dm = $this->get('doctrine_mongodb')->getManager();

                $package->addDocument($packageDocument);
                $dm->persist($package);
                $dm->flush();
                return $this->redirect($this->generateUrl("package_documents", ['id' => $package->getId()]));
            }
        }

        return [
            'entity' => $package,
            'form' => $form->createView(),
            'logs' => $this->logs($package),
        ];
    }

    /**
     * @param Package $package
     * @param $docname
     * @return RedirectResponse
     *
     * @Route("/{id}/removeDocument/{docname}", name="package_remove_document", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     */
    public function removeAction(Package $package, $docname)
    {
        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        foreach ($package->getDocuments() as $document) {
            /** @var PackageDocument $document */
            if ($document->getName() == $docname) {
                $package->removeDocument($document);

                $dm->persist($package);
                $dm->flush();

                break;
            }
        }


        return new RedirectResponse($this->generateUrl('package_documents', ['id' => $package->getId()]));
    }


    /**
     *
     * @Route("/document/{docname}/{download}", name="package_document_view", options={"expose"=true}, defaults={"download" = 0})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param $docname
     * @param $download
     * @return Response
     */
    public function viewAction($docname, $download = 0)
    {
        //todo $repository->getDocumentByName
        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();
        /** @var PackageRepository $packageRepository */
        $packageRepository = $dm->getRepository('MBHPackageBundle:Package');

        /** @var Package $package */
        $package = $packageRepository->findOneBy(['documents.name' => $docname]);

        if(!$package)
            throw $this->createNotFoundException();

        $document = null;

        foreach($package->getDocuments()->getIterator() as $d)
            /** @var PackageDocument $d */
            if($d->getName() == $docname)
                $document = $d;

        if(!$document)
            throw $this->createNotFoundException();

        $fp = fopen($document->getPath(), "rb");
        $str = stream_get_contents($fp);
        fclose($fp);

        $headers = [];
        $headers['Content-Type'] = $document->getMimeType();

        if($download) {
            $headers['Content-Disposition'] = 'attachment; filename="'.$document->getOriginalName().'"';
            $headers['Content-Length'] = filesize($document->getPath());
        }

        $response = new Response($str, 200, $headers);

        return $response;
    }

    /**
     * @Route("/document/{id}/edit/{docname}", name="package_document_edit", options={"expose"=true}, defaults={"download" = 0})
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     * @Template()
     */
    public function editAction(Package $package, $docname, Request $request)
    {
        $touristIds = $this->get('mbh.helper')->toIds($package->getTourists());

        $packageDocument = null;
        foreach($package->getDocuments()->getIterator() as $document)
            /** @var PackageDocument $document */
            if($document->getName() == $docname)
                $packageDocument = $document;

        if(!$packageDocument)
            throw $this->createNotFoundException();

        $documentTypes = [];
        foreach ($this->container->getParameter('mbh.package.document.types') as $type)
            $documentTypes[$type] = $this->get('translator')->trans('package.document.type_' . $type, [], 'MBHPackageBundle');

        if($mainTourist = $package->getOrder()->getMainTourist()){
            $touristIds[] = $mainTourist->getId();
        }

        $form = $this->createForm(new PackageDocumentType(), $packageDocument, [
            'documentTypes' => $documentTypes,
            'touristIds' => $touristIds,
            'scenario' => PackageDocumentType::SCENARIO_EDIT,
            'document' => $document
        ]);

        if ($request->isMethod("PUT")) {
            $oldPackageDocument = clone($packageDocument);
            $form->submit($request);

            if ($form->isValid()) {
                if(!$packageDocument->isUploaded()){
                    $packageDocument->upload();
                    $oldPackageDocument->deleteFile();
                }
                /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
                $dm = $this->get('doctrine_mongodb')->getManager();

                $dm->persist($packageDocument);
                $dm->flush();


                return $this->redirect($this->generateUrl("package_documents", ['id' => $package->getId()]));
            }
        }

        return [
            'entity' => $package,
            'document' => $packageDocument,
            'form' => $form->createView(),
            'logs' => $this->logs($package),
        ];
    }
}