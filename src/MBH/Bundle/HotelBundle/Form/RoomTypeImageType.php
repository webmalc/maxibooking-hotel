<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoomTypeImageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fileText = 'Изображние типа номера для онлайн бронирования';
//
//        if ($options['imageUrl']) {
//            $fileText = '<a class="fancybox" href="/' . $options['imageUrl'] . '"><i class="fa fa-image"></i> ' . $fileText . '</a>';
//
//            if ($options['deleteImageUrl']) {
//                $fileText .= ' <br> <a href="' . $options['deleteImageUrl'] . '" class="text-danger"><i class="fa fa-trash"></i> Удалить изображение</a>';
//            }
//        }

        $builder->
        add('imageFile', 'file', ['label' => 'form.roomTypeType.image',
            'required' => false,
            'mapped' => false,
            'group' => 'form.roomTypeType.general_info',
            'help' => $fileText,
            'constraints' => [new Image(), new NotBlank()],
            'attr' => ['multiple' => 'multiple']
        ])
        ->add('save', 'submit');
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type_image_type';
    }
}