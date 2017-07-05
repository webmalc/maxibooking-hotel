<?php

namespace MBH\Bundle\BaseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MBHBaseExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->getParameter('client')) {
            $bundles = $container->getParameter('kernel.bundles');
            if (isset($bundles['VichUploaderBundle'])) {
                $config = ['mappings' => ['upload_image' => ['upload_destination' => $container->getParameter('kernel.root_dir').'/../web/clients/zalex/upload/images']]];
                foreach ($container->getExtensions() as $name => $extension) {
                    switch ($name) {
                        case 'vich_uploader':
                            $container->prependExtensionConfig($name, $config);
                            break;
                    }
                }
            }
        }

//        $configs = $container->getExtensionConfig($this->getAlias());
//        $config = $this->processConfiguration(new Configuration(), $configs);
//        $container->prependExtensionConfig('vich_uploader', $config);

        

    }


}
