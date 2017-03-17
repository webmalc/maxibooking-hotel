<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoomTypeImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imageFile', FileType::class, ['label' => 'form.roomTypeType.image',
                'required' => false,
                'help' => 'form.roomTypeType.image.help',
                'constraints' => [new Image(), new NotBlank()],
                'attr' => ['multiple' => 'multiple']
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_room_type_image_type';
    }
}