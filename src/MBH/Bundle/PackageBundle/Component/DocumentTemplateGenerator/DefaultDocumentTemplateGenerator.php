<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DefaultDocumentTemplateGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DefaultDocumentTemplateGenerator extends AbstractDocumentTemplateGenerator implements ContainerAwareInterface
{
    /**
     * @var Package
     */
    protected $package;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $formParams;

    public function setPackage(Package $package)
    {
        $this->package = $package;
    }

    /**
     * @param $formParams
     */
    public function setFormParams(array $formParams)
    {
        $this->formParams = $formParams;
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
    protected function getAdditionalParams()
    {
        $vegaDocumentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $vegaDocumentTypes = array_map(['\MBH\Bundle\VegaBundle\Service\FriendlyFormatter', 'convertDocumentType'], $vegaDocumentTypes);

        return [
            'vegaDocumentTypes' => $vegaDocumentTypes,
        ];
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        $params = [
            'entity' => $this->package,
            'formParams' => $this->formParams,
        ] + $this->getAdditionalParams();

        $html = $this->container->get('templating')->render($this->getTemplateName(), $params);

        return $html;
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