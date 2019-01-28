<?php

namespace MBH\Bundle\ClientBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\ClientBundle\Form\DocumentTemplateType;
use MBH\Bundle\PackageBundle\Document\Package;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $entity = new DocumentTemplate();
        $form = $this->createForm(DocumentTemplateType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'clientbundle.controller.documentTemplateController.entry_successfully_created');
            return $this->afterSaveRedirect('document_templates', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/tinymce_codemirror", name="document_templates_tinymce_codemirror")
     * @return Response
     */
    public function tinymceCodemirrorAction()
    {
        return $this->render('@MBHClient/DocumentTemplate/Tinymce/source.html.twig');
    }

    /**
     * @Route("/edit/{id}", name="document_templates_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATES_EDIT')")
     * @Template()
     * @ParamConverter(class="\MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     * @param DocumentTemplate $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(DocumentTemplate $entity, Request $request)
    {
        $form = $this->createForm(DocumentTemplateType::class, $entity);

        if($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();
                $this->addFlash('success', 'clientbundle.controller.documentTemplateController.entry_successfully_eited');

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
     * @param DocumentTemplate $documentTemplate
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
        // for interceptor notices in prod
        $this->container->get('twig')->enableStrictVariables();

        try {
            $content = $this->get('mbh.template_formatter')->generateDocumentTemplate($doc, $package, $this->getUser());

            return new Response($content, 200, [
                'Content-Type' => 'application/pdf'
            ]);
        } catch (\Twig_Error $twigError) {
            $msg = $this->get('translator')->trans(
                'clientbundle.controller.documentTemplateController.error_generate_pdf.template',
                [
                    '%error_msg%' => $twigError->getRawMessage()
                ]
            );

        } catch (\Exception $exception) {
            $msg = $this->get('translator')->trans(
                'clientbundle.controller.documentTemplateController.error_generate_pdf.other',
                [
                    '%error_msg%' => $exception->getMessage()
                ]
            );
        }

        return $this->render('@MBHClient/DocumentTemplate/error.html.twig', ['msg' => $msg]);
    }

    /**
     * @Route("/delete/{id}", name="document_templates_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATES_DELETE')")
     * @ParamConverter(class="\MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     * @param DocumentTemplate $entity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(DocumentTemplate $entity)
    {
        return $this->deleteEntity($entity->getId(), '\MBH\Bundle\ClientBundle\Document\DocumentTemplate', 'document_templates');
    }
}