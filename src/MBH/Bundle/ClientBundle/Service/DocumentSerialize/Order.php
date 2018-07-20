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
     * @return array
     */
    public function allCashDocuments(): array
    {
        $return = [];
        $cashDocumentSerialize = $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\CashDocument');
        foreach ($this->entity->getCashDocuments() as $cashDocument) {
            $return[] = (clone $cashDocumentSerialize)->newInstance($cashDocument);
        }

        return $return;
    }

    /**
     * @return array
     */
    public function allServices(): array
    {
        $services = [];
        $serviceSerialize = $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Service');
        foreach ($this->getPackagesServices() as $ps) {
            $services[] = (clone $serviceSerialize)->newInstance($ps);
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

        $sGS = $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\ServiceGroup');
        foreach ($this->getPackagesServices() as $ps) {
            $service = $ps->getService();
            $groupBy = $ps->getPrice() . $service->getId();
            if (!array_key_exists($groupBy, $packageServicesByType)) {
                $packageServicesByType[$groupBy] = (clone $sGS)
                    ->newInstance(new PackageServiceGroupByService($service, $ps->getPrice()));
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