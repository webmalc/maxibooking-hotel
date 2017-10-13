<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Extended;


use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\DefaultTemplateGenerator;

/**
 * Class RegistrationCardTemplateGenerator

 */
class RegistrationCardTemplateGenerator extends DefaultTemplateGenerator
{
    protected function prepareParams(array $formData)
    {
        $params = parent::prepareParams($formData);

        /** @var Package $package */
        $package = $formData['package'];
        $hotel = $package->getRoomType()->getHotel();

        $tourists = $package->getTourists(); //guests
        if(count($tourists) == 0) {
            $fakeTourist = new Tourist(); // empty form
            $tourists = [$fakeTourist];
        }

        $params['tourists'] = $tourists;
        $params['arrivalTimeDefault'] = $hotel->getPackageArrivalTime();
        $params['departureTimeDefault'] = $hotel->getPackageDepartureTime();

        return $params;
    }
}