<?php

namespace MBH\Bundle\HotelBundle\Form;

use MBH\Bundle\OnlineBundle\Document\DistrictRepository;
use MBH\Bundle\OnlineBundle\Document\HighwayRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelExtendedType extends AbstractType
{
    /**
     * @var DocumentManager
     */
    private $dm;
    /**
     * @var HighwayRepository
     */
    protected $highwayRepository;
    /**
     * @var DistrictRepository
     */
    protected $districtRepository;

    public function __construct(DocumentManager $dm, HighwayRepository $highwayRepository, DistrictRepository $districtRepository)
    {
        $this->dm = $dm;
        $this->highwayRepository = $highwayRepository;
        $this->districtRepository = $districtRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', 'text', [
                'label' => 'form.hotelExtendedType.city',
                'group' => 'form.hotelExtendedType.address',
                'required' => true,
                'attr' => [
                    'class' => 'citySelect',
                    'placeholder' => 'form.hotelExtendedType.city',
                ]
            ])
        ;
        $districtList = $this->districtRepository->getList();
        $builder
            ->add('district', 'choice', [
                'label' => 'form.hotelType.district',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
                'choices' => array_combine($districtList, $districtList)
            ]);
        $highwayList = $this->highwayRepository->getList();
        $builder
            ->add('highway', 'choice', [
                'label' => 'form.hotelType.highway',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
                'choices' => array_combine($highwayList, $highwayList),
                'multiple' => true,
            ]);
        $builder
            ->add('MKADdistance', 'number', [
                'label' => 'form.hotelType.MKADdistance',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ])
        ;
        $builder
            ->add('settlement', 'text', [
                'label' => 'form.hotelExtendedType.settlement',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
            ])
            ->add('street', 'text', [
                'label' => 'form.hotelExtendedType.street',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ])
            ->add('house', 'text', [
                'label' => 'form.hotelExtendedType.house',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ])
            ->add('corpus', 'text', [
                'label' => 'form.hotelExtendedType.corpus',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ]);

        $builder->add('flat', 'text', [
                'label' => 'form.hotelExtendedType.flat',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
            ]);
        //}
        $builder
            ->add('latitude', 'text', [
                'label' => 'form.hotelExtendedType.latitude',
                'group' => 'form.hotelExtendedType.location',
                'required' => false,
                'attr' => ['placeholder' => '55.752014'],
                'help' => 'form.hotelExtendedType.gps_coordinates_latitude<br><a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">form.hotelExtendedType.know_hotel_coordinates</a>'
            ])
            ->add('longitude', 'text', [
                'label' => 'form.hotelExtendedType.longitude',
                'group' => 'form.hotelExtendedType.location',
                'required' => false,
                'attr' => ['placeholder' => '37.617515'],
                'help' => 'form.hotelExtendedType.gps_coordinates_longitude<br><a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">form.hotelExtendedType.know_hotel_coordinates</a>'
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
            ->add('facilities', 'mbh_facilities', [
                'label' => 'form.hotelExtendedType.hotel_amenities',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
            ])
            ->add('panorama', 'text', [
                'label' => 'form.hotelExtendedType.panorama',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
            ])
            ->add('scheme', 'text', [
                'label' => 'form.hotelExtendedType.scheme',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
            ])
    ;

        $builder->add('vega_address_id', 'number', [
            'label' => 'form.hotelExtendedType.vega_address_id',
            'help' => 'form.hotelExtendedType.vega_address_id_help',
            'group' => 'form.hotelExtendedType.integration',
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
        ]);
    }


    public function getName()
    {
        return 'mbh_bundle_hotelbundle_hotel_extended_type';
    }

}
