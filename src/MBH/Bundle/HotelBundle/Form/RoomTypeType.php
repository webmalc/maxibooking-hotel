<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RoomTypeType
 */
class RoomTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', 'text', [
                'label' => 'form.roomTypeType.name',
                'required' => true,
                'group' => 'form.roomTypeType.general_info',
                'attr' => ['placeholder' => 'form.roomTypeType.comfort_plus']
            ])
            ->add('title', 'text', [
                'label' => 'form.roomTypeType.inner_name',
                'required' => false,
                'group' => 'form.roomTypeType.general_info',
                'attr' => ['placeholder' => 'form.roomTypeType.comport_plus_rooms_in_new_housing'],
                'help' => 'Название для использования внутри MaxiBooking'
            ])
            ->add('internationalTitle', 'text', [
                'label' => 'form.roomTypeType.international_title',
                'required' => false,
                'group' => 'form.roomTypeType.general_info',
            ])
            ->add('description', 'textarea', [
                'label' => 'form.roomTypeType.description',
                'help' => 'form.roomTypeType.online_reservation_room_description',
                'required' => false,
                'group' => 'form.roomTypeType.general_info',
                'attr' => ['class' => 'big roomTypeTypeEditor tinymce']
            ])
            ->add('color', 'text', [
                'label' => 'form.roomTypeType.color',
                'required' => true,
                'group' => 'form.roomTypeType.general_info',
                'attr' => ['placeholder' => '008000'],
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.roomTypeType.chess_room_type_color'
            ])
            ->add('roomSpace', 'text', [
                'label' => 'form.roomTypeType.room_space',
                'required' => false,
                'group' => 'form.roomTypeType.general_info',
            ])
            ->add('facilities', 'mbh_facilities', [
                //'label' => 'form.roomTypeType.is_included',
                'group' => 'form.roomTypeType.general_info',
                'required' => false
            ])
            ->add('isHostel', 'checkbox', [
                'label' => 'form.hotelType.hostel',
                'group' => 'form.roomTypeType.places',
                'value' => true,
                'required' => false,
                'help' => 'form.hotelType.hostel_hotel_or_not'
            ])
            ->add('places', 'text', [
                'label' => 'form.roomTypeType.main_places',
                'group' => 'form.roomTypeType.places',
                'required' => true,
                'attr' => ['placeholder' => 'hotel', 'class' => 'spinner room-type-places'],
                'help' => 'form.roomTypeType.room_main_places_amount'
            ])
            ->add('additionalPlaces', 'text', [
                'label' => 'form.roomTypeType.additional_places',
                'group' => 'form.roomTypeType.places',
                'required' => true,
                'attr' => ['placeholder' => 'hotel', 'class' => 'spinner room-type-places'],
                'help' => 'form.roomTypeType.room_additional_places_amount'
            ])
            ->add('isChildPrices', 'checkbox', [
                'label' => 'form.roomTypeType.isChildPrices',
                'group' => 'form.roomTypeType.prices',
                'value' => true,
                'required' => false,
                'help' => 'form.roomTypeType.isChildPricesDesc'
            ])
            ->add('isIndividualAdditionalPrices', 'checkbox', [
                'label' => 'form.roomTypeType.isIndividualAdditionalPrices',
                'group' => 'form.roomTypeType.prices',
                'value' => true,
                'required' => false,
                'help' => 'form.roomTypeType.isIndividualAdditionalPricesDesc'
            ])
            ->add('isEnabled', 'checkbox', [
                'label' => 'form.roomTypeType.is_included',
                'group' => 'form.roomTypeType.settings',
                'value' => true,
                'required' => false,
                'help' => 'form.roomTypeType.is_room_included_in_search'
            ])
        ;
        if ($options['disabledRoomTypeCategory']) {
            $builder
                ->add('category', 'document', [
                    'label' => 'form.roomTypeType.category',
                    'group' => 'form.roomTypeType.settings',
                    'required' => false,
                    'empty_data' => null,
                    'class' => 'MBH\Bundle\HotelBundle\Document\RoomTypeCategory'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\RoomType',
            'imageUrl' => null,
            'deleteImageUrl' => null,
            'facilities' => [],
            'disabledRoomTypeCategory' => false,
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type_type';
    }

}
