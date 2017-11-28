<?php

namespace MBH\Bundle\ClientBundle\Controller;

use Liip\ImagineBundle\Templating\ImagineExtension;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\ClientBundle\Form\DocumentTemplateType;
use MBH\Bundle\ClientBundle\Service\TemplateFormatter;
use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Twig\Extension\UploaderExtension;

/**
 * Class DocumentTemplateController
 *

 * @Route("/templates")
 */
class DocumentTemplateController extends BaseController
{
    /**
     * @Route("/", name="document_templates")
     * @Method("GET")
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATES_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm
            ->getRepository('MBHClientBundle:DocumentTemplate')
            ->findBy([], ['title' => 'asc']);

        return [
            'entities' => $entities,
        ];
    }

    /**
     * @Route("/new", name="document_templates_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATES_NEW')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new DocumentTemplate();
        $form = $this->createForm(DocumentTemplateType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', $this->container->get('translator')->trans('clientbundle.controller.documentTemplateController.entry_successfully_created'));
            return $this->afterSaveRedirect('document_templates', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/edit/{id}", name="document_templates_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATES_EDIT')")
     * @Template()
     * @ParamConverter(class="\MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     */
    public function editAction(DocumentTemplate $entity, Request $request)
    {
        $form = $this->createForm(DocumentTemplateType::class, $entity);

        if($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                return $this->afterSaveRedirect('document_templates', $entity->getId());
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/preview/{id}", name="document_templates_preview")
     * @Method("GET")
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATES_VIEW')")
     * @ParamConverter(class="\MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     * @return Response
     * @deprecated
     */
    public function previewAction(DocumentTemplate $documentTemplate)
    {
        $entity = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy([]);
        $html = $this->get('mbh.template_formatter')->prepareHtml($documentTemplate, $entity);
        $content = $this->get('knp_snappy.pdf')->getOutputFromHtml($html);
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * @Route("/show/{id}/order/{packageId}", name="document_templates_show")
     * @Method("GET")
     * @Security("is_granted('ROLE_DOCUMENTS_GENERATOR')")
     * @ParamConverter(class="MBHClientBundle:DocumentTemplate")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @return Response
     * @param DocumentTemplate $doc
     * @param Package $package
     */
    public function showAction(DocumentTemplate $doc, Package $package)
    {
        $content = $this->get('mbh.template_formatter')->generateDocumentTemplate($doc, $package, $this->getUser());

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="document_templates_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATES_DELETE')")
     * @Template()
     * @ParamConverter(class="\MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     */
    public function deleteAction(DocumentTemplate $entity)
    {
        return $this->deleteEntity($entity->getId(), '\MBH\Bundle\ClientBundle\Document\DocumentTemplate', 'document_templates');
    }
}