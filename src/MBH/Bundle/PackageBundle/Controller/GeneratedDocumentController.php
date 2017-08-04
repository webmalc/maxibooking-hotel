<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\DocumentGenerator\Xls\XlsGeneratorFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/document/generated")
 * @Method("GET")
 *

 */
class GeneratedDocumentController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Return generated doc
     *
     * @Route("/{id}/{type}", name="package_pdf", requirements={
     *      "type" : "confirmation|confirmation_en|registration_card|fms_form_5|evidence|form_1_g|receipt|act|xls_notice|bill"
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_DOCUMENTS_GENERATOR')")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function actPdfAction(Package $entity, $type, Request $request)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $generatorFactory = $this->get('mbh.package.document_factory');
        if(!in_array($type, $generatorFactory->getAvailableTypes())) {
            throw $this->createNotFoundException();
        }

        $formData = [];
        if($request->isMethod(Request::METHOD_POST) && $generatorFactory->hasForm($type)) {
            $options = [];
            if($type == XlsGeneratorFactory::TYPE_NOTICE) {
                $options['tourists'] = $this->dm->getRepository('MBHPackageBundle:Tourist')->getForeignTouristsByPackage($entity);
            }
            $form = $generatorFactory->createFormByType($type, $options);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
            } else {
                throw $this->createNotFoundException();
            }
        }
        $formData['package'] = $entity;

        $documentGenerator = $generatorFactory->createGeneratorByType($type);
        $response = $documentGenerator->generateResponse($formData);

        return $response;
    }

    /**
     * @Route("/{id}/modal_form/{type}", name="document_modal_form", options={"expose"=true})
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @Security("is_granted('ROLE_DOCUMENTS_GENERATOR')")
     */
    public function documentModalFormAction(Package $entity, $type)
    {
        $generatorFactory = $this->get('mbh.package.document_factory');
        if(!$generatorFactory->hasForm($type)) {
            throw $this->createNotFoundException();
        }

        $options = [];
        $error = null;
        if($type == XlsGeneratorFactory::TYPE_NOTICE) {
            $options['tourists'] = $this->dm->getRepository('MBHPackageBundle:Tourist')->getForeignTouristsByPackage($entity);
            if(empty($options['tourists'])) {
                $error = 'В данной брони нет иностранных граждан';
            }
        }

        if($error) {
            $html = $this->renderView('MBHPackageBundle:Documents:documentModalError.html.twig', [
                'error' => $error
            ]);
        } else {
            $form = $generatorFactory->createFormByType($type, $options);
            if($type == XlsGeneratorFactory::TYPE_NOTICE) {
                $defaultUser = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['defaultNoticeDoc' => true]);

                $form->setData(['user' => $defaultUser ? $defaultUser : $this->getUser()]);
            }
            $html = $this->renderView('MBHPackageBundle:Documents:documentModalForm.html.twig', [
                'form' => $form->createView(),
                'type' => $type,
                'entity' => $entity
            ]);
        }

        return new JsonResponse([
            'html' => $html,
            'error' => $error,
            'name' => $this->get('translator')->trans('templateDocument.type.'. $type)
        ]);
    }

    /**
     * @Route("/stamp/{id}.jpg", name="stamp")
     * @Method("GET")
     * @Security("is_granted('ROLE_DOCUMENTS_GENERATOR')")
     * @return Response
     * @ParamConverter(class="\MBH\Bundle\PackageBundle\Document\Organization")
     */
    public function stampAction(Organization $entity)
    {
        /** @var string|null $client */
        $client = $this->container->get('kernel')->getClient();

        if (!$entity->getStamp($client)) {
            throw $this->createNotFoundException();
        }

        $fp = fopen($entity->getStamp($client)->getPathname(), "rb");
        $str = stream_get_contents($fp);
        fclose($fp);

        /*$binary = $this->get('liip_imagine.data.manager')->find('stamp',
            '/orderDocuments/5554599b7d3d6494118b4567'//$entity->getStamp()->getPathname()
        );
        $str = $binary->getContent();*/

        $response = new Response($str, 200);
        $response->headers->set('Content-Type', $entity->getStamp($client)->getMimeType());

        return $response;
    }
}