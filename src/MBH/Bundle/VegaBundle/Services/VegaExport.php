<?php

namespace MBH\Bundle\VegaBundle\Services;


use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class VegaExport
 * @package MBH\Bundle\VegaBundle\Services
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class VegaExport
{
    private $container;

    public function __construct(Container $container)
    {
           $this->container = $container;
    }

    /**
     * @var String
     */
    public function getXML(Tourist $tourist)
    {
        $xml = $this->container->get('twig')->render('MBHVegaBundle::vega_export.xml.twig', [
            'tourist' => $tourist
        ]);


        /*$writer = new \XMLWriter();
        $writer->*/

        return $xml;
    }
}