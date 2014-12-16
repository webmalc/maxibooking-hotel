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
                ->add('latitude', 'text', [
                    'label' => 'Широта',
                    'group' => 'Местоположение',
                    'required' => false,
                    'attr' => ['placeholder' => '55.752014'],
                    'help' => 'GPS координата широты отеля. <a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">Узнать координаты отеля.</a>'
                ])
                ->add('longitude', 'text', [
                    'label' => 'Долгота',
                    'group' => 'Местоположение',
                    'required' => false,
                    'attr' => ['placeholder' => '37.617515'],
                    'help' => 'GPS координата широты отеля. <a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">Узнать координаты отеля.</a>'
                ])
                ->add('rating', 'text', [
                    'label' => 'Звездность отеля',
                    'group' => 'Параметры',
                    'required' => false,
                ])
                ->add('type', 'choice', [
                    'label' => 'Тип отеля',
                    'group' => 'Параметры',
                    'required' => false,
                    'choices' => (isset($options['config']['types'])) ? $options['config']['types'] : [],
                    'multiple' => true
                ])
                ->add('theme', 'choice', [
                    'label' => 'Тема отеля',
                    'group' => 'Параметры',
                    'required' => false,
                    'choices' => (isset($options['config']['themes'])) ? $options['config']['themes'] : [],
                    'multiple' => true
                ])
                ->add('facilities', 'choice', [
                    'label' => 'Удобства отеля',
                    'group' => 'Параметры',
                    'required' => false,
                    'choices' => (isset($options['config']['facilities'])) ? $options['config']['facilities'] : [],
                    'multiple' => true
                ])

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'city' => null,
            'config' => null,
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_hotel_extended_type';
    }

}
