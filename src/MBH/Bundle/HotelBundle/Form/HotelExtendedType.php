<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\BaseBundle\Form\FacilitiesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelExtendedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating', TextType::class, [
                'label' => 'form.hotelExtendedType.how_many_stars_hotel',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
            ])
            ->add('type', InvertChoiceType::class, [
                'label' => 'form.hotelExtendedType.hotel_type',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
                'choices' => (isset($options['config']['types'])) ? $options['config']['types'] : [],
                'multiple' => true
            ])
            ->add('theme', InvertChoiceType::class, [
                'label' => 'form.hotelExtendedType.hotel_theme',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
                'choices' => (isset($options['config']['themes'])) ? $options['config']['themes'] : [],
                'multiple' => true
            ])
            ->add('facilities', FacilitiesType::class, [
                'label' => 'form.hotelExtendedType.hotel_amenities',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
            ]);

        $builder->add('vega_address_id', NumberType::class, [
            'label' => 'form.hotelExtendedType.vega_address_id',
            'help' => 'form.hotelExtendedType.vega_address_id_help',
            'group' => 'form.hotelExtendedType.integration',
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'config' => null,
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_hotel_extended_type';
    }

}
