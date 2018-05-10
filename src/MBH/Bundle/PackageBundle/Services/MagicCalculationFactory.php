<?php


namespace MBH\Bundle\PackageBundle\Services;


use Symfony\Component\DependencyInjection\ContainerInterface;

class MagicCalculationFactory extends CalculationFactory
{
    protected static function getService(ContainerInterface $container)
    {
        return new MagicCalculationRounded($container);
    }

}