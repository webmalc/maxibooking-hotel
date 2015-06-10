<?php

namespace MBH\Bundle\VegaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MBHVegaExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $documentTypes = Yaml::parse(__DIR__.'/../Resources/config/docum.yml')['docum']; //@todo move to service Provider
        $container->setParameter('mbh.vega.document.types', $documentTypes);

        $scanTypes = Yaml::parse(__DIR__.'/../Resources/config/tpscan.yml')['tpscan'];
        $container->setParameter('mbh.vega.document.scan.types', $scanTypes);

        $houseParts = Yaml::parse(__DIR__.'/../Resources/config/housepart.yml')['housepart'];
        $container->setParameter('mbh.vega.house_parts', $houseParts);
    }
}
