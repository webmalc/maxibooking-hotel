<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DocumentGeneratorFactoryInterface
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class ChainGeneratorFactory implements GeneratorFactoryInterface
{
    /**
     * @var GeneratorFactoryInterface[]
     */
    private $factories = [];

    private $container;

    public function addFactory(GeneratorFactoryInterface $factory)
    {
        $this->factories[] = $factory;

        return $this;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string[]
     */
    public function getAvailableTypes()
    {
        $types = [];
        foreach($this->factories as $factory) {
            $types = array_merge($types, $factory->getAvailableTypes());
        }
        return $types;
    }

    /**
     * @param $type
     * @return \Symfony\Component\Form\Form
     */
    public function createFormByType($type)
    {
        foreach($this->factories as $factory) {
            $form = $factory->createFormByType($type);
            if($form) {
                return $form;
            }
        }
        return null;
    }

    /**
     * @param $type
     * @return bool
     */
    public function hasForm($type)
    {
        foreach($this->factories as $factory) {
            if($factory->hasForm($type)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $type
     * @return DocumentResponseGeneratorInterface
     * @throws \InvalidArgumentException
     */
    public function createGeneratorByType($type)
    {
        foreach($this->factories as $factory) {
            try{
                $type = $factory->createGeneratorByType($type);
            }catch (\InvalidArgumentException $e) {
                continue;
            }
            if($type) {
                return $type;
            }
        }
        throw new \InvalidArgumentException();
    }
}