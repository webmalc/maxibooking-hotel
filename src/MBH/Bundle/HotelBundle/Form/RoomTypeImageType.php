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

        $builder->
        add('imageFile', 'file', ['label' => 'form.roomTypeType.image',
            'required' => false,
            'help' => $fileText,
            'constraints' => [new Image(), new NotBlank()],
            'attr' => ['multiple' => 'multiple']
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type_image_type';
    }
}