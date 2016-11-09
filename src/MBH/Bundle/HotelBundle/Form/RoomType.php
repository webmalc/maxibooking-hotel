<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\FacilitiesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RoomType
 */
class RoomType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'form.roomType.name',
                'required' => true,
                'group' => 'form.roomType.general_info',
                'attr' => ['placeholder' => '27']
            ])
            ->add('title', TextType::class, [
                'label' => 'form.roomType.inner_name',
                'required' => false,
                'group' => 'form.roomType.general_info',
                'attr' => ['placeholder' => 'form.roomType.placeholder_27_with_repair'],
                'help' => 'form.roomType.maxibooking_inner_name'
            ]);

        $housingOptions = [
            'label' => 'form.roomType.housing',
            'group' => 'form.roomType.general_info',
            'class' => 'MBH\Bundle\HotelBundle\Document\Housing',
            'required' => false
        ];
        $hotelId = $options['hotelId'];
        if($hotelId) {
            $housingOptions['query_builder'] = function(DocumentRepository $dr) use ($hotelId) {
                return $dr->createQueryBuilder()->field('hotel.id')->equals($hotelId);
            };
        }

        $builder
            ->add('housing', DocumentType::class, $housingOptions)
            ->add('floor', TextType::class, [
                'label' => 'form.roomType.floor',
                'group' => 'form.roomType.general_info',
                'required' => false
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'form.roomType.is_included',
                'group' => 'form.roomType.settings',
                'value' => true,
                'required' => false,
                'help' => 'form.roomType.is_room_included_in_search'
            ]);

        if (!$options['isNew']) {
            $builder->add('roomType', DocumentType::class, [
                'label' => 'form.roomType.room_type',
                'group' => 'form.roomType.general_info',
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function (DocumentRepository $documentRepository) use ($options) {
                    return $documentRepository->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotelId'])
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true
            ]);
        }

        $builder
            ->add('facilities', FacilitiesType::class, [
                'label' => 'form.roomType.facilities',
                'group' => 'form.roomType.general_info',
                'required' => false
            ])
            ->add('status', DocumentType::class, [
                'label' => 'form.roomType.status',
                'group' => 'form.roomType.settings',
                'required' => false,
                'query_builder' => function (DocumentRepository $documentRepository) use ($options) {
                    return $documentRepository->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotelId']);
                },
                'class' => 'MBH\Bundle\HotelBundle\Document\RoomStatus',
                'placeholder' => '',
                'multiple' => 'true',

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Room',
            'isNew' => true,
            'hotelId' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_room_type';
    }

}
