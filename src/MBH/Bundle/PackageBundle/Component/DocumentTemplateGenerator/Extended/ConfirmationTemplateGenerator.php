<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator\Extended;


use MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator\DefaultDocumentTemplateGenerator;
use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\PackageService;

/**
 * Class ConfirmationTemplateGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class ConfirmationTemplateGenerator extends DefaultDocumentTemplateGenerator
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

        return $params + [
            'total' => $total,
            'packageServicesByType' => $packageServicesByType
        ];
    }
}