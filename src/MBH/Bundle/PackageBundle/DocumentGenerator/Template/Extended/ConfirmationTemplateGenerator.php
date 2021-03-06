<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Extended;


use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\DefaultTemplateGenerator;

/**
 * Class ConfirmationTemplateGenerator

 */
class ConfirmationTemplateGenerator extends DefaultTemplateGenerator
{
    protected function prepareParams(array $formData)
    {
        $params =  parent::prepareParams($formData);

        $hasServices = isset($formData['hasServices']) && $formData['hasServices'];
        $hasFull = isset($formData['hasFull']) && $formData['hasFull'];

        /** @var PackageService[] $packageServices */
        $packageServices = [];

        /** @var PackageServiceGroupByService[] $packageServicesByType */
        $packageServicesByType = []; //todo mongo aggregation, move to repository

        $total = 0;
        if($hasFull) {
            $packages = $formData['package']->getOrder()->getPackages();
        } else {
            $packages = [$formData['package']];
        }
        foreach($packages as $package) {
            $packageServices = array_merge(iterator_to_array($package->getServices()), $packageServices);
            $total += $package->getPackagePrice(true);
        }

        if($hasServices) {
            foreach($packageServices as $ps) {
                $service = $ps->getService();
                $groupBy = $ps->getPrice().$service->getId();
                if(!array_key_exists($groupBy, $packageServicesByType)) {
                    $packageServicesByType[$groupBy] = new PackageServiceGroupByService($service, $ps->getPrice());
                }
                $packageServicesByType[$groupBy]->add($ps);
                $total += $ps->getTotal();
            }
        }

        return $params + [
            'total' => $total,
            'packageServicesByType' => $packageServicesByType
        ];
    }
}