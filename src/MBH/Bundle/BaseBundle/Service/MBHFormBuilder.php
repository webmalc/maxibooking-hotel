<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Form\MultiLanguagesType;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;

class MBHFormBuilder
{
    protected $clientConfigManager;
    private $formFactory;

    public function __construct(ClientConfigManager $clientConfigManager, FormFactory $formFactory)
    {
        $this->clientConfigManager = $clientConfigManager;
        $this->formFactory = $formFactory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string $fieldName
     * @param array $fieldOptions
     * @return FormBuilderInterface
     */
    public function addMultiLangField(FormBuilderInterface $builder, string $fieldType, string $fieldName, array $fieldOptions)
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

    /**
     * @param FormBuilderInterface $formBuilder
     * @param string $mergedFormName
     * @param $data
     */
    public function addMergedFormFields(FormBuilderInterface $formBuilder, string $mergedFormName, $data)
    {
        $mergedFormBuilder = $this->formFactory->createBuilder($mergedFormName, $data);
        foreach ($mergedFormBuilder->all() as $formField) {
            $formBuilder->add($formField);
        }
    }
}