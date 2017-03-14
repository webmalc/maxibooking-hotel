<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\BaseBundle\Form\LanguageType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelContactInformationType extends AbstractType
{
    /** @var  DocumentManager $dm */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ->add('zipCode', TextType::class, [
                'label' => 'form.hotelExtendedType.zip_code',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'label' => 'form.hotelExtendedType.street',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ])
            ->add('internationalStreetName', TextType::class, [
                'group' => 'form.hotelExtendedType.address',
                'label' => 'form.hotelExtendedType.international_street_name.label',
                'help' => 'form.hotelExtendedType.international_street_name.help',
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
            ])
            ->add('flat', TextType::class, [
                'label' => 'form.hotelExtendedType.flat',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
            ])
        ;

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
            ]);

        $builder
            ->add('contactInformation', ContactInfoType::class)
            ->add('supportedLanguages', LanguageType::class, [
                'multiple' => true,
                'label' => 'form.contact_info_type.available_languages.label',
                'group' => 'form.hotelType.general_info',
                'required' => false
            ])
        ;

        $builder->get('city')->addViewTransformer(new EntityToIdTransformer($this->dm, 'MBHHotelBundle:City'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_hotel_contact_information_type';
    }
}
