<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator\Extended;


use MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator\DefaultDocumentTemplateGenerator;
use MBH\Bundle\PackageBundle\Document\Tourist;

/**
 * Class RegistrationCardTemplateGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class RegistrationCardTemplateGenerator extends DefaultDocumentTemplateGenerator
{
    protected function getAdditionalParams()
    {
        $params = parent::getAdditionalParams();

        $tourists = $this->package->getTourists(); //guests

        if(count($tourists) == 0) {
            $fakeTourist = new Tourist(); // empty form
            $tourists = [$fakeTourist];
        }

        return $params + [
            'tourists' => $tourists
        ];
    }
}