<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HotelExtendedType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('address', 'text', [
                    'label' => 'Город',
                    'group' => 'Местоположение',
                    'mapped' => false,
                    'required' => true,
                    'data' => (empty($options['city'])) ? null : $options['city']->getId(),
                    'attr' => ['placeholder' => 'Москва, Московская обл., Щелково', 'style' => 'min-width: 500px']
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'city' => null
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_hotel_extended_type';
    }

}
