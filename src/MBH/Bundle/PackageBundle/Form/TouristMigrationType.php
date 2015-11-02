<?php

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TouristMigrationType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TouristMigrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('series', 'text', [
                'label' => 'tourist.migration.type_series',
                'group' => 'migration.card',
                'required' => false,
            ])
            ->add('number', 'text', [
                'label' => 'tourist.migration.type_number',
                'group' => 'migration.card',
                'required' => false,
            ])
            ->add('representative', 'textarea', [
                'label' => 'tourist.migration.type_representative',
                'group' => 'migration.card',
                'required' => false,
            ])
            ->add('address', 'textarea', [
                'label' => 'tourist.migration.type_address',
                'group' => 'migration.card',
                'required' => false,
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Migration'
        ]);
    }


    public function getName()
    {
        return 'migration';
    }
}