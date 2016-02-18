<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Interface DocumentGeneratorFactoryInterface
 * AbstractFactory. Create DocumentResponseGeneratorInterface by type and Form

 */
interface GeneratorFactoryInterface extends ContainerAwareInterface
{
    /**
     * @return string[]
     */
    public function getAvailableTypes();

    /**
     * @param $type
     * @param $options
     * @return \Symfony\Component\Form\Form|null
     */
    public function createFormByType($type, $options = []);

    /**
     * @param $type
     * @return bool
     */
    public function hasForm($type);

    /**
     * @param string $type
     * @return DocumentResponseGeneratorInterface
     * @throws \InvalidArgumentException
     */
    public function createGeneratorByType($type);
}