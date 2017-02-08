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
    private $dm;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->dm = $options['dm'];

        $builder
            ->add('city', TextType::class, [
                'label' => 'form.hotelExtendedType.city',
                'group' => 'form.hotelExtendedType.address',
                'required' => true,
                'attr' => [
                    'class' => 'citySelect',
                    'placeholder' => 'form.hotelExtendedType.city',
                ]
            ])
            ->add('settlement', TextType::class, [
                'label' => 'form.hotelExtendedType.settlement',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'label' => 'form.hotelExtendedType.street',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ])
            ->add('house', TextType::class, [
                'label' => 'form.hotelExtendedType.house',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ])
            ->add('corpus', TextType::class, [
                'label' => 'form.hotelExtendedType.corpus',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ]);

        $builder->add('flat', TextType::class, [
            'label' => 'form.hotelExtendedType.flat',
            'group' => 'form.hotelExtendedType.address',
            'required' => false,
        ]);
        //}
        $builder
            ->add('latitude', TextType::class, [
                'label' => 'form.hotelExtendedType.latitude',
                'group' => 'form.hotelExtendedType.location',
                'required' => false,
                'attr' => ['placeholder' => '55.752014'],
                'help' => 'form.hotelExtendedType.gps_coordinates_latitude<br><a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">form.hotelExtendedType.know_hotel_coordinates</a>'
            ])
            ->add('longitude', TextType::class, [
                'label' => 'form.hotelExtendedType.longitude',
                'group' => 'form.hotelExtendedType.location',
                'required' => false,
                'attr' => ['placeholder' => '37.617515'],
                'help' => 'form.hotelExtendedType.gps_coordinates_longitude<br><a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">form.hotelExtendedType.know_hotel_coordinates</a>'
            ])
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

        $builder
            ->add('contact_full_name', TextType::class, [
                'label' => 'form.hotelExtendedType.contact_full_name.label',
                'help' => 'form.hotelExtendedType.contact_full_name.help',
                'group' => 'form.hotelExtendedType.contact_person_info',
                'required' => false
            ])
            ->add('contact_email', EmailType::class, [
                'label' => 'form.hotelExtendedType.contact_email.label',
                'help' => 'form.hotelExtendedType.contact_email.help',
                'group' => 'form.hotelExtendedType.contact_person_info',
                'required' => false
            ])
            ->add('contact_phone_number', TextType::class, [
                'label' => 'form.hotelExtendedType.contact_phone_number.label',
                'help' => 'form.hotelExtendedType.contact_phone_number.help',
                'group' => 'form.hotelExtendedType.contact_person_info',
                'required' => false
            ]);

        $builder->get('city')->addViewTransformer(new EntityToIdTransformer($this->dm, 'MBHHotelBundle:City'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'city' => null,
            'config' => null,
            'dm' => null
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_hotel_extended_type';
    }

}
