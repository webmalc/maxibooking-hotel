<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\Service;

/**
 * Class PackageServiceGroupByService
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class PackageServiceGroupByService extends PackageServiceGroup
{
    /**
     * @var Service
     */
    protected $byService;

    function __construct(Service $service)
    {
        $this->byService = $service;
    }

    public function add(PackageService $packageService)
    {
        if($packageService->getService() != $this->byService)
            throw new \Exception();

        parent::add($packageService);
    }

    /**
     * @return Service
     */
    public function getByService()
    {
        return $this->byService;
    }
}