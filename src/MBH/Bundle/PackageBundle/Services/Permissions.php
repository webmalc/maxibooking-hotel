<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\PackageBundle\Document\Order as OrderDoc;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\BaseBundle\Document\Base;


/**
 *  Calculation service
 */
class Permissions
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Base $doc
     * @return boolean
     */
    public function checkHotel(Base $doc)
    {
        $hotelSelector = $this->container->get('mbh.hotel.selector');
        if ($doc instanceof Package) {
            return $hotelSelector->checkPermissions($doc->getRoomType()->getHotel());
        }
        if ($doc instanceof OrderDoc) {
            foreach ($doc->getPackages() as $package) {
                if (!$hotelSelector->checkPermissions($package->getRoomType()->getHotel())) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAvailablePackages()
    {
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $dm->getFilterCollection()->disable('softdeleteable');
        $result = [];

        $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {

            if (!$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
                continue;
            }

            $packages = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder()
                ->field('roomType.id')->in($this->container->get('mbh.helper')->toIds($hotel->getRoomTypes()))
                ->getQuery()
                ->execute()
            ;
            $result = array_merge($packages->toArray(), $result);
        }

        $dm->getFilterCollection()->enable('softdeleteable');

        return $result;
    }

    /**
     * @return array
     */
    public function getAvailableOrders()
    {
        $result = [];

        foreach ($this->getAvailablePackages() as $package) {
            $result[] = $package->getOrder();
        }
        return $result;
    }

}
