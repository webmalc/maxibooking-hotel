<?php

namespace MBH\Bundle\OnlineBundle\Form;

use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnalyticsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('googleAnalyticConfigIsEnabled', CheckboxType::class, [
                'property_path' => 'googleAnalyticConfig.isEnabled',
                'required' => false,
                'group' => 'Google',
                'attr' => [
                    'class' => 'box-full-visibility-checkbox',
                ],
                'label' => 'analytics_form.is_enabled.label',
            ])
            ->add('googleAnalyticConfigId', TextType::class, [
                'property_path' => 'googleAnalyticConfig.id',
                'required' => false,
                'group' => 'Google',
                'label' => 'analytics_form.google_id.label',
                'attr' => [
                    'placeholder' => 'UA-XXXXXXXXX-Y'
                ]
            ])
            ->add('yandexAnalyticConfigIsEnabled', CheckboxType::class, [
                'property_path' => 'yandexAnalyticConfig.isEnabled',
                'required' => false,
                'group' => 'Yandex',
                'attr' => [
                    'class' => 'box-full-visibility-checkbox'
                ],
                'label' => 'analytics_form.is_enabled.label'
            ])
            ->add('yandexAnalyticConfigId', TextType::class, [
                'property_path' => 'yandexAnalyticConfig.id',
                'required' => false,
                'group' => 'Yandex',
                'label' => 'analytics_form.yandex_id.label'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => FormConfig::class
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhonline_bundle_form_analytics';
    }
}
