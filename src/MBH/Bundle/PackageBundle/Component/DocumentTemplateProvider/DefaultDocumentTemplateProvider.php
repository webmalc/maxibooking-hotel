<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateProvider;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultDocumentTemplateProvider extends AbstractDocumentTemplateProvider implements ContainerAwareInterface
{
    /**
     * @var Package
     */
    protected $package;
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setPackage(Package $package)
    {
        $this->package = $package;
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


    protected function getAdditionalParams()
    {
        $vegaDocumentTypes = $this->container->getParameter('mbh.vega.document.types');

        return [
            'vegaDocumentTypes' => $vegaDocumentTypes,
        ];
    }

    /**
     * @param array $formParams
     * @return string
     */
    public function getTemplate(array $formParams = [])
    {
        $params = [
            'entity' => $this->package,
            'params' => $formParams,
        ] + $this->getAdditionalParams();

        $html = $this->container->get('templating')->render($this->getTemplateName(), $params);

        return $html;
    }

    protected function getTemplateName()
    {
        return 'MBHPackageBundle:Documents/pdfTemplates:'.$this->type.'.html.twig';
    }
}