<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PdfGenerator
 * @package MBH\Bundle\BaseBundle\Service
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PdfGenerator implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

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
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    private function getDefaultPath()
    {
        return $this->container->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'protectedUpload' . DIRECTORY_SEPARATOR . 'generateDocuments';
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
     * @param $nameFile without extension
     * @param $documentType
     * @param array $params
     * @return int
     */
    public function save($nameFile, $documentType, $params = [])
    {
        $path = $this->path ? $this->path : $this->getDefaultPath();
        $resource = $this->output($documentType, $params);

        return file_put_contents($path . DIRECTORY_SEPARATOR . $nameFile . '.pdf', $resource);
    }
}