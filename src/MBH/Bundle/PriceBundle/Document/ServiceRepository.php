<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\Package;

/**
 * Class ServiceRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class ServiceRepository extends DocumentRepository
{
    /**
     * @param Package $package
     * @return \Doctrine\Common\Collections\Collection|Service[]
     */
    public function getAvailableServicesForPackage(Package $package)
    {
        $tariff = $package->getTariff();
        $services = iterator_to_array($tariff->getServices());
        if (count($services) == 0) {
            $serviceCategories = $tariff->getHotel()->getServicesCategories();
            foreach ($serviceCategories as $category) {
                $services = array_merge($services, iterator_to_array($category->getServices()));
            }
        }

        /*$services = array_filter($services, function($service) {
            return $service->getIsEnabled();
        });*/

        $results = [];
        foreach($services as $service) {
            $results[$service->getCategory()->getName()][$service->getId()] = $service;
        }

        return $results;
    }
}