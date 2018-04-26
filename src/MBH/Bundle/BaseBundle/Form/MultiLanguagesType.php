<?php

namespace MBH\Bundle\BaseBundle\Form;

use MBH\Bundle\BaseBundle\Service\MultiLangTranslator;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class MultiLanguagesType extends AbstractType
{
    private $languages;
    private $multiLangTranslator;

    public function __construct(ClientConfigManager $clientConfigManager, MultiLangTranslator $multiLangTranslator) {
        $this->languages = $clientConfigManager->fetchConfig()->getLanguages();
        $this->multiLangTranslator = $multiLangTranslator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldName = $builder->getName();
        $translationsByFieldsWithLang = $this->multiLangTranslator->getTranslationsByLanguages($builder->getData(), $fieldName, $this->languages);

        foreach ($this->languages as $language) {
            $data = isset($translationsByFieldsWithLang[$language]) ? $translationsByFieldsWithLang[$language] : null;
            $builder
                ->add($language, TextareaType::class, [
                    'label' => 'form.hotelType.description',
                    'group' => 'no-group',
                    'attr' => ['class' => 'tinymce'],
                    'required' => false,
                    'mapped' => false,
                    'data' => $data
                ]);
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['embedded'] = true;
        foreach ($this->languages as $language) {
            $field = $view->children[$language];
            $field->vars['languages'] = $this->languages;
            $field->vars['language'] = $language;
        }
    }

    public function getBlockPrefix()
    {
        return 'mbhbase_bundle_multi_languages_type';
    }
}
