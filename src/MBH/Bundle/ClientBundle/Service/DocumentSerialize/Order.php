<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\Order as OrderBase;
use MBH\Bundle\PackageBundle\Document\PackageService;

/**
 * Class Order
 *
 * @property OrderBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class Order extends Common
{
    protected const METHOD = [
        'getPaid|money',
        'getDebt|money',
    ];

    /**
     * @var PackageService[] array
     */
    private $packageServices;

    /**
     * @var bool
     */
    private $isPackageServicesInit = false;

    /**
     * @return array| Package[]
     */
    public function getPackages(): array
    {
        $wrapper = [];

        /** @var \MBH\Bundle\PackageBundle\Document\Package $package */
        foreach ($this->entity->getPackages() as $package) {
            $wrapper[] = $this->helper->entityDecoratorInstance($package);
        }

        return $wrapper;
    }


    /**
     * @return array
     */
    public function allCashDocuments(): array
    {
        $return = [];

        foreach ($this->entity->getCashDocuments() as $cashDocument) {
            $return[] = $this->helper->entityDecoratorInstance($cashDocument);
        }

        return $return;
    }

    /**
     * @return array
     */
    public function allServices(): array
    {
        $services = [];
        foreach ($this->getPackagesServices() as $ps) {
            $services[] = $this->helper->entityDecoratorInstance($ps);
        }

        return $services;
    }

    /**
     * @return array
     */
    public function allServicesByGroup(): array
    {
        /** @var PackageServiceGroupByService[] $packageServicesByType */
        $packageServicesByType = [];

        foreach ($this->getPackagesServices() as $ps) {
            $service = $ps->getService();
            $groupBy = $ps->getPrice() . $service->getId();
            if (!array_key_exists($groupBy, $packageServicesByType)) {
                $packageServicesByType[$groupBy] =
                    $this->helper->entityDecoratorInstance(new PackageServiceGroupByService($service, $ps->getPrice()));
            }
            $packageServicesByType[$groupBy]->add($ps);
        }

        return $packageServicesByType;
    }

    /**
     * @return int
     */
    public function getAmountServices(): int
    {
        return count($this->getPackagesServices());
    }

    /**
     * @return int
     */
    public function getAmountServicesByGroup(): int
    {
        return count($this->allServicesByGroup());
    }

    /**
     * @return string
     */
    public function getCreateDate(): string
    {
        $date = $this->entity->getCreatedAt();

        return $date !== null
            ? $date->format('d.m.Y')
            : '';
    }

    protected function getSourceClassName()
    {
        return OrderBase::class;
    }

    /**
     * @return array
     */
    private function getPackagesServices(): array
    {
        if (!$this->isPackageServicesInit) {
            $packageServices = [];
            /** @var \MBH\Bundle\PackageBundle\Document\Package $package */
            foreach ($this->entity->getPackages() as $package) {
                $packageServices = array_merge(iterator_to_array($package->getServices()), $packageServices);
            }

            $this->packageServices = $packageServices;
            $this->isPackageServicesInit = true;
        }

        return $this->packageServices;
    }
}