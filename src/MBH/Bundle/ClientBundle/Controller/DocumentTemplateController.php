<?php

namespace MBH\Bundle\ClientBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\ClientBundle\Form\DocumentTemplateType;
use MBH\Bundle\ClientBundle\Service\TemplateFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


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
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATE_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHClientBundle:DocumentTemplate')->findAll();

        return [
            'entities' => $entities,
        ];
    }

    /**
     * @Route("/new", name="document_templates_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATE_NEW')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new DocumentTemplate();
        $form = $this->createForm(new DocumentTemplateType(), $entity);

        if($request->isMethod(Request::METHOD_POST)) {
            $form->submit($request);
            if($form->isValid()) {
                $entity->setHotel($this->hotel);
                $this->dm->persist($entity);
                $this->dm->flush();

                return $this->afterSaveRedirect('document_templates', $entity->getId());
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/edit/{id}", name="document_templates_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATE_EDIT')")
     * @Template()
     * @ParamConverter(class="\MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     */
    public function editAction(DocumentTemplate $entity, Request $request)
    {
        $form = $this->createForm(new DocumentTemplateType(), $entity);

        if($request->isMethod(Request::METHOD_POST)) {
            $form->submit($request);
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
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATE_VIEW')")
     * @ParamConverter(class="\MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     * @return Response
     */
    public function previewAction(DocumentTemplate $documentTemplate)
    {
        $entity = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy([]);
        $html = (new TemplateFormatter())->prepareHtml($documentTemplate, $entity);
        $content = $this->get('knp_snappy.pdf')->getOutputFromHtml($html);
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="document_templates_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_DOCUMENT_TEMPLATE_DELETE')")
     * @Template()
     * @ParamConverter(class="\MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     */
    public function deleteAction(DocumentTemplate $entity)
    {
        return $this->deleteEntity($entity->getId(), '\MBH\Bundle\ClientBundle\Document\DocumentTemplate', 'document_templates');
    }
}