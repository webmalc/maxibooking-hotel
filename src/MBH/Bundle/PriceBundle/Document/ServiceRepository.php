<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\Package;

/**
 * Class ServiceRepository

 */
class ServiceRepository extends DocumentRepository
{
    /**
     * @param Tariff $tariff
     * @param $code
     * @return null|Service
     */
    public function findOneByCode(Tariff $tariff, $code)
    {
        $services = $this->getAvailableServicesForTariff($tariff);

        foreach ($services as $cat) {
            foreach ($cat as $service) {
                if ($service->getCode() == $code) {
                    return $service;
                }
            }
        }

        return null;
    }

    /**
     * @param Package $package
     * @return \Doctrine\Common\Collections\Collection|Service[]
     */
    public function getAvailableServicesForPackage(Package $package)
    {
        return $this->getAvailableServicesForTariff($package->getTariff());
    }

    /**
     * @param Tariff $tariff
     * @param boolean $all
     * @return \Doctrine\Common\Collections\Collection|Service[]
     */
    public function getAvailableServicesForTariff(Tariff $tariff, $all = false)
    {
        $services = iterator_to_array($tariff->getServices());
        if (count($services) == 0 || $all) {
            $serviceCategories = $tariff->getHotel()->getServicesCategories();
            foreach ($serviceCategories as $category) {
                $services = array_merge($services, iterator_to_array($category->getServices()));
            }
        }

        $services = array_filter($services, function($service) {
            return $service->getIsEnabled();
        });

        $results = [];
        foreach($services as $service) {
            $results[$service->getCategory()->getName()][$service->getId()] = $service;
        }

        return $results;
    }
}