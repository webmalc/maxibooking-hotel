<?php


namespace MBH\Bundle\PackageBundle\Services;


use MBH\Bundle\ClientBundle\Document\ClientConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CalculationFactory
{
    public static function createCalculation(ContainerInterface $container)
    {
        /** @var ClientConfig $clientConfig */
        $clientConfig = $container
            ->get('doctrine_mongodb.odm.default_document_manager')
            ->getRepository('MBHClientBundle:ClientConfig')
            ->fetchConfig();

        $calculation = self::getService($container);
        if (null !== $clientConfig->getPriceRoundSign()) {
            $calculation->setRoundedSign($clientConfig->getPriceRoundSign());
        }

        return $calculation;
    }

    protected static function getService(ContainerInterface $container)
    {
        return new CalculationRounded($container);
    }

}