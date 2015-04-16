<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PackageAccommodationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('room', 'choice', [
                    'label' => ($options['isHostel']) ? 'form.packageAccommodationType.room_or_bed': 'form.packageAccommodationType.room',
                    'required' => true,
                    'empty_value' => '',
                    'group' => 'form.packageAccommodationType.choose_placement',
                    'multiple' => false,
                    'choices' => $options['rooms'],
                    'constraints' => new NotBlank()
                ])
            ->add('isCheckIn', 'checkbox', [
                'label' => 'form.packageAccommodationType.are_guests_checked_in',
                'value' => true,
                'required' => false,
                'data' => $options['guests'],
                'help' => 'form.packageAccommodationType.are_guests_checked_in_help'
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'rooms' => [],
            'isHostel' => false,
            'guests' => false
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_accommodation_type';
    }

}
