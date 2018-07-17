<?php

namespace MBH\Bundle\BaseBundle\Form;

use MBH\Bundle\BaseBundle\Service\MultiLangTranslator;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MultiLanguagesType extends AbstractType
{
    private $languages;
    private $multiLangTranslator;
    private $defaultLang;
    private $propertyAccessor;

    public function __construct(ClientConfigManager $clientConfigManager, MultiLangTranslator $multiLangTranslator, string $defaultLang, PropertyAccessor $propertyAccessor)
    {
        $this->languages = $configLanguages = $clientConfigManager->fetchConfig()->getLanguages();
        $this->multiLangTranslator = $multiLangTranslator;
        $this->defaultLang = $defaultLang;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldName = $builder->getName();
        $document = $builder->getData();
        $translationsByLanguages = $this->multiLangTranslator
            ->getTranslationsByLanguages($document, $fieldName, $this->languages);

        foreach ($this->languages as $language) {
            if (isset($translationsByLanguages[$language])) {
                $data = $translationsByLanguages[$language];
            } else {
                $data = $this->defaultLang === $language
                    ? $this->propertyAccessor->getValue($document, $fieldName)
                    : null;
            }

            $fieldType = $options['field_type'];
            $builder
                ->add($language, $fieldType, array_merge([
                    'group' => 'no-group',
                    'mapped' => false,
                    'data' => $data,
                ], $options['fields_options']));
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['embedded'] = true;
        foreach ($this->languages as $language) {
            $field = $view->children[$language];
            $field->vars['languages'] = $this->languages;
            $field->vars['language'] = $language;
            $field->vars['defaultLang'] = $this->defaultLang;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'fields_options' => [],
            'field_type' => TextareaType::class,
            'defaultValue' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhbase_bundle_multi_languages_type';
    }
}
