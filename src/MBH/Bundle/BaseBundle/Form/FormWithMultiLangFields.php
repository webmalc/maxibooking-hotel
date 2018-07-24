<?php

namespace MBH\Bundle\BaseBundle\Form;

use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class FormWithMultiLangFields extends AbstractType
{
    protected $clientConfigManager;

    public function __construct(ClientConfigManager $clientConfigManager)
    {
        $this->clientConfigManager = $clientConfigManager;
    }

    protected function addMultiLangField(FormBuilderInterface $builder, string $fieldType, string $fieldName, array $fieldOptions)
    {
        $isDocumentExists = !empty($builder->getData()->getId());
        if (!$isDocumentExists || $this->clientConfigManager->hasSingleLanguage()) {
            $builder->add($fieldName, $fieldType, $fieldOptions);
        } else {
            $group = $fieldOptions['group'];
            unset($fieldOptions['group']);
            unset($fieldOptions['required']);

            $builder->add($fieldName, MultiLanguagesType::class, [
                'mapped' => false,
                'data' => $builder->getData(),
                'field_type' => $fieldType,
                'group' => $group,
                'fields_options' => array_merge([
                    'required' => false
                ], $fieldOptions),
            ]);
        }

        return $builder;
    }
}
