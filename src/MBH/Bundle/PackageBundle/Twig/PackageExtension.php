<?php

namespace MBH\Bundle\PackageBundle\Twig;


use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Services\PackageAccommodationManipulator;

class PackageExtension extends \Twig_Extension
{

    private $pAccManipulator;

    /**
     * PackageExtension constructor.
     * @param $pAccManipulator
     */
    public function __construct(PackageAccommodationManipulator $pAccManipulator)
    {
        $this->pAccManipulator = $pAccManipulator;
    }


    public function getName()
    {
        return 'mbh_package_accommodation_extension';
    }


    public function getFunctions()
    {
        return [
            'pAccClass' => new \Twig_SimpleFunction('pAccClass', [$this, 'getFullAccommodationClass'], ['is_safe' => array('html')])
        ];
    }

    public function isFullAccommodation(Package $package)
    {
        return $this->pAccManipulator->isFullAccommodation($package);
    }

    public function getFullAccommodationClass(Package $package)
    {
        if ($package->getAccommodation() && $this->isFullAccommodation($package)) {
            return 'label-success';
        } elseif ($package->getAccommodation()) {
            return 'label-warning';
        } else {
            return 'label-default';
        }
    }

}