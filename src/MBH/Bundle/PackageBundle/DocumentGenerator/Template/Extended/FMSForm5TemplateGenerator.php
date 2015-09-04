<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Extended;


/**
 * Class FMSForm5TemplateGenerator
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class FMSForm5TemplateGenerator extends RegistrationCardTemplateGenerator
{
    protected function prepareParams(array $formData)
    {
        $params = parent::prepareParams($formData);


        $params['tourists'] = array_filter(is_array($params['tourists']) ? $params['tourists'] : iterator_to_array($params['tourists']),
            function($tourist) {
                return $tourist && (empty($tourist->getCitizenship()) || $tourist->getCitizenship()->getName() == 'Россия');
            }
        );

        return $params;
    }
}