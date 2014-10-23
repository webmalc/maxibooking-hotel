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
                    'label' => ($options['isHostel']) ? 'Комната/койко-место': 'Комната',
                    'required' => true,
                    'empty_value' => '',
                    'group' => 'Выбрать размещение',
                    'multiple' => false,
                    'choices' => $options['rooms'],
                    'constraints' => new NotBlank()
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'rooms' => [],
            'isHostel' => false
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_accommodation_type';
    }

}
