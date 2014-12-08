<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;


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

    public function check(Package $package)
    {
        if (!$package->getCreatedBy()) {
            return true;
        }

        $securityContext = $this->container->get('security.context');
        $roles = ['ROLE_ADMIN', 'ROLE_ADMIN_HOTEL', 'ROLE_BOOKKEEPER', 'ROLE_SENIOR_MANAGER'];

        if ($securityContext->isGranted($roles)) {
            return true;
        }

        return $securityContext->isGranted('EDIT', $package);;
    }

    /**
     * @param Package $package
     * @return boolean
     */
    public function checkHotel(Package $package)
    {
        return $this->container->get('mbh.hotel.selector')->checkPermissions($package->getRoomType()->getHotel());
    }

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

            $packages = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('s')
                ->field('roomType.id')->in($this->container->get('mbh.helper')->toIds($hotel->getRoomTypes()))
                ->getQuery()
                ->execute()
            ;
            $result = array_merge($packages->toArray(), $result);
        }

        $dm->getFilterCollection()->enable('softdeleteable');

        return $result;
    }

}
