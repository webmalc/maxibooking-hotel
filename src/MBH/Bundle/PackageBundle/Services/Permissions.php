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

}
