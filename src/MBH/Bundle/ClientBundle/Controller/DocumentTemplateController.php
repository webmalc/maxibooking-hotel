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
        $html = (new TemplateFormatter())->prepareHtml($documentTemplate, $entity);
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
        $loader = new \Twig_Loader_Array(['template' => $doc->getContent()]);
        $env = new \Twig_Environment($loader);
        $env->addExtension($this->get('mbh.twig.extension'));
        $env->addExtension(new TranslationExtension($this->get('translator')));
        $env->addExtension(new AssetExtension($this->get('assets.packages')));
        $env->addExtension(new HttpFoundationExtension($this->get('request_stack')));
        $env->addExtension(new ImagineExtension($this->get('liip_imagine.cache.manager')));
        $env->addExtension(new UploaderExtension($this->get('vich_uploader.templating.helper.uploader_helper')));

        $order = $package->getOrder();
        $hotel = $doc->getHotel() ? $doc->getHotel() : $package->getRoomType()->getHotel();
        $organization = $doc->getOrganization() ? $doc->getOrganization() : $hotel->getOrganization();
        $params = [
            'package' => $package,
            'order' => $order,
            'hotel' => $hotel,
            'payer' => $order->getPayer(),
            'organization' => $organization,
            'user' => $this->getUser(),
            'arrivalTimeDefault' => $hotel->getPackageArrivalTime(),
            'departureTimeDefault' => $hotel->getPackageDepartureTime()
        ];

        $params = $this->addCalculatedParams($params, $package);
        $renderedTemplate = $env->render('template', $params);
        $content = $this->get('knp_snappy.pdf')->getOutputFromHtml($renderedTemplate);

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * @param $params
     * @param Package $package
     * @return array
     */
    private function addCalculatedParams(array $params, Package $package)
    {
        /** @var PackageService[] $packageServices */
        $packageServices = [];

        /** @var PackageServiceGroupByService[] $packageServicesByType */
        $packageServicesByType = [];

        $total = 0;
        $packages = $package->getOrder()->getPackages();

        /** @var Package $package */
        foreach($packages as $package) {
            $packageServices = array_merge(iterator_to_array($package->getServices()), $packageServices);
            $total += $package->getPackagePrice(true);
        }

        foreach($packageServices as $ps) {
            $service = $ps->getService();
            $groupBy = $ps->getPrice().$service->getId();
            if(!array_key_exists($groupBy, $packageServicesByType)) {
                $packageServicesByType[$groupBy] = new PackageServiceGroupByService($service, $ps->getPrice());
            }
            $packageServicesByType[$groupBy]->add($ps);
            $total += $ps->getTotal();
        }

        return $params + [
                'total' => $total,
                'packageServicesByType' => $packageServicesByType
            ];
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