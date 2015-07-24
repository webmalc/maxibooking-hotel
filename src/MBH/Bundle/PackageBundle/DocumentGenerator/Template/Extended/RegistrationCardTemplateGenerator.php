<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Extended;


use MBH\Bundle\PackageBundle\DocumentGenerator\Template\DefaultTemplateGenerator;
use MBH\Bundle\PackageBundle\Document\Tourist;

/**
 * Class RegistrationCardTemplateGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class RegistrationCardTemplateGenerator extends DefaultTemplateGenerator
{
    protected function prepareParams(array $formData)
    {
        $params = parent::prepareParams($formData);

        $tourists = $formData['package']->getTourists(); //guests
        if(count($tourists) == 0) {
            $fakeTourist = new Tourist(); // empty form
            $tourists = [$fakeTourist];
        }

        $params['tourists'] = $tourists;
        return $params;
    }
}