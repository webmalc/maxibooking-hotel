<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\Service;

/**
 * Class PackageServiceGroupByService

 */
class PackageServiceGroupByService extends PackageServiceGroup
{
    /**
     * @var Service
     */
    protected $byService;

    protected $price;

    function __construct(Service $service, $price)
    {
        $this->byService = $service;
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
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