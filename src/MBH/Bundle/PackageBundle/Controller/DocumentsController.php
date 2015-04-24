<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageDocument;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Document\TouristRepository;
use MBH\Bundle\PackageBundle\Form\PackageDocumentType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/")
 * @Method("GET")
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class DocumentsController extends Controller
{
    /**
     * Edits an existing entity.
     *
     * @Route("/{id}/documents", name="package_documents")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();

        /** @var PackageRepository $packageRepository */
        $packageRepository = $dm->getRepository('MBHPackageBundle:Package');
        /** @var Package $entity */
        $entity = $packageRepository->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $packageDocument = new PackageDocument();
        $documentTypes = [];
        foreach ($this->container->getParameter('mbh.package.document.types') as $type)
            $documentTypes[$type] = $this->get('translator')->trans('package.document.type_' . $type, [], 'MBHPackageBundle');

        /** @var TouristRepository $touristRepository */
        //$touristRepository = $dm->getRepository('MBHPackageBundle:Tourist');

        $touristIds = $this->get('mbh.helper')->toIds($entity->getTourists());


        if($mainTourist = $entity->getOrder()->getMainTourist()){
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

                $entity->addDocument($packageDocument);
                $dm->persist($entity);
                $dm->flush();
                return $this->redirect($this->generateUrl("package_documents", ['id' => $id]));
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
        ];
    }

    /**
     *
     * @Route("/{id}/removeDocument", name="package_remove_document", options={"expose"=true})
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function removeAction(Request $request, $id)
    {
        $filename = $request->get('filename');

        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();

        /** @var Package $entity */
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        foreach ($entity->getDocuments() as $document) {
            /** @var PackageDocument $document */
            if ($document->getName() == $filename) {
                $entity->removeDocument($document);

                $dm->persist($entity);
                $dm->flush();

                return new JsonResponse([
                    'success' => true
                ]);
            }
        }

        return new JsonResponse([
            'success' => false,
            'error' => 'File is not found. Filename is ' . $filename
        ]);
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
    public function imageAction($docname, $download = 0)
    {
        //@todo getPackageDocumentByName()

        $packageDocument = new PackageDocument();
        $packageDocument->setName($docname);
        $pathImage = $packageDocument->getPath();

        if(!is_file($pathImage))
            throw $this->createNotFoundException();

        //$this->get('assetic.asset_manager')->get

        $fp = fopen($pathImage, "rb");
        $str = stream_get_contents($fp);
        fclose($fp);

        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        //$dm = $this->get('doctrine_mongodb')->getManager();
        /** @var \Doctrine\ODM\MongoDB\Cursor $data */
        /*$data = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder()->field('documents.name')->equals($docname)->getQuery()->execute();
        $data = iterator_to_array($data->getMongoCursor());

        if(!$data)
            throw $this->createNotFoundException();

        $data = reset($data);
*/
        $headers = [];
        if($download) {
            $headers['Content-Disposition'] = 'attachment; filename="'.$docname.'"';
            $headers['Content-Length:'] = filesize($pathImage);
        }

        $response = new Response($str, 200, $headers);

        return $response;
    }
}