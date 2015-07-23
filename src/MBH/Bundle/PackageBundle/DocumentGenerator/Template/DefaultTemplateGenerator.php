<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultDocumentTemplateGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DefaultTemplateGenerator implements TemplateGeneratorInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getAdditionalParams($formData)
    {
        $vegaDocumentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $vegaDocumentTypes = array_map(['\MBH\Bundle\VegaBundle\Service\FriendlyFormatter', 'convertDocumentType'], $vegaDocumentTypes);

        return [
            'vegaDocumentTypes' => $vegaDocumentTypes,
        ];
    }

    /**
     * @param array $formData
     * @return string
     */
    public function getTemplate(array $formData)
    {
        $params = [
            'entity' => $formData['package'],
            'formParams' => $formData,
        ] + $this->getAdditionalParams($formData);

        $html = $this->container->get('templating')->render($this->getTemplateName(), $params);

        return $html;
    }

    /**
     * @return Response
     */
    public function generateResponse(array $formData)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $content = $this->container->get('knp_snappy.pdf')->getOutputFromHtml($this->getTemplate($formData), [
            'cookie' => [$request->getSession()->getName() => $request->getSession()->getId()],
            //'disable-smart-shrinking' => true,
        ]);
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => //'attachment;
            'filename="'.$this->type.'_'.$formData['package']->getNumberWithPrefix().'.pdf"'
        ]);
    }


    /**
     * @return string
     * @throws \Exception
     */
    protected function getTemplateName()
    {
        $templateName = 'MBHPackageBundle:Documents/pdfTemplates:'.$this->type.'.html.twig';
        if(!$this->container->get('templating')->exists($templateName)) {
            throw new \Exception();
        };

        return $templateName;
    }
}