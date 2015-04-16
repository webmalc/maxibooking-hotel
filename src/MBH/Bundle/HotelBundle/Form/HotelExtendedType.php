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
                    'label' => 'form.hotelExtendedType.city',
                    'group' => 'form.hotelExtendedType.location',
                    'mapped' => false,
                    'required' => true,
                    'data' => (empty($options['city'])) ? null : $options['city']->getId(),
                    'attr' => ['placeholder' => 'form.hotelExtendedType.placeholder_location', 'style' => 'min-width: 500px']
                ])
                ->add('latitude', 'text', [
                    'label' => 'form.hotelExtendedType.latitude',
                    'group' => 'form.hotelExtendedType.location',
                    'required' => false,
                    'attr' => ['placeholder' => '55.752014'],
                    'help' => 'form.hotelExtendedType.gps_coordinates_latitude <a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">form.hotelExtendedType.know_hotel_coordinates</a>'
                ])
                ->add('longitude', 'text', [
                    'label' => 'form.hotelExtendedType.longitude',
                    'group' => 'form.hotelExtendedType.location',
                    'required' => false,
                    'attr' => ['placeholder' => '37.617515'],
                    'help' => 'form.hotelExtendedType.gps_coordinates_longitude' .'<a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">'.'form.hotelExtendedType.know_hotel_coordinates</a>'
                ])
                ->add('rating', 'text', [
                    'label' => 'form.hotelExtendedType.how_many_stars_hotel',
                    'group' => 'form.hotelExtendedType.parameters',
                    'required' => false,
                ])
                ->add('type', 'choice', [
                    'label' => 'form.hotelExtendedType.hotel_type',
                    'group' => 'form.hotelExtendedType.parameters',
                    'required' => false,
                    'choices' => (isset($options['config']['types'])) ? $options['config']['types'] : [],
                    'multiple' => true
                ])
                ->add('theme', 'choice', [
                    'label' => 'form.hotelExtendedType.hotel_theme',
                    'group' => 'form.hotelExtendedType.parameters',
                    'required' => false,
                    'choices' => (isset($options['config']['themes'])) ? $options['config']['themes'] : [],
                    'multiple' => true
                ])
                ->add('facilities', 'choice', [
                    'label' => 'form.hotelExtendedType.hotel_amenities',
                    'group' => 'form.hotelExtendedType.parameters',
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
