<?php
/**
 * Created by PhpStorm.
 * User: mb
 * Date: 06.07.15
 * Time: 11:35
 */

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateProvider;


use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\PackageService;

class ConfirmationTemplateProvider extends DefaultDocumentTemplateProvider
{
    protected function getAdditionalParams()
    {
        $params =  parent::getAdditionalParams();
        /** @var PackageService[] $packageServices */
        $packageServices = [];
        /** @var PackageServiceGroupByService[] $packageServicesByType */
        $packageServicesByType = [];

        $total = 0;
        foreach($this->package->getOrder()->getPackages() as $package) {
            $packageServices = array_merge(iterator_to_array($package->getServices()), $packageServices);
            $total += $package->getPackagePrice(true);
        }
        foreach($packageServices as $ps) {
            if(!array_key_exists($ps->getService()->getId(), $packageServicesByType)) {
                $group = new PackageServiceGroupByService($ps->getService());
                $packageServicesByType[$ps->getService()->getId()] = $group;
            }
            $packageServicesByType[$ps->getService()->getId()]->add($ps);
            $total += $ps->getTotal();
        }

        return [
            'total' => $total,
            'packageServicesByType' => $packageServicesByType
        ];
    }
}