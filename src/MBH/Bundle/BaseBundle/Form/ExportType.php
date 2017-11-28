<?php

namespace MBH\Bundle\BaseBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fields', InvertChoiceType::class, [
                'multiple' => true,
                'choices' => $options['fieldChoices'],
                'label' => 'forms.export_type.fields.label',
                'help' => 'forms.export_type.fields.help',
                'group' => 'forms.export_type.main_group',
                'data' => $options['fieldChoices']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'fieldChoices' => null
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhbase_bundle_export_type';
    }
}
