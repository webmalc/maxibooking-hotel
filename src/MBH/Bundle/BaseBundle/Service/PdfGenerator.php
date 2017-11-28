<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PdfGenerator
 * @package MBH\Bundle\BaseBundle\Service
 */
class PdfGenerator implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @var string
     */
    private $path;

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
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    private function getDefaultPath()
    {
        /** @var \AppKernel $kernel */
        $kernel = $this->container->get('kernel');
        $client = $kernel->getClient();

        return $kernel->getRootDir().'/../protectedUpload'.($kernel->getClient() ? '/clients/' . $client: '').'/generateDocuments';
    }

    /**
     * @param string $documentType Template name
     * @param array $params
     * @return string resource
     */
    public function output($documentType, $params = [])
    {
        /** @var \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator $d */
        $generator = $this->container->get('knp_snappy.pdf');
        $html = $this->container->get('twig')->render('MBHBaseBundle:PdfTemplates:' . $documentType . '.html.twig',
            $params);
        $resource = $generator->getOutputFromHtml($html);

        return $resource;
    }

    /**
     * @param string $nameFile without extension
     * @param string $documentType
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function save($nameFile, $documentType, $params = [])
    {
        $path = $this->path ? $this->path : $this->getDefaultPath();

        if (!file_exists($path) || !is_writable($path)) {
            throw new Exception($path . ' not exist or not writable.');
        }
        $resource = $this->output($documentType, $params);

        $filePath = $path . DIRECTORY_SEPARATOR . $nameFile . '.pdf';
        if (!file_put_contents($filePath, $resource)) {
            throw new Exception($filePath . ' not saved.');
        }

        return true;
    }
}