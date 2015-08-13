<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class RoomType
 */
class RoomType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', 'text', [
                'label' => 'form.roomType.name',
                'required' => true,
                'group' => 'form.roomType.general_info',
                'attr' => ['placeholder' => '27']
            ])
            ->add('title', 'text', [
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
            ->add('housing', 'document', $housingOptions)
            ->add('floor', 'text', [
                'label' => 'form.roomType.floor',
                'group' => 'form.roomType.general_info',
                'required' => false
            ])
            ->add('isEnabled', 'checkbox', [
                'label' => 'form.roomType.is_included',
                'group' => 'form.roomType.settings',
                'value' => true,
                'required' => false,
                'help' => 'form.roomType.is_room_included_in_search'
            ]);

        if (!$options['isNew']) {
            $builder->add('roomType', 'document', [
                'label' => 'form.roomType.room_type',
                'group' => 'form.roomType.general_info',
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotelId'])
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true
            ]);
        }

        $builder->add('status', 'document', [
            'label' => 'form.roomType.status',
            'group' => 'form.roomType.general_info',
            'required' => false,
            'class' => 'MBH\Bundle\HotelBundle\Document\RoomStatus',
            'empty_value' => '',
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

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type';
    }

}
