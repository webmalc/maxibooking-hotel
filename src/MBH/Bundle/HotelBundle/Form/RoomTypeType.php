<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Image;

class RoomTypeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fileText = 'Изображние типа номера для онлайн бронирования';

        if($options['imageUrl']) {
            $fileText  = '<a class="fancybox" href="/' . $options['imageUrl'] . '"><i class="fa fa-image"></i> ' . $fileText . '</a>';

            if ($options['deleteImageUrl']) {
                $fileText  .= ' <br> <a href="'. $options['deleteImageUrl'] .'" class="text-danger"><i class="fa fa-trash"></i> Удалить изображение</a>';
            }
        }

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
                ->add('description', 'textarea', [
                     'label' => 'form.roomTypeType.description',
                     'help' => 'form.roomTypeType.online_reservation_room_description',
                     'required' => false,
                     'group' => 'form.roomTypeType.general_info',
                     'attr' => ['class' => 'big']
                ])
                ->add('color', 'text', [
                    'label' => 'form.roomTypeType.color',
                    'required' => true,
                    'group' => 'form.roomTypeType.general_info',
                    'attr' => ['placeholder' => '008000'],
                    'help' => 'form.roomTypeType.chess_room_type_color'
                ])
                ->add('imageFile', 'file', [
                    'label' => 'form.roomTypeType.image',
                    'required' => false,
                    'mapped' => false,
                    'group' => 'form.roomTypeType.general_info',
                    'help' => $fileText,
                    'constraints' => [new Image()]
                ])
                ->add('places', 'text', [
                    'label' => 'form.roomTypeType.main_places',
                    'group' => 'form.roomTypeType.settings',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel', 'class' => 'spinner'],
                    'help' => 'form.roomTypeType.room_main_places_amount'
                ])
                ->add('additionalPlaces', 'text', [
                    'label' => 'form.roomTypeType.additional_places',
                    'group' => 'form.roomTypeType.settings',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel', 'class' => 'spinner'],
                    'help' => 'form.roomTypeType.room_additional_places_amount'
                ])
                ->add('isEnabled', 'checkbox', [
                    'label' => 'form.roomTypeType.is_included',
                    'group' => 'form.roomTypeType.settings',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.roomTypeType.is_room_included_in_search'
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\RoomType',
            'imageUrl' => null,
            'deleteImageUrl' => null
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type_type';
    }

}
