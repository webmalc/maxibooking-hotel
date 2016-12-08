<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Extended;


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
        $container = $this->container;

        $tourists = $formData['package']->getTourists(); //guests
        if(count($tourists) == 0) {
            $fakeTourist = new Tourist(); // empty form
            $tourists = [$fakeTourist];
        }

        $params['tourists'] = $tourists;
        $params['arrivalTimeDefault'] = $container->getParameter('mbh_package_arrival_time');
        $params['departureTimeDefault'] = $container->getParameter('mbh_package_departure_time');
        return $params;
    }
}